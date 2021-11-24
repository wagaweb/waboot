<?php

namespace Waboot\inc\cli;

use League\Csv\Writer;
use Waboot\inc\cli\product_export\ExportableProductFactory;
use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\cli\CLIRuntimeException;

class ExportProducts extends AbstractCommand
{
    /**
     * @var string
     */
    protected $logDirName = 'export-products';
    /**
     * @var string
     */
    protected $logFileName = 'export-products';
    /**
     * @var int[]
     */
    protected $providedIds = [];
    /**
     * @var string
     */
    protected $outputFileName;
    /**
     * @var string
     */
    protected $outputFilePath;
    /**
     * @var string
     */
    protected $language;
    /**
     * @var array
     */
    private $csvColumns;

    /**
     * Generate a CSV with all products
     *
     * ## OPTIONS
     *
     * [--products]
     * : Comma-separated id of the products to export (variable products will automatically export all variations)
     *
     * [--marker]
     * : Insert a text before and after the log entry
     *
     * [--output-file-name]
     * : Specify the output file name
     *
     * [--output-file-path]
     * : Specify the output file path
     *
     * [--lang]
     * : Set the language to use
     *
     * ## EXAMPLES
     *
     *      wp wb:export-products
     */
    public function __invoke($args, $assoc_args): int
    {
        parent::__invoke($args,$assoc_args);
        if($this->dryRun){
            $this->log('### DRY-RUN ###');
        }
        try{
            if(isset($assoc_args['products'])){
                $providedIds = explode(',',$assoc_args['products']);
                if(\is_array($providedIds)){
                    $this->providedIds = $providedIds;
                }
            }
            $this->language = $assoc_args['lang'] ?? 'it';
            $this->buildOutputFileNameAndDir();
            if(isset($assoc_args['output-dir-path']) && \is_string($assoc_args['output-dir-path']) && $assoc_args['output-dir-path'] !== ''){
                $this->outputFileName = $assoc_args['output-dir-path'];
            }
            if(isset($assoc_args['output-file-name']) && \is_string($assoc_args['output-file-name']) && $assoc_args['output-file-name'] !== ''){
                $this->outputFilePath = $assoc_args['output-file-name'];
            }
            $this->beginCommandExecution();
            if(!$this->hasProducts()){
                throw new \RuntimeException('Error: no products found');
            }
            $this->generateCSVColumns();
            $this->generateCSV();
            $this->success('Operation completed');
            $this->endCommandExecution();
            return 0;
        }catch (\Exception $e){
            $this->error($e->getMessage());
            return 1;
        }
    }

    public function generateCSV(): void
    {
        if (!wp_mkdir_p($this->outputFilePath)) {
            throw new \RuntimeException('Unable to create directory: ' . $this->outputFilePath);
        }
        $outputFile = rtrim($this->outputFilePath,'/') . '/' . $this->outputFileName;
        try{
            if(\is_file($outputFile)){
                unlink($outputFile);
            }
            $this->log('Writing of: '.$outputFile);
            $csv = Writer::createFromPath($outputFile,'w+');
            $csv->setDelimiter(';');
            $csv->insertOne($this->csvColumns);
            foreach ($this->getRecords() as $record) {
                try{
                    if(isset($record['id'])){
                        $csv->insertOne(array_values($record));
                    }else{
                        //Multidimensional array (variable product and variations)
                        foreach ($record as $r){
                            $csv->insertOne($r);
                        }
                    }
                }catch (\Exception $e){
                    $this->log('- Error: '.$e->getMessage());
                }
            }
            $this->log('File written: '.$outputFile);
        }catch (\Exception $e){
            $this->log('Error: '.$e->getMessage());
        }
    }

    public function generateCSVColumns(): void
    {
        $standardColumns = [
            'id',
            'parent_id',
            'sku',
            'name',
            'description',
            'short_description'
        ];
        $metaColumns = [
            'meta:_regular_price',
            'meta:_sale_price'
        ];
        $taxColumns = [];
        $productTaxonomies = $this->getProductTaxonomies();
        foreach ($productTaxonomies as $taxonomyName){
            $productTax = get_taxonomy($taxonomyName);
            if(!$productTax instanceof \WP_Taxonomy){
                continue;
            }
            $taxColumns[] = $this->getColumnNameFromTaxonomy($productTax);
        }
        $attColumns = [];
        foreach ($this->getProducts() as $product) {
            $attributes = $product->get_attributes();
            if(\is_array($attributes) && count($attributes) > 0){
                foreach ($attributes as $attKey => $att){
                    $attColumnName = $this->getColumnNameFromAttribute($att,$attKey);
                    if($attColumnName === false){
                        continue;
                    }
                    if(\in_array($attColumnName,$attColumns,true)){
                        continue;
                    }
                    $attColumns[] = $attColumnName;
                }
            }
        }

        $finalAttColumns = $attColumns;
        //For attributes available for variations we end up with "attribute:pa_color:Prodotto Color:taxonomy" and "attribute:pa_color:Prodotto Color:variations:taxonomy" columns
        //We want to remove the first
        foreach ($attColumns as $attColumn){
            if(!strpos($attColumn,':variations')){
                continue;
            }
            $attColumnSingleName = str_replace(':variations','',$attColumn);
            foreach ($attColumns as $k => $innerAttColumn){
                if($innerAttColumn === $attColumnSingleName){
                    unset($finalAttColumns[$k]);
                    break;
                }
            }
        }

        $this->csvColumns = array_merge($standardColumns,$metaColumns,$taxColumns,$finalAttColumns);
    }

    /**
     * @return bool
     */
    public function hasProducts(): bool
    {
        return $this->getProducts() !== null;
    }

    /**
     * @return \Generator|null
     */
    public function getRecords(): ?\Generator
    {
        if(!$this->getProducts()){
            return null;
        }
        foreach ($this->getProducts() as $product) {
            $exportableProduct = ExportableProductFactory::create($product);
            yield $exportableProduct->createRecord($this->csvColumns);
        }
    }

    /**
     * @return \Generator|null
     */
    public function getProducts(): ?\Generator
    {
        $qArgs = [
            'post_type' => ['product'],
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'fields' => 'ids'
        ];
        if(isset($this->providedIds) && count($this->providedIds) !== 0){
            $qArgs['post__in'] = $this->providedIds;
        }
        $productsIds = get_posts($qArgs);
        if(!\is_array($productsIds) || count($productsIds) === 0){
            return null;
        }
        foreach ($productsIds as $productsId){
            yield wc_get_product($productsId);
        }
    }

    /**
     * @throws \Exception
     */
    private function buildOutputFileNameAndDir(): void
    {
        $today = new \DateTime('now', new \DateTimeZone('Europe/Rome'));
        $this->outputFileName = 'products-export-'.$today->format('Y_m_d_hi').'.csv';
        $this->outputFilePath = WP_CONTENT_DIR.'/product-exports';
    }

    /**
     * @param \WC_Product_Attribute $attribute
     * @return false|string
     */
    private function getColumnNameFromAttribute(\WC_Product_Attribute $attribute, string $attributeKey)
    {
        if($attribute->is_taxonomy()){
            $columnName = 'attribute:'.$attribute->get_name();
            $attTaxonomyName = $attribute->get_taxonomy();
            $attTaxonomy = get_taxonomy($attTaxonomyName);
            if(!$attTaxonomy instanceof \WP_Taxonomy){
                return false;
            }
            $columnName .= ':'.$attTaxonomy->label;
        }else{
            $columnName = 'attribute:'.$attributeKey;
            $columnName .= ':'.$attribute->get_name();
        }
        if($attribute->get_variation()){
            $columnName .= ':variations';
        }
        if($attribute->is_taxonomy()){
            $columnName .= ':taxonomy';
        }
        return $columnName;
    }

    /**
     * @return array
     */
    private function getProductTaxonomies(): array
    {
        $taxonomies = get_taxonomies([],'objects');
        $productTaxonomies = [];
        $excludedTaxonomies = ['product_type','product_visibility','product_shipping_class'];
        if(!\is_array($taxonomies)){
            return $productTaxonomies;
        }
        foreach ($taxonomies as $taxonomy){
            if(!$taxonomy instanceof \WP_Taxonomy){
                continue;
            }
            if(strpos($taxonomy->name,'pa_') === 0){
                continue;
            }
            if(\in_array($taxonomy->name,$excludedTaxonomies,true)){
                continue;
            }
            if(\in_array('product',$taxonomy->object_type,true)){
                $productTaxonomies[] = $taxonomy->name;
            }
        }
        return $productTaxonomies;
    }

    /**
     * @param \WP_Taxonomy $taxonomy
     * @return string
     */
    private function getColumnNameFromTaxonomy(\WP_Taxonomy $taxonomy): string
    {
        $columnName = 'taxonomy:'.$taxonomy->name;
        $columnName .= ':'.$taxonomy->label;
        return $columnName;
    }
}
