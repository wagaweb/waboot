<?php

namespace Waboot\inc\cli\product_import;

use League\Csv\Reader;
use Waboot\inc\cli\utils\ImportExportCSVColumnHelpers;
use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\utils\Posts;
use Waboot\inc\core\utils\Utilities;
use function Waboot\inc\adjustPriceMeta;
use function Waboot\inc\syncVariableProductData;

class ImportProducts extends AbstractCommand
{
    /**
     * @var string
     */
    protected $logDirName = 'import-new-products';
    /**
     * @var string
     */
    protected $logFileName = 'import-new-products';
    /**
     * @var string
     */
    protected $importDirPath;
    /**
     * @var string
     */
    protected $sourceFilePath;
    /**
     * @var string
     */
    protected $productIdentifier;
    /**
     * @var string
     */
    protected $brandTaxonomyName;
    /**
     * @var string
     */
    protected $colorTaxonomyName;
    /**
     * @var string
     */
    protected $sizeTaxonomyName;
    /**
     * @var bool
     */
    protected $mustUpdatePrices;
    /**
     * @var bool
     */
    protected $mustUpdateStocks;
    /**
     * @var string
     */
    protected $parseAllFiles;
    /**
     * @var array
     */
    protected $variablesProductsToSync;
    /**
     * @var int[]
     */
    protected $parsedProductIds;
    /**
     * @var string[]
     */
    protected $upSellsToAssign;
    /**
     * @var string[]
     */
    protected $crossSellsToAssign;
    /**
     * @var ImportProductsCSVRow
     */
    protected $currentCSVRow;
    /**
     * @var ImportProductsManifestFile
     */
    protected $manifestFileHelper;
    /**
     * @var string[]
     */
    protected $excludedColumns;


    /**
     * Importa i prodotti
     *
     * ## OPTIONS
     *
     * [--file]
     * : Specifica il path del file xlsx da importare
     *
     * [--parse-all-files]
     * : Specifica se parsare automaticamente solo il file del giorno o tutti i file CSV nella cartella di importazione
     *
     * [--manifest]
     * : Absolute path to a manifest file
     *
     * [--product-identifier]
     * : Specifica a quale meta utilizzare per identificare il prodotto (solitamente il meta corrispondende al valore della prima colonna)
     *
     * [--brand-taxonomy]
     * : Specifica il nome della tassonoma dei brand (default: brand_taxonomy)
     *
     * [--color-taxonomy]
     * : Specifica il nome della tassonoma dei colori (default: pa_color)
     *
     * [--size-taxonomy]
     * : Specifica il nome della tassonoma delle taglie (default: pa_size)
     *
     * [--update-prices]
     * : Specifica se aggiornare il prezzo dei prodotti già importati
     *
     * [--update-stocks]
     * : Specifica se aggiornare lo stock dei prodotti già importati
     *
     * [--dry-run]
     * : Esegue una dry-run
     *
     * ## EXAMPLES
     *
     *      wp dabbene:import-products --file=baz.csv
     */
    public function __invoke($args, $assoc_args): int
    {
        parent::__invoke($args, $assoc_args);
        try{
            $importDirPath = $assoc_args['basepath'] ?? WP_CONTENT_DIR . '/imports/products';
            if(!\is_dir($importDirPath)){
                $importDirCreated = wp_mkdir_p($importDirPath);
                if(!$importDirCreated){
                    throw new \RuntimeException('Impossibile creare la directory: '.$importDirPath);
                }
            }
            $this->importDirPath = $importDirPath;
            $this->parseAllFiles = isset($assoc_args['parse-all-files']);
            if($this->parseAllFiles){
                $this->log('Parsing di tutti i files');
            }
            if(isset($assoc_args['manifest']) && \is_string($assoc_args['manifest']) && $assoc_args['manifest'] !== ''){
                $manifestFile = $assoc_args['manifest'];
                try{
                    $this->parseManifestFile($manifestFile);
                    $this->log('Using manifest: '.$manifestFile);
                }catch (\Exception | \Throwable $e){
                    $this->error($e->getMessage(),false);
                }
            }
            if(!isset($this->productIdentifier)){
                $this->productIdentifier = $assoc_args['product-identifier'] ?? '_sku';
            }
            $this->log('Identificatore prodotto: '.$this->productIdentifier);
            if(!isset($this->brandTaxonomyName)){
                $this->brandTaxonomyName = $assoc_args['brand-taxonomy'] ?? 'brand_taxonomy';
            }
            $this->log('Nome tassonomia brand: '.$this->brandTaxonomyName);
            if(!isset($this->colorTaxonomyName)){
                $this->colorTaxonomyName = $assoc_args['color-taxonomy'] ?? 'pa_color';
            }
            $this->log('Nome tassonomia colore: '.$this->colorTaxonomyName);
            if(!isset($this->sizeTaxonomyName)){
                $this->sizeTaxonomyName = $assoc_args['size-taxonomy'] ?? 'pa_size';
            }
            $this->log('Nome tassonomia size: '.$this->sizeTaxonomyName);
            if(isset($assoc_args['update-prices'])){
                $this->mustUpdatePrices = true;
            }
            if($this->mustUpdatePrices){
                $this->log('Aggiornamento prezzi attivato');
            }
            if(isset($assoc_args['update-stocks'])){
                $this->mustUpdateStocks = isset($assoc_args['update-stocks']);
            }
            if($this->mustUpdateStocks){
                $this->log('Aggiornamento stock attivato');
            }
            $sourceFilePath = false;
            if(isset($assoc_args['file'])){
                $sourceFilePath = $assoc_args['file'];
            }elseif(!$this->parseAllFiles){
                $sourceFilePath = 'anagrafiche-'.(new \DateTime('now',new \DateTimeZone('Europe/Rome')))->format('ymd').'.csv';
            }
            if($sourceFilePath !== false && !\is_file($sourceFilePath)){
                $sourceFilePath = $importDirPath.'/'.$sourceFilePath;
                if(!\is_file($sourceFilePath)){
                    $this->error('Impossibile trovare il file: '.$sourceFilePath);
                }
            }
            if($this->parseAllFiles){
                foreach ($this->fetchFiles() as $sourceFilePath){
                    $this->sourceFilePath = $sourceFilePath;
                    $this->log('Selezionato il file: '.$sourceFilePath);
                    $this->parseCSV();
                    try{
                        if(!$this->isDryRun()){
                            $this->setLocalFileAsParsed();
                        }
                        $this->log('File locale settato come parsato');
                    }catch (\Exception $e){
                        $this->error('ERRORE: Impossibile settare il file locale come parsato');
                        return 1;
                    }
                }
            }else{
                $this->sourceFilePath = $sourceFilePath;
                $this->log('Selezionato il file: '.$sourceFilePath);
                $this->parseCSV();
                try{
                    if(!$this->isDryRun()){
                        $this->setLocalFileAsParsed();
                    }
                    $this->log('File locale settato come parsato');
                }catch (\Exception $e){
                    $this->error('ERRORE: Impossibile settare il file locale come parsato');
                    return 1;
                }
            }
            $this->assignRelatedProducts();
            $this->success('Done');
            return 0;
        }catch (\Exception | \Throwable $e){
            $this->log('ERRORE: '.$e->getMessage());
            return 1;
        }
    }

    /**
     * Parse the manifest file and store
     * @param string $manifestFilePath
     * @throws \RuntimeException
     * @throws ImportProductsManifestFileException
     */
    private function parseManifestFile(string $manifestFilePath): void
    {
        $manifest = new ImportProductsManifestFile($manifestFilePath);
        $this->manifestFileHelper = $manifest;
        $this->excludedColumns = $manifest->getExcludedColumns();
        $this->brandTaxonomyName = $manifest->getSetting('brand_taxonomy_name');
        $this->colorTaxonomyName = $manifest->getSetting('color_taxonomy_name');
        $this->sizeTaxonomyName = $manifest->getSetting('size_taxonomy_name');
        $this->mustUpdatePrices = $manifest->getSetting('update_prices');
        $this->mustUpdateStocks = $manifest->getSetting('update_stocks');
        $productIdentifier = $manifest->getSetting('product_identifier');
        if($productIdentifier !== null){
            $this->productIdentifier = $productIdentifier;
        }
    }

    /**
     * @return void
     * @throws \League\Csv\Exception
     * @throws \WC_Data_Exception
     */
    private function parseCSV(): void
    {
        $csv = Reader::createFromPath($this->sourceFilePath);
        $this->log('Parsing del file...');
        $csv->setDelimiter(',');
        $csv->setHeaderOffset(0);
        $this->log('Counting dei record...');
        foreach ($csv->getRecords() as $r) {
            try{
                $this->currentCSVRow = $this->createCSVColumnInstance($r);
                $this->currentCSVRow->parseRowData();
                $identifier = $this->currentCSVRow->getIdentifier();
                $this->log(sprintf('--- Parsing prodotto %s', $identifier));
                if($this->currentCSVRow->isSimpleProductRow()){
                    $this->log('--- Verrà creato o aggiornato un prodotto SEMPLICE');
                    $this->createOrUpdateSimpleProduct();
                }else{
                    $this->log('--- Verrà creata o aggiornata una VARIAZIONE');
                    $this->createOrUpdateVariation();
                }
            }catch (ImportProductsCSVRowException | \RuntimeException | \Exception | \Throwable $e){
                $this->log('ERRORE: '.$e->getMessage());
                //var_dump($e);
                continue;
            }
        }
        if(!empty($this->variablesProductsToSync)){
            $this->log('Syncing dei prodotti variabili...');
            foreach ($this->variablesProductsToSync as $variableProductId => $variationsIds) {
                $this->log('- Sync prodotto #'.$variableProductId);
                $variationsIds = array_unique($variationsIds);
                foreach ($variationsIds as $variationsId){
                    $this->log('-- Fix prezzi variazione #'.$variationsId);
                    adjustPriceMeta($variationsId);
                }
                syncVariableProductData($variableProductId);
            }
            if($this->parseAllFiles){
                $this->variablesProductsToSync = [];
            }
        }
        if(!empty($this->parsedProductIds)){
            $this->finalizeParsedProducts();
            if($this->parseAllFiles){
                $this->parsedProductIds = [];
            }
        }
    }

    /**
     * @return void
     */
    private function finalizeParsedProducts(): void
    {
        if(has_action('wawoo_product_importer/finalize_products')){
            $this->log('Finalizzazione dei prodotti parsati...');
        }
        do_action('wawoo_product_importer/finalize_products',$this->parsedProductIds);
    }

    /**
     * @return void
     */
    private function assignRelatedProducts(): void
    {
        //Up sells
        $this->log('Assegnazione Up Sells...');
        if(isset($this->upSellsToAssign) && \is_array($this->upSellsToAssign) && !empty($this->upSellsToAssign)){
            foreach ($this->upSellsToAssign as $productId => $identifiersToAssign)
            {
                if(!\is_array($identifiersToAssign) || empty($identifiersToAssign)){
                    continue;
                }
                $idToAssigns = [];
                foreach ($identifiersToAssign as $productIdentifier){
                    $productId = $this->getProductIdByProductIdentifier($productIdentifier);
                    if(is_int($productId) && $productId > 0){
                        $idToAssigns[] = $productId;
                    }
                }
                if(!empty($idToAssigns)){
                    $this->log(sprintf('-- Prodotto %s: %s',$productId,implode(',',$idToAssigns)));
                    update_post_meta($productId,'_upsell_ids',$idToAssigns);
                }
            }
        }
        //Cross sells
        $this->log('Assegnazione Cross Sells...');
        if(isset($this->crossSellsToAssign) && \is_array($this->crossSellsToAssign) && !empty($this->crossSellsToAssign)){
            foreach ($this->crossSellsToAssign as $productId => $identifiersToAssign)
            {
                if(!\is_array($identifiersToAssign) || empty($identifiersToAssign)){
                    continue;
                }
                $idToAssigns = [];
                foreach ($identifiersToAssign as $productIdentifier){
                    $productId = $this->getProductIdByProductIdentifier($productIdentifier);
                    if(is_int($productId) && $productId > 0){
                        $idToAssigns[] = $productId;
                    }
                }
                if(!empty($idToAssigns)){
                    $this->log(sprintf('-- Prodotto %s: %s',$productId,implode(',',$idToAssigns)));
                    update_post_meta($productId,'_crosssell_ids',$idToAssigns);
                }
            }
        }
    }

    /**
     * @return void
     * @throws ImportProductsException
     * @throws \WC_Data_Exception
     */
    private function createOrUpdateSimpleProduct(): void
    {
        $CSVRow = $this->currentCSVRow;
        $isNew = true;
        $productId = $this->getProductIdByProductIdentifier($CSVRow->getIdentifier());
        if(is_int($productId) && $productId > 0){
            $this->log('--- Prodotto SEMPLICE con SKU %s già esistente');
            $product = wc_get_product($productId);
            if(!$product instanceof \WC_Product){
                throw new ImportProductsException('Impossibile recuperare il prodotto');
            }
            if($this->mustUpdatePrices && $CSVRow->hasRegularPrice()){
                $product->set_regular_price($CSVRow->getRegularPrice());
            }
            if($this->mustUpdateStocks && $CSVRow->hasStock()){
                $product->set_stock_quantity($CSVRow->getStock());
                $product->set_stock_status($CSVRow->getStockStatus());
            }
            $isNew = false;
        }else{
            $this->log('--- Verrà creato un prodotto SEMPLICE');
            $product = new \WC_Product_Simple();
            $product->set_sku($CSVRow->getSku());
            $product->set_status('draft');
            $product->set_stock_quantity($CSVRow->getStock());
            $product->set_stock_status($CSVRow->getStockStatus());
            $product->set_regular_price($CSVRow->getRegularPrice());
        }
        $this->addParsedProductId($product->get_id());
        $product->set_name($CSVRow->getTitle());
        $slug = $CSVRow->getSlug();
        if(\is_string($slug) && $slug !== ''){
            $product->set_slug($slug);
        }
        $product->set_short_description($CSVRow->getShortDescription());
        $product->set_description($CSVRow->getLongDescription());
        $product->set_manage_stock(true);
        if($CSVRow->hasEan13()){
            $product->add_meta_data('_ean13','field_ean13');
        }
        $id = $product->save();
        if ($id === 0) {
            throw new ImportProductsException('ERRORE: impossibile creare o aggiornare prodotto con IDENTIFICATORE %s', $CSVRow->getIdentifier());
        }
        if($CSVRow->hasBrand()){
            $this->log('--- Assegnazione brand: '.$CSVRow->getBrand());
            $this->addHierarchicalTermsToObjectFromTermListString($product->get_id(), $CSVRow->getBrand(), $this->getBrandTaxonomyName());
        }
        if($CSVRow->hasCategory()){
            $this->log('--- Assegnazione categorie: '.$CSVRow->getCategory());
            $this->addHierarchicalTermsToObjectFromTermListString($product->get_id(), $CSVRow->getCategory(), 'product_cat');
        }
        if($CSVRow->hasColor()) {
            try{
                $colorTerm = $this->getOrCreateTerm($this->getColorTaxonomyName($product->get_id()), $CSVRow->getColor());
                wp_set_object_terms($product->get_id(), $colorTerm->term_id, $colorTerm->taxonomy, false);
            }catch (\RuntimeException $e){
                $this->log('ERRORE: '.$e->getMessage());
            }
        }
        if($CSVRow->hasSize()) {
            try{
                $sizeTerm = $this->getOrCreateTerm($this->getSizeTaxonomyName($product->get_id()), $CSVRow->getSize());
                wp_set_object_terms($product->get_id(), $sizeTerm->term_id, $sizeTerm->taxonomy, false);
            }catch (\RuntimeException $e){
                $this->log('ERRORE: '.$e->getMessage());
            }
        }
        $this->log('--- Assegnazione custom fields...');
        foreach ($CSVRow->getMetaFields() as $customFieldData) {
            $customFieldKey = $customFieldData['key'];
            $customFieldValue = $customFieldData['value'];
            if($customFieldValue !== null && $customFieldValue !== ''){
                $this->log('---- '.$customFieldKey);
                $product->add_meta_data($customFieldKey,$customFieldValue);
            }
        }
        $product->save();
        $this->log('-- Fix dei meta dei prezzi');
        adjustPriceMeta($product->get_id());
        $this->setImportedProductMeta($product->get_id());
        if($isNew){
            $this->log(sprintf('--- Prodotto creato #%d', $product->get_id()));
        }else{
            $this->log(sprintf('--- Prodotto aggiornato #%d', $product->get_id()));
        }
        $this->log('--- Parsing up sells...');
        $upSells = $CSVRow->getUpsells();
        if(!empty($upSells)){
            $this->upSellsToAssign[$product->get_id()] = $upSells;
        }
        $this->log('--- Parsing cross sells...');
        $crossSells = $CSVRow->getCrossSells();
        if(!empty($crossSells)){
            $this->crossSellsToAssign[$product->get_id()] = $crossSells;
        }
    }

    /**
     * @return void
     * @throws ImportProductsException
     * @throws \WC_Data_Exception
     */
    private function createOrUpdateVariation(): void
    {
        $CSVRow = $this->currentCSVRow;
        $parentProduct = $this->getProductByGroupId($CSVRow->getGroupId());
        if ($parentProduct === null) {
            $this->log(sprintf('--- Il prodotto variabile con SKU %s non esiste. Verrà creato.', $CSVRow->getParentSku()));
            $parentProduct = new \WC_Product_Variable();
            $parentProduct->set_name($CSVRow->getTitle());
            $slug = $CSVRow->getSlug();
            if(\is_string($slug) && $slug !== ''){
                $parentProduct->set_slug($slug);
            }
            $parentProduct->set_sku($CSVRow->getGroupId());
            $parentProduct->set_short_description($CSVRow->getShortDescription());
            $parentProduct->set_description($CSVRow->getLongDescription());
            $parentProduct->update_meta_data('_group_id', $CSVRow->getGroupId());
            $parentProduct->set_manage_stock(false);
            $parentProduct->set_status('draft');
            $this->log('--- Assegnazione custom fields...');
            foreach ($CSVRow->getMetaFields() as $customFieldData) {
                $canAssign = \in_array($customFieldData['assign_to'],[
                    ImportExportCSVColumnHelpers::METADATA_MODIFIER_ONLY_PARENT,
                    ImportExportCSVColumnHelpers::METADATA_MODIFIER_BOTH_PARENT_AND_VARIATIONS
                ],true);
                if(!$canAssign){
                    continue;
                }
                $customFieldKey = $customFieldData['key'];
                $customFieldValue = $customFieldData['value'];
                if($customFieldValue !== null && $customFieldValue !== ''){
                    $this->log('---- '.$customFieldKey);
                    $parentProduct->add_meta_data($customFieldKey,$customFieldValue);
                }
            }
            $id = $parentProduct->save();
            if($id === 0){
                throw new ImportProductsException(sprintf('ERRORE: impossibile creare prodotto con SKU %s', $CSVRow->getParentSku()));
            }
            if($CSVRow->hasBrand()){
                $this->log('--- Assegnazione brand: '.$CSVRow->getBrand());
                $this->addHierarchicalTermsToObjectFromTermListString($parentProduct->get_id(), $CSVRow->getBrand(), $this->getBrandTaxonomyName());
            }
            if($CSVRow->hasCategory()){
                $this->log('--- Assegnazione categorie: '.$CSVRow->getCategory());
                $this->addHierarchicalTermsToObjectFromTermListString($parentProduct->get_id(), $CSVRow->getCategory(), 'product_cat');
            }
            // this save is not really necessary because the parent product
            // will be saved again after the creation of its variations
            // $parentProduct->save();
            $this->log(sprintf('--- Creato prodotto variabile con ID #%s', $parentProduct->get_id()));
            $this->log('--- Parsing up sells...');
            $upSells = $CSVRow->getUpsells();
            if(!empty($upSells)){
                $this->upSellsToAssign[$parentProduct->get_id()] = $upSells;
            }
            $this->log('--- Parsing cross sells...');
            $crossSells = $CSVRow->getCrossSells();
            if(!empty($crossSells)){
                $this->crossSellsToAssign[$parentProduct->get_id()] = $crossSells;
            }
        }else{
            $this->log(sprintf('--- Il prodotto variabile con SKU %s esiste.', $CSVRow->getParentSku()));
            if(!$parentProduct instanceof \WC_Product){
                throw new ImportProductsException('Impossibile recuperare il prodotto variabile');
            }
            /*$parentProduct->set_short_description($CSVRow->getShortDescription());
            $parentProduct->set_description($CSVRow->getLongDescription());
            $parentProduct->set_manage_stock(false);
            $this->log(sprintf('--- Aggiornato prodotto variabile con ID #%s', $parentProduct->get_id()));*/
        }
        $this->setImportedProductMeta($parentProduct->get_id());
        $this->addParsedProductId($parentProduct->get_id());

        $attributes = [];
        $attributesTerms = [];
        $attributesForVariableProductTermsSlugs = [];
        if($CSVRow->hasSize()){
            $sizeTerm = $this->getOrCreateTerm($this->getSizeTaxonomyName($parentProduct->get_id()), $CSVRow->getSize());
            $attributes[$sizeTerm->taxonomy] = $sizeTerm->slug;
            $attributesTerms[] = $sizeTerm;
        }
        if($CSVRow->hasColor()){
            $colorTerm = $this->getOrCreateTerm($this->getColorTaxonomyName($parentProduct->get_id()), $CSVRow->getColor());
            $attributes[$colorTerm->taxonomy] = $colorTerm->slug;
            $attributesTerms[] = $colorTerm;
        }

        $isVariationNew = true;
        $productId = $this->getProductIdByProductIdentifier($CSVRow->getIdentifier());
        if(is_int($productId) && $productId > 0){
            $this->log(sprintf('--- La variazione con IDENTIFICATORE %s esiste.', $CSVRow->getIdentifier()));
            $product = wc_get_product($productId);
            if(!$product instanceof \WC_Product_Variation){
                throw new ImportProductsException('Impossibile recuperare la variazione');
            }
            $isVariationNew = false;
            if($this->mustUpdatePrices && $CSVRow->hasRegularPrice()){
                $product->set_regular_price($CSVRow->getRegularPrice());
            }
            if($this->mustUpdateStocks && $CSVRow->hasStock()){
                $product->set_stock_quantity($CSVRow->getStock());
                $product->set_stock_status($CSVRow->getStockStatus());
            }
        }else{
            $product = new \WC_Product_Variation();
            $product->set_parent_id($parentProduct->get_id());
            $product->set_sku($CSVRow->getSku());
            $product->set_stock_quantity($CSVRow->getStock());
            $product->set_stock_status($CSVRow->getStockStatus());
            $product->set_regular_price($CSVRow->getRegularPrice());
        }

        $product->set_name($CSVRow->getTitle());
        if(!empty($attributes)){
            $product->set_attributes($attributes);
        }
        $product->set_manage_stock(true);
        $product->set_status('publish');
        $this->log('--- Assegnazione custom fields...');
        foreach ($CSVRow->getMetaFields() as $customFieldData) {
            $canAssign = \in_array($customFieldData['assign_to'],[
                ImportExportCSVColumnHelpers::METADATA_MODIFIER_ONLY_VARIATION,
                ImportExportCSVColumnHelpers::METADATA_MODIFIER_BOTH_PARENT_AND_VARIATIONS
            ],true);
            if(!$canAssign){
                continue;
            }
            $customFieldKey = $customFieldData['key'];
            $customFieldValue = $customFieldData['value'];
            if($customFieldValue !== null && $customFieldValue !== ''){
                $this->log('---- '.$customFieldKey);
                $product->add_meta_data($customFieldKey,$customFieldValue);
            }
        }
        $id = $product->save();
        if ($id === 0) {
            throw new ImportProductsException(sprintf('ERRORE: impossibile creare variazione con IDENTIFICATORE %s', $CSVRow->getIdentifier()));
        }
        $this->setImportedProductMeta($id);

        if($isVariationNew){
            $this->log(sprintf('--- Creata variazione con ID #%d', $product->get_id()));
        }else{
            $this->log(sprintf('--- Aggiornata variazione con ID #%d', $product->get_id()));
        }

        $parentAttributes = $parentProduct->get_attributes();
        $attributesTermsIdToAdd = [];
        foreach ($attributesTerms as $attrTerm){
            /** @var \WC_Product_Attribute $productAttribute */
            $productAttribute = $parentAttributes[$attrTerm->taxonomy] ?? null;
            if($productAttribute === null) {
                $productAttribute = new \WC_Product_Attribute();
                $productAttribute->set_id(wc_attribute_taxonomy_id_by_name($attrTerm->taxonomy));
                $productAttribute->set_name($attrTerm->taxonomy);
                $productAttribute->set_visible(true);
                $options = [$attrTerm->term_id];
                $productAttribute->set_options($options);
                if(\in_array($attrTerm->slug,$attributesForVariableProductTermsSlugs,true)){
                    $productAttribute->set_variation(false);
                }else{
                    $productAttribute->set_variation(true);
                }
            }else{
                $options = $productAttribute->get_options();
                if (!in_array($attrTerm->term_id, $options)) {
                    $options[] = $attrTerm->term_id;
                }
                $productAttribute->set_options($options);
            }
            $parentAttributes[$attrTerm->taxonomy] = $productAttribute;
            $attributesTermsIdToAdd[$attrTerm->taxonomy] = $options;
        }

        $parentProduct->set_attributes($parentAttributes);
        $parentProduct->save();

        if(!empty($attributesTermsIdToAdd)){
            foreach ($attributesTermsIdToAdd as $taxonomy => $terms){
                wp_set_object_terms($parentProduct->get_id(), $terms, $taxonomy, false);
            }
        }

        $this->variablesProductsToSync[$parentProduct->get_id()][] = $product->get_id();
    }

    /**
     * @param int $productId
     * @return void
     */
    private function setImportedProductMeta(int $productId): void
    {
        try {
            $today = new \DateTime('now', new \DateTimeZone('Europe/Rome'));
            update_post_meta($productId,'_imported','1');
            if(get_post_meta($productId,'_imported_creation_date',true) === ''){
                update_post_meta($productId,'_imported_creation_date',$today->format('Y-m-d_His'));
            }
            update_post_meta($productId,'_imported_update_date',$today->format('Y-m-d_His'));
        }catch (\Exception | \Throwable $e){
            $this->log('ERRORE - setImportedProductMeta(): '.$e->getMessage());
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function setLocalFileAsParsed(): void
    {
        $parsedDestination = $this->importDirPath.'/parsed';
        if (!is_dir($parsedDestination)) {
            if(!mkdir($parsedDestination, 0777, true) && !is_dir($parsedDestination)){
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $parsedDestination));
            }
        }
        $parsedDestination .= '/' . basename($this->sourceFilePath);
        if(!rename($this->sourceFilePath,$parsedDestination)) {
            $this->log(sprintf('ERRORE: Failed to move file %s', $this->sourceFilePath));
        }
    }

    /**
     * @param int $objectId
     * @param string $termListString
     * @param string $taxonomy
     * @return void
     * @throws ImportProductsException
     */
    private function addTermsToObjectFromTermListString(int $objectId, string $termListString, string $taxonomy): void {
        $termNames = explode('|', $termListString);
        $termIds = [];
        foreach ($termNames as $tm) {
            $t = get_term_by('name', $tm, $taxonomy);
            if (empty($t)) {
                $creationResult = wp_insert_term($tm,$taxonomy,['parent' => 0]);
                if(is_wp_error($creationResult)){
                    throw new ImportProductsException(sprintf('Impossibile creare il termine %s nella tassonomia %s',$tm,$taxonomy));
                }
                $termIds[] = $creationResult['term_id'];
            }else{
                $termIds[] = $t->term_id;
            }
        }

        if (count($termIds) > 0) {
            wp_set_object_terms($objectId, $termIds, $taxonomy, false);
            if ($taxonomy === 'product_cat') {
                $this->removeDefaultProductCatFromObject($objectId);
            }
        }
    }

    /**
     * @param int $objectId
     * @param string $termListString
     * @param string $taxonomy
     * @return void
     * @throws ImportProductsException
     */
    private function addHierarchicalTermsToObjectFromTermListString(int $objectId, string $termListString, string $taxonomy): void {
        //$termIds = [];
        $termNames = explode('>', $termListString);
        //Creation
        $lastTermParent = 0;
        $parsedTerms = [];
        foreach ($termNames as $tm) {
            $tm = trim($tm);
            //$currentExistingTerm = get_term_by('name', $tm, $taxonomy);
            $currentExistingTermResult = get_terms(
                [
                    'taxonomy' => $taxonomy,
                    'name' => $tm,
                    'parent' => $lastTermParent,
                    'number' => 1,
                    'hide_empty' => false,
                ]
            );
            if(\is_array($currentExistingTermResult) && !empty($currentExistingTermResult)){
                $currentExistingTerm = $currentExistingTermResult[0];
            }else{
                $currentExistingTerm = false;
            }
            if(!$currentExistingTerm){
                $creationResult = wp_insert_term($tm,$taxonomy,['parent' => $lastTermParent]);
                if(is_wp_error($creationResult)){
                    throw new ImportProductsException(sprintf('Impossibile creare il termine %s nella tassonomia %s',$tm,$taxonomy));
                }
                $this->log(sprintf('---- Creato termine %s nella tassonomia %s con parent %d',$tm,$taxonomy,$lastTermParent));
                $lastTermParent = $creationResult['term_id'];
                $parsedTerms[] = get_term_by('term_id', $creationResult['term_id'], $taxonomy);
            }else{
                $lastTermParent = $currentExistingTerm->term_id;
                $parsedTerms[] = $currentExistingTerm;
            }
        }
        //Assigning
        /*$parent = 0;
        foreach ($termNames as $tm) {
            $tm = trim($tm);
            $terms = get_terms(
                [
                    'taxonomy' => $taxonomy,
                    'name' => $tm,
                    'parent' => $parent,
                    'number' => 1,
                    'hide_empty' => false,
                ]
            );
            if (is_wp_error($terms)) {
                $this->log(
                    sprintf(
                        'Failed to retrieve term. Term name: %s, Taxonomy: %s, WP Error %s',
                        $tm,
                        $taxonomy,
                        $terms->get_error_message()
                    )
                );
                break;
            }

            $t = $terms[0] ?? null;
            if (empty($t)) {
                $this->log(sprintf('Term `%s` from taxonomy `%s` does not exists', $tm, $taxonomy));
                break;
            }

            $termIds[] = $t->term_id;
            $parent = $t->term_id;
        }*/

        $termIds = \is_array($parsedTerms) ? wp_list_pluck($parsedTerms,'term_id') : [];

        if (count($termIds) > 0) {
            wp_set_object_terms($objectId, $termIds, $taxonomy, false);
            if ($taxonomy === 'product_cat') {
                $this->removeDefaultProductCatFromObject($objectId);
            }
        }
    }

    /**
     * @param int $objectId
     * @return void
     */
    private function removeDefaultProductCatFromObject(int $objectId): void
    {
        $defaultCatId = get_option('default_product_cat');
        if (empty($defaultCatId)) {
            return;
        }

        $cat = get_term($defaultCatId);
        if (is_wp_error($cat)) {
            $this->log(sprintf('Failed to retrieve default product category `%s`', $defaultCatId));
            return;
        }

        if (empty($cat)) {
            return;
        }

        wp_remove_object_terms($objectId, $cat->term_id, 'product_cat');
    }

    /**
     * @param string $taxonomy
     * @param string $name
     * @throws \RuntimeException
     * @return \WP_Term
     */
    private function getOrCreateTerm(string $taxonomy, string $name): \WP_Term
    {
        return Utilities::getOrCreateTerm($taxonomy,$name);
    }

    /**
     * @param string $identifier
     * @return int|null
     */
    private function getProductIdByProductIdentifier(string $identifier): ?int
    {
        if($this->productIdentifier === '_sku'){
            $productId = wc_get_product_id_by_sku($identifier);
            if(!\is_int($productId) || $productId === 0){
                return null;
            }
            return $productId;
        }
        return Posts::getPostIdByMeta($this->productIdentifier,$identifier);
    }

    /**
     * @param string $sku
     * @return \WC_Product|null
     */
    private function getProductByGroupId(string $sku): ?\WC_Product
    {
        global $wpdb;

        $sql = <<<SQL
select pm.post_id from $wpdb->postmeta pm where pm.meta_key = '_group_id' and pm.meta_value = %s
SQL;
        $res = $wpdb->get_var($wpdb->prepare($sql, $sku));
        if ($res === null) {
            return null;
        }

        $product = wc_get_product((int)$res);
        if (empty($product)) {
            return null;
        }

        return $product;
    }

    /**
     * @param int|null $postId
     * @return string
     */
    private function getBrandTaxonomyName(int $postId = null): string
    {
        return apply_filters('wawoo_products_importer/brand_taxonomy_name',$this->brandTaxonomyName,$postId);
    }

    /**
     * @param int|null $postId
     * @return string
     */
    private function getColorTaxonomyName(int $postId = null): string
    {
        return apply_filters('wawoo_products_importer/color_taxonomy_name',$this->colorTaxonomyName,$postId);
    }

    /**
     * @param int|null $postId
     * @return string
     */
    private function getSizeTaxonomyName(int $postId = null): string
    {
        return apply_filters('wawoo_products_importer/size_taxonomy_name',$this->sizeTaxonomyName,$postId);
    }

    /**
     * @param array $rowData
     * @return ImportProductsCSVRow
     */
    private function createCSVColumnInstance(array $rowData): ImportProductsCSVRow
    {
        $currentCSVRow = new ImportProductsCSVRow($rowData, $this->manifestFileHelper);
        $currentCSVRow->setBrandTaxonomyName($this->getBrandTaxonomyName());
        $currentCSVRow->setColorAttributeTaxonomyName($this->getColorTaxonomyName());
        $currentCSVRow->setSizeAttributeTaxonomyName($this->getSizeTaxonomyName());
        return $currentCSVRow;
    }

    /**
     * @param int $productId
     * @return void
     */
    private function addParsedProductId(int $productId): void
    {
        if(!\is_array($this->parsedProductIds)){
            $this->parsedProductIds = [];
        }
        if(!\in_array($productId,$this->parsedProductIds,true)){
            $this->parsedProductIds[] = $productId;
        }
    }

    /**
     * @return array|false
     */
    private function fetchFiles(): array
    {
        $files = glob($this->importDirPath.'/*.csv');
        if(!\is_array($files)){
            return [];
        }
        // sort files by last modified date
        usort($files, static function($x, $y) {
            $mtx = filemtime($x);
            $mty = filemtime($y);
            if($mtx === $mty){

            }
            return $mtx < $mty ? -1 : 1;
        });
        return $files;
    }
}
