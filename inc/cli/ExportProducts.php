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
     * @var string[]
     */
    protected $excludedColumns;
    /**
     * @var string[]
     */
    protected $customColumns;
    /**
     * @var string[]
     */
    protected $includedMetas;
    /**
     * @var array
     */
    protected $columnsRenameMap;
    /**
     * @var int
     */
    protected $limit;
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
     * [--limit]
     * : The number of products to export
     *
     * [--exclude-cols]
     * : Comma separated list of columns to exclude in the generated file
     *
     * [--include-meta]
     * : Comma separated list of meta to include in the generated file
     *
     * [--manifest]
     * : Absolute path to a manifest file
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
                $this->outputFilePath = $assoc_args['output-dir-path'];
            }
            if(isset($assoc_args['output-file-name']) && \is_string($assoc_args['output-file-name']) && $assoc_args['output-file-name'] !== ''){
                $this->outputFileName = $assoc_args['output-file-name'];
            }
            if(isset($assoc_args['exclude-cols']) && \is_string($assoc_args['exclude-cols']) && $assoc_args['exclude-cols'] !== ''){
                $excludedCols = explode(',',$assoc_args['exclude-cols']);
                $this->excludedColumns = $excludedCols;
            }
            if(isset($assoc_args['include-meta']) && \is_string($assoc_args['include-meta']) && $assoc_args['include-meta'] !== ''){
                $includedMeta = explode(',',$assoc_args['include-meta']);
                $this->includedMetas = $includedMeta;
            }
            if(isset($assoc_args['limit']) && \is_string($assoc_args['limit']) && $assoc_args['limit'] !== ''){
                $limit = (int) $assoc_args['limit'];
                if($limit > 0){
                    $this->limit = $limit;
                    $this->log('Exporting '.$this->limit.' products');
                }
            }
            if(isset($assoc_args['manifest']) && \is_string($assoc_args['manifest']) && $assoc_args['manifest'] !== ''){
                $manifestFile = $assoc_args['manifest'];
                if(\is_file($manifestFile)){
                    $this->log('Using manifest: '.$manifestFile);
                    try{
                        $this->parseManifestFile($manifestFile);
                    }catch (\RuntimeException $e){
                        $this->error($e->getMessage(),false);
                    }
                }else{
                    $this->log('Error: invalid manifest file at '.$manifestFile);
                }
            }
            if(isset($this->excludedColumns)){
                $this->log('Excluded columns: '.implode(',',$this->excludedColumns));
            }
            if(isset($this->customColumns)){
                $this->log('Custom columns: '.implode(',',$this->customColumns));
            }
            if(isset($this->includedMetas)){
                $this->log('Included meta: '.implode(',',$this->includedMetas));
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
            if(!isset($this->columnsRenameMap) || !\is_array($this->columnsRenameMap)){
                $csv->insertOne($this->csvColumns);
            }else{
                $csvColumns = $this->csvColumns;
                foreach ($this->columnsRenameMap as $renameEntry){
                    foreach ($csvColumns as $k => $column){
                        if($column === $renameEntry['src']){
                            $csvColumns[$k] = $renameEntry['dest'];
                        }
                    }
                }
                $csv->insertOne($csvColumns);
            }
            $bundleRecords = [];
            foreach ($this->getRecords() as $record) {
                try{
                    if(isset($record['id'])){
                        if($record['type'] === 'bundle'){
                            $bundleRecords[] = $record; //We need bundles at the end
                        }else{
                            $csv->insertOne(array_values($record));
                        }
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
            if(count($bundleRecords) > 0){
                foreach ($bundleRecords as $record){
                    $csv->insertOne(array_values($record));
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
            'type',
            'sku',
            'sku_parent',
            'name',
            'description',
            'short_description'
        ];
        $metaColumns = [
            'meta:_regular_price',
            'meta:_sale_price',
            'meta:_stock',
            'meta:_stock_status',
            'meta:_manage_stock'
        ];
        if(\is_array($this->includedMetas) && count($this->includedMetas) > 0){
            foreach ($this->includedMetas as $includedMeta){
                $metaColumns[] = 'meta:'.$includedMeta;
            }
        }
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

        $mediaColumns = [
            'featured_image',
            'gallery'
        ];

        if(isset($this->customColumns) && is_array($this->customColumns)){
            $customColumns = $this->customColumns;
        }else{
            $customColumns = [];
        }

        $csvColumns = array_merge($standardColumns,$metaColumns,$taxColumns,$finalAttColumns,$mediaColumns,$customColumns);

        if(\is_array($this->excludedColumns) && count($this->excludedColumns) > 0){
            $csvColumns = array_values(array_diff($csvColumns,$this->excludedColumns));
        }

        $this->csvColumns = $csvColumns;
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
            'posts_per_page' => $this->limit ?? -1,
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
     * @param string $attributeKey
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

    /**
     * Parse the manifest file and store
     * @param string $manifestFilePath
     * @throws \RuntimeException
     */
    private function parseManifestFile(string $manifestFilePath): void
    {
        $content = file_get_contents($manifestFilePath);
        if(!\is_string($content)){
            throw new \RuntimeException('Error: unable to parse the manifest file at '.$manifestFilePath);
        }
        $jsonContent = json_decode($content,ARRAY_A);
        if(json_last_error() === JSON_ERROR_NONE){
            if(isset($jsonContent['rename_columns']) && \is_array($jsonContent['rename_columns'])){
                foreach ($jsonContent['rename_columns'] as $src => $dest){
                    $this->columnsRenameMap[] = [
                        'src' => $src,
                        'dest' => $dest
                    ];
                }
            }
            if(isset($jsonContent['exclude_columns']) && \is_array($jsonContent['exclude_columns'])){
                if(isset($this->excludedColumns)){
                    $this->excludedColumns = array_unique(array_merge($this->excludedColumns,$jsonContent['exclude_columns']));
                }else{
                    $this->excludedColumns = array_merge($jsonContent['exclude_columns']);
                }
            }
            if(isset($jsonContent['custom_columns']) && \is_array($jsonContent['custom_columns'])){
                if(isset($this->customColumns)){
                    $this->customColumns = array_unique(array_merge($this->customColumns,$jsonContent['custom_columns']));
                }else{
                    $this->customColumns = array_merge($jsonContent['custom_columns']);
                }
                //Custom columns will be automatically renamed to exclude the function name
                foreach ($this->customColumns as $customColumn){
                    preg_match('|([a-zA-Z]+):|',$customColumn,$columnNameRegExResults);
                    if(\is_array($columnNameRegExResults) && isset($columnNameRegExResults[1])){
                        $this->columnsRenameMap[] = [
                            'src' => 'cs:'.$customColumn, //cs: is added
                            'dest' => $columnNameRegExResults[1]
                        ];
                    }
                }
                $this->customColumns = array_map(static function($el){ return 'cs:'.$el; },$this->customColumns);
            }
            if(isset($jsonContent['include_meta']) && \is_array($jsonContent['include_meta'])){
                if(isset($this->includedMetas)){
                    $this->includedMetas = array_unique(array_merge($this->includedMetas,$jsonContent['include_meta']));
                }else{
                    $this->includedMetas = array_merge($jsonContent['include_meta']);
                }
            }
        }else{
            throw new \RuntimeException('Error: unable to parse the manifest file: '.json_last_error_msg());
        }
    }
}
