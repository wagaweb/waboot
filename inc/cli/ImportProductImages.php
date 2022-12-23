<?php

namespace Waboot\inc\cli;

use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\StorageAttributes;
use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\utils\Utilities;
use function Waboot\inc\getAllProductVariationIds;
use function Waboot\inc\getProductType;

class ImportProductImages extends AbstractCommand
{
    /**
     * @var string
     */
    protected $logDirName = 'import-images';
    /**
     * @var string
     */
    protected $logFileName = 'import-images';
    /**
     * @var string
     */
    protected string $importDirPath;
    /**
     * @var array
     */
    protected array $allowedExtensions;
    /**
     * @var string
     */
    protected string $gallerySeparator;

    /**
     * @return array
     */
    public static function getCommandDescription(): array
    {
        return [
            'shortdesc' => 'Import product images',
            'longdesc' => '## EXAMPLES' . "\n\n" . 'wp wawoo:import-images',
            'synopsis' => [
                [
                    'type' => 'assoc',
                    'name' => 'path',
                    'description' => 'Specifies the path where looking for images',
                    'default' => WP_CONTENT_DIR . '/imports/images',
                    'optional' => true,
                ],
                [
                    'type' => 'assoc',
                    'name' => 'allowed-extensions',
                    'description' => 'Comma separated list of allowed file extensions',
                    'default' => 'webp,jpg',
                    'optional' => true,
                ],
                [
                    'type' => 'assoc',
                    'name' => 'gallery-separator',
                    'description' => 'Character used to identify gallery images',
                    'default' => '-',
                    'optional' => true,
                ],
                [
                    'type' => 'flag',
                    'name' => 'dry-run',
                    'description' => 'Perform a dry run',
                    'optional' => true,
                ],
            ],
        ];
    }

    /**
     * @param array $args
     * @param array $assoc_args
     * @return int
     */
    public function __invoke(array $args, array $assoc_args): int
    {
        parent::__invoke($args, $assoc_args);
        try{
            $importDirPath = $assoc_args['path'] ?? WP_CONTENT_DIR . '/imports/images';
            if(!\is_dir($importDirPath)){
                $importDirCreated = wp_mkdir_p($importDirPath);
                if(!$importDirCreated){
                    throw new \RuntimeException('Unable to create directory: '.$importDirPath);
                }
            }
            $this->importDirPath = $importDirPath;
            $this->log('Images path: '.$this->importDirPath);

            $gallerySeparator = $assoc_args['gallery-separator'] ?? '-';
            if(\is_string($gallerySeparator) || $gallerySeparator === ''){
                throw new \RuntimeException('Invalid gallery separator');
            }
            $this->gallerySeparator = $gallerySeparator;
            $this->log('Gallery separator: '.$this->gallerySeparator);

            $allowedExtensions = $assoc_args['allowed-extensions'] ?? 'webp,jpg';
            $allowedExtensions = explode(',',$allowedExtensions);
            if(empty($allowedExtensions)){
                throw new \RuntimeException('Unable to parse allowed image files extensions');
            }
            $this->allowedExtensions = $allowedExtensions;
            $this->log('Allowed image files extensions: '.implode(',',$this->allowedExtensions));

            $local = $this->getLocalFileSystemHandler();
            $localFiles = $local->listContents('/',false)
                ->filter(function (StorageAttributes $attributes){
                    $isValid = false;
                    foreach ($this->allowedExtensions as $fileExtension){
                        if($attributes->isFile() && strpos($attributes->path(),'.'.$fileExtension) !== false){
                            $isValid = true;
                        }
                    }
                    return $isValid;
                })
                ->sortByPath()
                ->toArray();
            if(count($localFiles) === 0){
                throw new \RuntimeException('No valid images found');
            }
            $variableProductsToAssignImagesTo = [];
            $galleries = [];
            foreach ($localFiles as $file){
                /**
                 * @var FileAttributes $file
                 */
                try{
                    $this->log('Parsing of: '.$file->path());
                    //Is it a gallery image?
                    $galleryImagePattern = '/([a-zA-Z0-9]+)'.$this->gallerySeparator.'\d+\./';
                    $galleryImagePattern = apply_filters('wawoo_product_images_importer/gallery_image_pattern',$galleryImagePattern,$this->gallerySeparator);
                    if(preg_match($galleryImagePattern,$file->path(),$matches)){
                        //Le immagini che finiscono con -1, -2, ecc, sono parte della galleria
                        $this->log('It is an gallery image');
                        if(isset($matches[1])){
                            $sku = $matches[1];
                            $galleries[$sku][] = $file;
                        }else{
                            $this->warning('Product identifier not found in the file name');
                        }
                        continue;
                    }
                    $productIdentifier = $this->getProductIdentifierByImageFileName($file->path());
                    $localProductId = $this->getProductIdByIdentifier($productIdentifier);
                    if(!\is_int($localProductId) || $localProductId === 0){
                        $this->log('- Product identified by: '.$productIdentifier.' non found');
                        continue;
                    }
                    $this->log('- Product identified by: '.$productIdentifier.' trovato: #'.$localProductId);
                    if(getProductType($localProductId) === 'variation'){
                        $this->log('-- It is a variation');
                        try{
                            $postParentId = Utilities::getPostParent($localProductId);
                            if(!\in_array($postParentId,$variableProductsToAssignImagesTo,true)){
                                $variableProductsToAssignImagesTo[] = $postParentId;
                            }
                            $this->log('--- Parent: #'.$postParentId);
                        }catch (\RuntimeException $e){
                            $this->log('--- Unable to find the parent product of #'.$localProductId);
                        }
                    }

                    $localThumbnailPath = $this->importDirPath.'/'.$file->path();
                    /*
                     * Thumbnail
                     */
                    $this->log('- Setting thumbnail ('.$localThumbnailPath.') to #'.$localProductId);
                    $canAssignNewThumbnail = true;
                    $pInfo = pathinfo($localThumbnailPath);
                    $currentThumbnailId = get_post_thumbnail_id($localProductId);
                    //The product already has an image...
                    if(\is_int($currentThumbnailId) && $currentThumbnailId !== 0){
                        $attachmentUrl = wp_get_attachment_image_url($currentThumbnailId,'full');
                        if(\is_string($attachmentUrl) && $attachmentUrl !== ''){
                            $attachmentImageName = basename($attachmentUrl);
                            if($attachmentImageName === basename($file->path())){
                                $canAssignNewThumbnail = false; //The product already have an image assigned with the same name of the image we are trying to assign now, so avoid creating another media
                            }elseif(strpos($attachmentImageName,$pInfo['filename']) === 0){
                                $canAssignNewThumbnail = false; //Image already assigned, but its a double image like xxx-2, xxx-3...
                            }
                        }
                    }
                    if(!$canAssignNewThumbnail){
                        $this->log('- Thumbnail already assigned. Skip');
                        $this->log('- Setting image as parsed');
                        $this->setLocalImageAsParsed($file->path());
                        continue;
                    }
                    $existingAttachmentId = $this->getExistingAttachmentIdFromImageName($pInfo['filename']);
                    if($existingAttachmentId){
                        $this->log('-- Immagine già esistente con ID: #'.$existingAttachmentId);
                        //Assign the existing attachment id to the product
                        if(!$this->isDryRun()){
                            $assigned = set_post_thumbnail($localProductId, $existingAttachmentId);
                        }else{
                            $assigned = true;
                        }
                    }else{
                        $this->log('-- Immagine non esistente, creo il media');
                        //Create and assign a new attachment
                        if(!$this->isDryRun()){
                            $assignedResult = Utilities::setFeaturedImageFromFilePath($localThumbnailPath,$localProductId);
                            $assigned = $assignedResult['assigned'];
                        }else{
                            $assigned = true;
                        }
                    }
                    if(!$assigned){
                        $this->log('-- ERRORE: immagine non assegnata');
                    }else{
                        $this->log('- Immagine assegnata');
                        $this->log('- Setto l\immagine come parsata');
                        $this->setLocalImageAsParsed($file->path());
                    }
                }catch (\Exception | \Throwable $e){
                    $this->log('-- ERRORE: '.$e->getMessage());
                }
            }
            if(!empty($variableProductsToAssignImagesTo)){
                $this->log('Assegnazione immagini ai prodotti variabili');
                foreach ($variableProductsToAssignImagesTo as $variableProductId){
                    $this->log('- Parsing di #'.$variableProductId);
                    $currentVariableProductThumbnailId = get_post_thumbnail_id($variableProductId);
                    if(\is_int($currentVariableProductThumbnailId) && $currentVariableProductThumbnailId !== 0){
                        $this->log('-- Il prodotto variabile ha già una immagine');
                        continue;
                    }
                    $allVariationsIds = getAllProductVariationIds($variableProductId);
                    if(empty($allVariationsIds)){
                        continue;
                    }
                    $thumbnailId = null;
                    foreach ($allVariationsIds as $variationId){
                        $currentThumbnailId = (int) get_post_thumbnail_id($variationId);
                        if($currentThumbnailId === 0){
                            continue;
                        }
                        $thumbnailId = $currentThumbnailId;
                        break;
                    }
                    if(!$thumbnailId){
                        continue;
                    }
                    $this->log('-- Assegno la thumbnail #'.$thumbnailId.' al post #'.$variableProductId);
                    if(!$this->isDryRun()){
                        $assigned = set_post_thumbnail($variableProductId,$thumbnailId);
                    }else{
                        $assigned = true;
                    }
                    if($assigned){
                        $this->log('--- Thumbnail assegnata correttamente');
                    }else{
                        $this->log('--- ERRORE: Thumbnail NON assegnata');
                    }
                }
            }
            /*
             * Galleries
             */
            foreach ($galleries as $ean13 => $gallery){
                $this->log('Assegnazione galleria per: '.$ean13);
                $productId = $this->getProductIdByIdentifier($ean13);
                if($productId <= 0){
                    $this->log('- ERRORE: ID non trovato');
                    continue;
                }
                $galleryIds = [];
                foreach ($gallery as $galleryImage){
                    try{
                        $newImageId = Utilities::createAttachment($this->importDirPath.'/'.$galleryImage->path(),$productId);
                        if($newImageId !== 0){
                            $galleryIds[] = $newImageId;
                            $this->setLocalImageAsParsed($galleryImage->path());
                        }
                    }catch (\Throwable $e){
                        $this->log('- ERRORE: '.$e->getMessage());
                    }
                }
                if(!empty($galleryIds)){
                    if(getProductType($productId) === 'product_variation'){
                        do_action('wawoo_product_images_importer/setting_variation_gallery',$productId,$galleryIds);
                    }else{
                        $this->log(sprintf('- Set di "_product_image_gallery" per %d = %s',$productId,implode(',',$galleryIds)));
                        update_post_meta($productId,'_product_image_gallery', implode(',', $galleryIds));
                    }
                }
            }
            $this->success('Operazione completata');
            return 0;
        }catch (FilesystemException | \Exception | \Throwable $e){
            $this->error($e->getMessage());
            return 1;
        }
    }

    /**
     * @param int $attachmentId
     * @return bool
     * @deprecated
     */
    private function assignGUIDToAttachment(int $attachmentId): bool
    {
        global $wpdb;
        $attachmentUrl = wp_get_attachment_image_url($attachmentId,'full');
        if(!\is_string($attachmentUrl) || $attachmentUrl === ''){
            return false;
        }
        $this->log('--- Assegno il GUID: '.$attachmentUrl.' alla immagine #'.$attachmentId);
        $affectedRows = $wpdb->update($wpdb->posts,[
            'guid' => $attachmentUrl
        ],[
            'ID' => $attachmentId
        ]);
        return (bool) $affectedRows;
    }

    /**
     * @param string $imageName
     * @return int|null
     */
    private function getExistingAttachmentIdFromImageName(string $imageName): ?int
    {
        global $wpdb;
        $q = 'SELECT ID FROM '.$wpdb->prefix.'posts WHERE post_type = %s AND post_title = %s';
        $q = $wpdb->prepare($q,'attachment',$imageName);
        $r = $wpdb->get_var($q);
        if(\is_string($r) && $r !== '' && (int) $r !== 0){
            return (int) $r;
        }
        return null;
    }

    /**
     * @throws FilesystemException
     */
    private function setLocalImageAsParsed(string $imageFilePath): void
    {
        if($this->isDryRun()){
            return;
        }
        $local = $this->getLocalFileSystemHandler();
        $parsedFilePath = 'parsed/'.$imageFilePath;
        if($local->fileExists($parsedFilePath)){
            $local->delete($parsedFilePath);
        }
        $local->move($imageFilePath,$parsedFilePath);
    }

    /**
     * @return Filesystem
     */
    private function getLocalFileSystemHandler(): Filesystem
    {
        return new Filesystem(new LocalFilesystemAdapter($this->importDirPath));
    }

    /**
     * @param string $imageFileName
     * @return string
     */
    private function getProductIdentifierByImageFileName(string $imageFileName): string
    {
        $allowedExtensions = $this->allowedExtensions;
        array_walk($allowedExtensions, fn($ext) => '.'.$ext); //Add a '.' before the extensions
        $regExPatternInner = implode('|',$allowedExtensions);
        $regExPattern = '/('.$regExPatternInner.')$/';
        return preg_replace($regExPattern,'',$imageFileName);
    }

    /**
     * @param string $identifier
     * @return int|null
     */
    private function getProductIdByIdentifier(string $identifier): ?int
    {
        if(has_filter('wawoo_product_images_importer/product_id_fetcher')){
            $productId = apply_filters('wawoo_product_images_importer/product_id_fetcher',$identifier);
        }else{
            $productId = (int) wc_get_product_id_by_sku($identifier);
        }
        if(!\is_int($productId) || $productId === 0){
            return null;
        }
        return $productId;
    }
}