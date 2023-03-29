<?php

namespace Waboot\inc\cli\product_import;

use Waboot\inc\cli\utils\ImportExportCSVColumnHelpers;
use Waboot\inc\core\cli\AbstractCSVParserCommand;
use Waboot\inc\core\utils\Posts;
use Waboot\inc\core\utils\Utilities;
use function Waboot\inc\adjustPriceMeta;
use function Waboot\inc\syncVariableProductData;

class ImportProducts extends AbstractCSVParserCommand
{
    /**
     * @var string
     */
    protected $logDirName = 'import-new-products';
    /**
     * @var string
     */
    protected $logFileName = 'import-new-products';
    protected ?string $productIdentifierColumnName = null;
    protected ?string $brandTaxonomyName = null;
    protected ?string $colorTaxonomyName = null;
    protected ?string $sizeTaxonomyName = null;
    protected ?bool $mustUpdatePrices = null;
    protected ?bool $mustUpdateStocks = null;
    protected ?array $variablesProductsToSync = null;
    /**
     * @var int[]
     */
    protected ?array $parsedProductIds = null;
    /**
     * @var string[]
     */
    protected ?array $upSellsToAssign = null;
    /**
     * @var string[]
     */
    protected ?array $crossSellsToAssign = null;
    /**
     * @var ImportProductsCSVRow
     */
    protected $currentCSVRow;
    protected ImportProductsManifestFile $manifestFileHelper;
    protected ?array $excludedColumns  = null;

    public static function getCommandDescription(): array
    {
        $description = parent::getCommandDescription();
        $description['shortdesc'] = 'Importa i prodotti';
        $description['longdesc'] = '## EXAMPLES' . "\n\n" . 'wp wawoo:import-products --file=baz.csv';
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'manifest',
            'description' => 'Absolute path to a manifest file',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'product-identifier-column-name',
            'description' => 'Specifies the column name containing the product identifier (eg: SKU)',
            'default' => 'meta:_sku',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'brand-taxonomy',
            'description' => 'Specifies brand taxonomy name (eg: product_brand)',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'color-taxonomy',
            'description' => 'Specifies color attribute taxonomy name (eg: pa_color)',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'size-taxonomy',
            'description' => 'Specifies size attribute taxonomy name (eg: pa_size)',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'flag',
            'name' => 'update-prices',
            'description' => 'Specifies whether to update the prices for existing products',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'flag',
            'name' => 'update-stocks',
            'description' => 'Specifies whether to update the stocks for existing products',
            'optional' => true,
        ];
        return $description;
    }

    public function customInitialization($args, $assoc_args): void
    {
        if(isset($assoc_args['manifest']) && \is_string($assoc_args['manifest']) && $assoc_args['manifest'] !== ''){
            $manifestFile = $assoc_args['manifest'];
            try{
                $this->parseManifestFile($manifestFile);
                $this->log('Using manifest: '.$manifestFile);
            }catch (\Exception | \Throwable $e){
                throw new \RuntimeException($e->getMessage());
            }
        }
        if(!isset($this->productIdentifierColumnName)){
            $this->productIdentifierColumnName = $assoc_args['product-identifier-column-name'] ?? 'meta:_sku';
        }
        $this->log('Product is identified by the value in the column: '.$this->productIdentifierColumnName);
        if(!isset($this->brandTaxonomyName)){
            $this->brandTaxonomyName = $assoc_args['brand-taxonomy'] ?? 'brand_taxonomy';
        }
        $this->log('Brand taxonomy name: '.$this->brandTaxonomyName);
        if(!isset($this->colorTaxonomyName)){
            $this->colorTaxonomyName = $assoc_args['color-taxonomy'] ?? 'pa_color';
        }
        $this->log('Color attribute taxonomy name: '.$this->colorTaxonomyName);
        if(!isset($this->sizeTaxonomyName)){
            $this->sizeTaxonomyName = $assoc_args['size-taxonomy'] ?? 'pa_size';
        }
        $this->log('Size attribute taxonomy name: '.$this->sizeTaxonomyName);
        if(!isset($this->mustUpdatePrices)){
            $this->mustUpdatePrices = isset($assoc_args['update-prices']);
        }
        if($this->mustUpdatePrices === true){
            $this->log('Prices will be updated');
        }
        if(!isset($this->mustUpdateStocks)){
            $this->mustUpdateStocks = isset($assoc_args['update-stocks']);
        }
        if($this->mustUpdateStocks === true){
            $this->log('Stocks will be updated');
        }
    }

    /**
     * @param array $rowData
     * @return ImportProductsCSVRow
     */
    protected function createCSVColumnInstance(array $rowData): ImportProductsCSVRow
    {
        $currentCSVRow = new ImportProductsCSVRow($rowData, $this->productIdentifierColumnName, $this->manifestFileHelper);
        $currentCSVRow->setBrandTaxonomyName($this->getBrandTaxonomyName());
        $currentCSVRow->setColorAttributeTaxonomyName($this->getColorTaxonomyName());
        $currentCSVRow->setSizeAttributeTaxonomyName($this->getSizeTaxonomyName());
        return $currentCSVRow;
    }

    /**
     * @return void
     * @throws ImportProductsCSVRowException
     * @throws ImportProductsException
     * @throws \WC_Data_Exception
     */
    protected function parseCSVRow(): void
    {
        $this->currentCSVRow->parseRowData();
        $identifier = $this->currentCSVRow->getIdentifier();
        $this->log(sprintf('--- Parsing prodotto %s', $identifier));
        if($this->currentCSVRow->isSimpleProductRow()){
            $this->log('--- The row identifies a SIMPLE PRODUCT');
            $this->createOrUpdateSimpleProduct();
        }else{
            $this->log('--- The row identifies a PRODUCT VARIATION');
            $this->createOrUpdateVariation();
        }
    }

    /**
     * @return void
     */
    protected function onDoneParsing(): void
    {
        if(!empty($this->variablesProductsToSync)){
            $this->log('Syncing variable products...');
            foreach ($this->variablesProductsToSync as $variableProductId => $variationsIds) {
                $this->log('- Syncing product #'.$variableProductId);
                $variationsIds = array_unique($variationsIds);
                foreach ($variationsIds as $variationsId){
                    $this->log('-- Fixing price metas for product #'.$variationsId);
                    if(!$this->isDryRun()){
                        adjustPriceMeta($variationsId);
                    }
                }
                if(!$this->isDryRun()){
                    syncVariableProductData($variableProductId);
                }
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
    protected function onBeforeCommandEnd(): void
    {
        $this->log('Assigning Up Sells...');
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
                    $this->log(sprintf('-- Product %d: %s',$productId,implode(',',$idToAssigns)));
                    if(!$this->isDryRun()){
                        update_post_meta($productId,'_upsell_ids',$idToAssigns);
                    }
                }
            }
        }
        $this->log('Assigning Cross Sells...');
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
                    $this->log(sprintf('-- Product #%d: %s',$productId,implode(',',$idToAssigns)));
                    if(!$this->isDryRun()){
                        update_post_meta($productId,'_crosssell_ids',$idToAssigns);
                    }
                }
            }
        }
    }

    /**
     * Parse the manifest file and store
     * @param string $manifestFilePath
     * @throws \RuntimeException
     * @throws ImportProductsManifestFileException
     */
    protected function parseManifestFile(string $manifestFilePath): void
    {
        $manifest = new ImportProductsManifestFile($manifestFilePath);
        $this->manifestFileHelper = $manifest;
        $this->excludedColumns = $manifest->getExcludedColumns();
        $brandTaxonomyName = $manifest->getSetting('brand_taxonomy_name');
        if(\is_string($brandTaxonomyName)){
            $this->brandTaxonomyName = $brandTaxonomyName;
        }
        $colorTaxonomyName = $manifest->getSetting('color_taxonomy_name');
        if(\is_string($colorTaxonomyName)){
            $this->colorTaxonomyName = $colorTaxonomyName;
        }
        $sizeTaxonomyName = $manifest->getSetting('size_taxonomy_name');
        if(\is_string($sizeTaxonomyName)){
            $this->sizeTaxonomyName = $sizeTaxonomyName;
        }
        $mustUpdatePrices = $manifest->getSetting('update_prices');
        if(\is_bool($mustUpdatePrices)){
            $this->mustUpdatePrices = $mustUpdatePrices;
        }
        $mustUpdateStocks = $manifest->getSetting('update_stocks');
        if(\is_bool($mustUpdateStocks)){
            $this->mustUpdateStocks = $mustUpdateStocks;
        }
        $productIdentifier = $manifest->getSetting('product_identifier_column_name');
        if(\is_string($productIdentifier)){
            $this->productIdentifierColumnName = $productIdentifier;
        }
    }

    /**
     * @return void
     */
    protected function finalizeParsedProducts(): void
    {
        if(has_action('wawoo_product_importer/finalize_products')){
            $this->log('Finalizing parsed products...');
        }
        do_action('wawoo_product_importer/finalize_products',$this->parsedProductIds, $this->isDryRun(), $this);
    }

    /**
     * @return void
     * @throws ImportProductsException
     * @throws \WC_Data_Exception
     */
    protected function createOrUpdateSimpleProduct(): void
    {
        $CSVRow = $this->currentCSVRow;
        $isNew = true;
        $productId = $this->getProductIdByProductIdentifier($CSVRow->getIdentifier());
        if($productId === null){
            $this->log(sprintf('--- Product identified by %s not found. It will be CREATED.', $CSVRow->getIdentifier()));
            $product = new \WC_Product_Simple();
            $product->set_sku($CSVRow->getSku());
            $product->set_status('draft');
            if($CSVRow->hasStock()){
                $product->set_stock_quantity($CSVRow->getStock());
                $product->set_stock_status($CSVRow->getStockStatus());
            }
            if($CSVRow->hasRegularPrice()){
                $product->set_regular_price($CSVRow->getRegularPrice());
            }
        }else{
            $this->log(sprintf('--- Product identified by %s found (#%d). It will be UPDATED.', $CSVRow->getIdentifier(), $productId));
            $product = wc_get_product($productId);
            if(!$product instanceof \WC_Product){
                throw new ImportProductsException('The product retrieved was not an instance of WC_Product');
            }
            if($this->mustUpdatePrices && $CSVRow->hasRegularPrice()){
                $product->set_regular_price($CSVRow->getRegularPrice());
            }
            if($this->mustUpdateStocks && $CSVRow->hasStock()){
                $product->set_stock_quantity($CSVRow->getStock());
                $product->set_stock_status($CSVRow->getStockStatus());
            }
            $isNew = false;
        }

        $product->set_name($CSVRow->getTitle());
        if($CSVRow->hasSlug()){
            $product->set_slug($CSVRow->getSlug());
        }
        $product->set_short_description($CSVRow->getShortDescription());
        $product->set_description($CSVRow->getLongDescription());
        $product->set_manage_stock(true);

        $this->log('--- Setting custom fields...');
        foreach ($CSVRow->getCustomMetaFields() as $customFieldData) {
            $customFieldKey = $customFieldData['key'];
            $customFieldValue = $customFieldData['value'];
            if($customFieldValue !== null && $customFieldValue !== ''){
                $this->log(sprintf('---- %s: %s',$customFieldKey,$customFieldValue));
                $product->update_meta_data($customFieldKey,$customFieldValue);
            }
        }

        if(!$this->isDryRun()){
            if ($product->save()) {
                throw new ImportProductsException('Unable to create or update product identified by %s', $CSVRow->getIdentifier());
            }
        }

        /*
         * Taxonomies
         */
        $this->assignTaxonomies($product->get_id());

        /*
         * Attributes
         */
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

        $attributes = $CSVRow->getAttributesForVariableAndSimpleProducts();
        foreach ($attributes as $attributeTaxonomyName => $attributeData){
            $this->log(sprintf('--- Assegnazione termini per %s: %s',$attributeTaxonomyName,$attributeData['value']));
            $this->addTermsToObjectFromTermListString($product->get_id(),$CSVRow->getAttribute($attributeTaxonomyName),$attributeTaxonomyName);
        }

        if(!$this->isDryRun()){
            $product->save();
        }

        $this->log('-- Fixing price metas');
        if(!$this->isDryRun()){
            adjustPriceMeta($product->get_id());
        }

        if($isNew){
            $this->log(sprintf('--- Product created with ID #%d', $product->get_id()));
        }else{
            $this->log(sprintf('--- Product #%d updated', $product->get_id()));
        }

        /*
         * Related products
         */
        $this->assignRelatedProducts($product->get_id());

        if(!$this->isDryRun()){
            $this->setImportedProductMeta($product->get_id());
        }

        $this->addParsedProductId($product->get_id());
    }

    /**
     * @return void
     * @throws ImportProductsException
     * @throws \WC_Data_Exception
     */
    protected function createOrUpdateVariation(): void
    {
        $CSVRow = $this->currentCSVRow;

        /*
         * Variable Product
         */
        $parentProduct = $this->getProductByGroupId($CSVRow->getGroupId());
        if ($parentProduct === null) {
            $this->log(sprintf('--- Variable product identified by %s not found. It will be created.', $CSVRow->getParentSku()));
            $parentProduct = new \WC_Product_Variable();
            $parentProduct->set_name($CSVRow->getTitle());
            if($CSVRow->hasSlug()){
                $parentProduct->set_slug($CSVRow->getSlug());
            }
            $parentProduct->set_sku($CSVRow->getGroupId());
            $parentProduct->set_short_description($CSVRow->getShortDescription());
            $parentProduct->set_description($CSVRow->getLongDescription());
            $parentProduct->update_meta_data('_group_id', $CSVRow->getGroupId());
            $parentProduct->set_manage_stock(false);
            $parentProduct->set_status('draft');

            $this->log('--- Setting custom fields...');
            foreach ($CSVRow->getCustomMetaFields() as $customFieldData) {
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
                    $this->log(sprintf('---- %s: %s',$customFieldKey,$customFieldValue));
                    $parentProduct->update_meta_data($customFieldKey,$customFieldValue);
                }
            }

            if(!$this->isDryRun()){
                if($parentProduct->save() === 0){
                    throw new ImportProductsException(sprintf('Unable to create variable product identified by %s', $CSVRow->getParentSku()));
                }
            }

            $this->log(sprintf('--- Variable product created with ID #%d', $parentProduct->get_id()));

            /*
             * Taxonomies
             */
            $this->assignTaxonomies($parentProduct->get_id());
            /*
             * Related products
             */
            $this->assignRelatedProducts($parentProduct->get_id());
        }else{
            $this->log(sprintf('--- Variable product identified by %s found (#%d).', $CSVRow->getParentSku(), $parentProduct->get_id()));
            if(!$parentProduct instanceof \WC_Product){
                throw new ImportProductsException('The variable product retrieved was not an instance of WC_Product');
            }
        }
        if(!$this->isDryRun()){
            $this->setImportedProductMeta($parentProduct->get_id());
        }
        $this->addParsedProductId($parentProduct->get_id());

        /*
         * Variation
         */
        $isVariationNew = true;
        $productId = $this->getProductIdByProductIdentifier($CSVRow->getIdentifier());
        if($productId === null){
            $this->log(sprintf('--- Variation identified by %s non found. It will be CREATED.', $CSVRow->getIdentifier()));
            $product = new \WC_Product_Variation();
            $product->set_parent_id($parentProduct->get_id());
            $product->set_sku($CSVRow->getSku());
            if($CSVRow->hasStock()){
                $product->set_stock_quantity($CSVRow->getStock());
                $product->set_stock_status($CSVRow->getStockStatus());
            }
            if($CSVRow->hasRegularPrice()){
                $product->set_regular_price($CSVRow->getRegularPrice());
            }
        }else{
            $this->log(sprintf('--- Variation identified by %s found (#%d). It will be UPDATED.', $CSVRow->getIdentifier(), $productId));
            $product = wc_get_product($productId);
            if(!$product instanceof \WC_Product_Variation){
                throw new ImportProductsException('The variation retrieved was not an instance of WC_Product_Variation');
            }
            if($this->mustUpdatePrices && $CSVRow->hasRegularPrice()){
                $product->set_regular_price($CSVRow->getRegularPrice());
            }
            if($this->mustUpdateStocks && $CSVRow->hasStock()){
                $product->set_stock_quantity($CSVRow->getStock());
                $product->set_stock_status($CSVRow->getStockStatus());
            }
            $isVariationNew = false;
        }

        $product->set_name($CSVRow->getTitle());
        $product->set_manage_stock(true);
        $product->set_status('publish');

        $this->log('--- Setting custom fields...');
        foreach ($CSVRow->getCustomMetaFields() as $customFieldData) {
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
                $this->log(sprintf('---- %s: %s',$customFieldKey,$customFieldValue));
                $product->update_meta_data($customFieldKey,$customFieldValue);
            }
        }

        /*
         * Attributes
         */
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
        $attributesForVariableProduct = $CSVRow->getAttributesForVariableAndSimpleProducts();
        foreach ($attributesForVariableProduct as $attributeTaxonomyName => $attributeData){
            if(!$CSVRow->hasAttribute($attributeTaxonomyName)){
                continue;
            }
            try{
                $this->log(sprintf('--- Parsing terms for %s: %s',$attributeTaxonomyName,$CSVRow->getAttribute($attributeTaxonomyName)));
                $attributeValues = explode('|',$CSVRow->getAttribute($attributeTaxonomyName));
                foreach ($attributeValues as $attValue){
                    $attributeTerm = $this->getOrCreateTerm($attributeTaxonomyName, $attValue);
                    $attributesTerms[] = $attributeTerm;
                    $attributesForVariableProductTermsSlugs[] = $attributeTerm->slug;
                }
            }catch (\Exception | \Throwable $e){
                $this->log('--- ERROR: '.$e->getMessage());
            }
        }

        /*
         * Assign attributes to variation
         */
        if(!empty($attributes)){
            $product->set_attributes($attributes);
        }

        /*
         * Assign attributes to variable
         */
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

        if(!$this->isDryRun()){
            if($product->save()) {
                throw new ImportProductsException(sprintf('Unable to create or update variation identified by %s', $CSVRow->getIdentifier()));
            }
        }

        if(!$this->isDryRun()){
            $this->setImportedProductMeta($product->get_id());
        }

        if(!$this->isDryRun()){
            $parentProduct->save();
            if(!empty($attributesTermsIdToAdd)){
                foreach ($attributesTermsIdToAdd as $taxonomy => $terms){
                    wp_set_object_terms($parentProduct->get_id(), $terms, $taxonomy, false);
                }
            }
        }

        if($isVariationNew){
            $this->log(sprintf('--- Variation created with ID #%d', $product->get_id()));
        }else{
            $this->log(sprintf('--- Variation #%d updated', $product->get_id()));
        }

        $this->variablesProductsToSync[$parentProduct->get_id()][] = $product->get_id();
    }

    /**
     * @param int $productId
     * @return void
     * @throws ImportProductsException
     */
    protected function assignTaxonomies(int $productId): void
    {
        $CSVRow = $this->currentCSVRow;
        if($CSVRow->hasBrand()){
            $this->log('--- Assigning brand: '.$CSVRow->getBrand());
            if($CSVRow->isBrandHierarchical()){
                $this->addHierarchicalTermsToObjectFromTermListString($productId, $CSVRow->getBrand(), $this->getBrandTaxonomyName());
            }else{
                $this->addTermsToObjectFromTermListString($productId, $CSVRow->getBrand(), $this->getBrandTaxonomyName());
            }
        }
        if($CSVRow->hasCategory()){
            $this->log('--- Assigning categories: '.$CSVRow->getCategory());
            if($CSVRow->isProductCategoryHierarchical()){
                $this->addHierarchicalTermsToObjectFromTermListString($productId, $CSVRow->getCategory(), 'product_cat');
            }else{
                $this->addTermsToObjectFromTermListString($productId, $CSVRow->getCategory(), 'product_cat');
            }
        }
    }

    /**
     * @param int $productId
     * @return void
     */
    protected function assignRelatedProducts(int $productId): void
    {
        $this->log('--- Parsing up sells...');
        $upSells = $this->fetchUpSellsIds();
        if(!empty($upSells)){
            $this->upSellsToAssign[$productId] = $upSells;
        }
        $this->log('--- Parsing cross sells...');
        $crossSells = $this->fetchCrossSellsIds();
        if(!empty($crossSells)){
            $this->crossSellsToAssign[$productId] = $crossSells;
        }
    }

    /**
     * @param int $productId
     * @return void
     */
    protected function setImportedProductMeta(int $productId): void
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
     * @param int $objectId
     * @param string $termListString
     * @param string $taxonomy
     * @return void
     * @throws ImportProductsException
     */
    protected function addTermsToObjectFromTermListString(int $objectId, string $termListString, string $taxonomy): void {
        $termNames = explode('|', $termListString);
        $termIds = [];
        foreach ($termNames as $tm) {
            $t = get_term_by('name', $tm, $taxonomy);
            if (empty($t)) {
                $creationResult = wp_insert_term($tm,$taxonomy,['parent' => 0]);
                if(is_wp_error($creationResult)){
                    throw new ImportProductsException(sprintf('Unable to create the term %s inside the taxonomy %s: %s',$tm,$taxonomy,$creationResult->get_error_message()));
                }
                $this->log(sprintf('---- Term %s created inside taxonomy %s',$tm,$taxonomy));
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
    protected function addHierarchicalTermsToObjectFromTermListString(int $objectId, string $termListString, string $taxonomy): void {
        $termNames = explode('>', $termListString);
        //Creation
        $lastTermParent = 0;
        $parsedTerms = [];
        foreach ($termNames as $tm) {
            $tm = trim($tm);
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
                    throw new ImportProductsException(sprintf('Unable to create the term %s inside the taxonomy %s: %s',$tm,$taxonomy,$creationResult->get_error_message()));
                }
                $this->log(sprintf('---- Term %s created inside taxonomy %s and assigned to parent term %d',$tm,$taxonomy,$lastTermParent));
                $lastTermParent = $creationResult['term_id'];
                $parsedTerms[] = get_term_by('term_id', $creationResult['term_id'], $taxonomy);
            }else{
                $lastTermParent = $currentExistingTerm->term_id;
                $parsedTerms[] = $currentExistingTerm;
            }
        }

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
    protected function removeDefaultProductCatFromObject(int $objectId): void
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
    protected function getOrCreateTerm(string $taxonomy, string $name): \WP_Term
    {
        return Utilities::getOrCreateTerm($taxonomy,$name);
    }

    /**
     * @param string $identifier
     * @return int|null
     */
    public function getProductIdByProductIdentifier(string $identifier): ?int
    {
        if($this->currentCSVRow->isIdentifierTheProductSKU()){
            $productId = wc_get_product_id_by_sku($identifier);
            if(!\is_int($productId) || $productId === 0){
                return null;
            }
            return $productId;
        }
        $productId = Posts::getPostIdByMeta($this->currentCSVRow->getStandardizedColumnNameFromActualColumnName($this->productIdentifierColumnName),$identifier);
        if(!\is_int($productId) || $productId === 0){
            return null;
        }
        return $productId;
    }

    /**
     * @param string $groupId
     * @return \WC_Product|null
     */
    public function getProductByGroupId(string $groupId): ?\WC_Product
    {
        global $wpdb;

        $sql = <<<SQL
select pm.post_id from $wpdb->postmeta pm where pm.meta_key = '_group_id' and pm.meta_value = %s
SQL;
        $res = $wpdb->get_var($wpdb->prepare($sql, $groupId));
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
     * @return int[]
     */
    protected function fetchUpSellsIds(): array
    {
        $upSells = $this->currentCSVRow->getUpsells();
        if(!\is_array($upSells)){
            return [];
        }
        $fetchUpSellsIdsCallable = apply_filters('wawoo_product_importer/fetch_upsells_ids_callable',null);
        if(\is_callable($fetchUpSellsIdsCallable)){
            return $fetchUpSellsIdsCallable($upSells);
        }
        $upSellsIds = [];
        foreach ($upSells as $upSellIdentifier){
            $pId = $this->getProductIdByProductIdentifier($upSellIdentifier);
            if(!$pId){
                continue;
            }
            $upSellsIds[] = $pId;
        }
        return $upSellsIds;
    }

    /**
     * @return int[]
     */
    protected function fetchCrossSellsIds(): array
    {
        $crossSells = $this->currentCSVRow->getCrossSells();
        if(!\is_array($crossSells)){
            return [];
        }
        $fetchCrossSellsIdsCallable = apply_filters('wawoo_product_importer/fetch_crossells_ids_callable',null);
        if(\is_callable($fetchCrossSellsIdsCallable)){
            return $fetchCrossSellsIdsCallable($crossSells);
        }
        $crossSellsIds = [];
        foreach ($crossSells as $crossSellIdentifier){
            $pId = $this->getProductIdByProductIdentifier($crossSellIdentifier);
            if(!$pId){
                continue;
            }
            $crossSellsIds[] = $pId;
        }
        return $crossSellsIds;
    }

    /**
     * @param int|null $postId
     * @return string
     */
    public function getBrandTaxonomyName(int $postId = null): string
    {
        return apply_filters('wawoo_products_importer/brand_taxonomy_name',$this->brandTaxonomyName,$postId,$this);
    }

    /**
     * @param int|null $postId
     * @return string
     */
    public function getColorTaxonomyName(int $postId = null): string
    {
        return apply_filters('wawoo_products_importer/color_taxonomy_name',$this->colorTaxonomyName,$postId,$this);
    }

    /**
     * @param int|null $postId
     * @return string
     */
    public function getSizeTaxonomyName(int $postId = null): string
    {
        return apply_filters('wawoo_products_importer/size_taxonomy_name',$this->sizeTaxonomyName,$postId,$this);
    }

    /**
     * @param int $productId
     * @return void
     */
    protected function addParsedProductId(int $productId): void
    {
        if(!\is_array($this->parsedProductIds)){
            $this->parsedProductIds = [];
        }
        if(!\in_array($productId,$this->parsedProductIds,true)){
            $this->parsedProductIds[] = $productId;
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getDefaultSourceFilePath(): string
    {
        return apply_filters('wawoo_products_importer/default_source_file_path','products-import-'.(new \DateTime('now',new \DateTimeZone('Europe/Rome')))->format('ymd').'.csv');
    }

    /**
     * @return string
     */
    protected function getDefaultImportDirPath(): string
    {
        return apply_filters('wawoo_products_importer/default_import_dir_path',WP_CONTENT_DIR.'/imports/products');
    }
}
