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
            'description' => 'Specifica il nome della colonna che contiene l\'identificativo del prodotto (per esempio lo SKU)',
            'default' => 'meta:_sku',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'brand-taxonomy',
            'description' => 'Specifica a quale meta utilizzare per identificare il prodotto (solitamente il meta corrispondende al valore della prima colonna)',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'color-taxonomy',
            'description' => 'Specifica a quale meta utilizzare per identificare il prodotto (solitamente il meta corrispondende al valore della prima colonna)',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'size-taxonomy',
            'description' => 'Specifica a quale meta utilizzare per identificare il prodotto (solitamente il meta corrispondende al valore della prima colonna)',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'flag',
            'name' => 'update-prices',
            'description' => 'Specifies whether parse all files inside the base path',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'flag',
            'name' => 'update-stocks',
            'description' => 'Specifies whether parse all files inside the base path',
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
        $this->log('Identificatore prodotto: '.$this->productIdentifierColumnName);
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
        if(!isset($this->mustUpdatePrices)){
            $this->mustUpdatePrices = isset($assoc_args['update-stocks']);
        }
        if($this->mustUpdatePrices === true){
            $this->log('Aggiornamento prezzi attivato');
        }
        if(!isset($this->mustUpdateStocks)){
            $this->mustUpdateStocks = isset($assoc_args['update-stocks']);
        }
        if($this->mustUpdateStocks === true){
            $this->log('Aggiornamento stock attivato');
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
            $this->log('--- Verrà creato o aggiornato un prodotto SEMPLICE');
            $this->createOrUpdateSimpleProduct();
        }else{
            $this->log('--- Verrà creata o aggiornata una VARIAZIONE');
            $this->createOrUpdateVariation();
        }
    }

    /**
     * @return void
     */
    protected function onDoneParsing(): void
    {
        if(!empty($this->variablesProductsToSync)){
            $this->log('Syncing dei prodotti variabili...');
            foreach ($this->variablesProductsToSync as $variableProductId => $variationsIds) {
                $this->log('- Sync prodotto #'.$variableProductId);
                $variationsIds = array_unique($variationsIds);
                foreach ($variationsIds as $variationsId){
                    $this->log('-- Fix prezzi variazione #'.$variationsId);
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
                    if(!$this->isDryRun()){
                        update_post_meta($productId,'_upsell_ids',$idToAssigns);
                    }
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
    private function parseManifestFile(string $manifestFilePath): void
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
            $this->mustUpdateStocks = $this->mustUpdateStocks;
        }
        $productIdentifier = $manifest->getSetting('product_identifier_column_name');
        if(\is_string($productIdentifier)){
            $this->productIdentifierColumnName = $productIdentifier;
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
        do_action('wawoo_product_importer/finalize_products',$this->parsedProductIds, $this->isDryRun());
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
            if($CSVRow->hasStock()){
                $product->set_stock_quantity($CSVRow->getStock());
                $product->set_stock_status($CSVRow->getStockStatus());
            }
            if($CSVRow->hasRegularPrice()){
                $product->set_regular_price($CSVRow->getRegularPrice());
            }
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
        if(!$this->isDryRun()){
            $id = $product->save();
            if ($id === 0) {
                throw new ImportProductsException('ERRORE: impossibile creare o aggiornare prodotto con IDENTIFICATORE %s', $CSVRow->getIdentifier());
            }
        }
        if($CSVRow->hasBrand()){
            $this->log('--- Assegnazione brand: '.$CSVRow->getBrand());
            if($CSVRow->isBrandHierarchical()){
                $this->addHierarchicalTermsToObjectFromTermListString($product->get_id(), $CSVRow->getBrand(), $this->getBrandTaxonomyName());
            }else{
                $this->addTermsToObjectFromTermListString($product->get_id(), $CSVRow->getBrand(), $this->getBrandTaxonomyName());
            }
        }
        if($CSVRow->hasCategory()){
            $this->log('--- Assegnazione categorie: '.$CSVRow->getCategory());
            if($CSVRow->isProductCategoryHierarchical()){
                $this->addHierarchicalTermsToObjectFromTermListString($product->get_id(), $CSVRow->getCategory(), 'product_cat');
            }else{
                $this->addTermsToObjectFromTermListString($product->get_id(), $CSVRow->getCategory(), 'product_cat');
            }
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
        $attributesForVariations = $CSVRow->getAttributesForVariableAndSimpleProducts();
        foreach ($attributesForVariations as $attributeTaxonomyName => $attributeData){
            $this->log(sprintf('--- Assegnazione termini per %s: %s',$attributeTaxonomyName,$attributeData['value']));
            $this->addTermsToObjectFromTermListString($product->get_id(),$CSVRow->getAttribute($attributeTaxonomyName),$attributeTaxonomyName);
        }
        $this->log('--- Assegnazione custom fields...');
        foreach ($CSVRow->getCustomMetaFields() as $customFieldData) {
            $customFieldKey = $customFieldData['key'];
            $customFieldValue = $customFieldData['value'];
            if($customFieldValue !== null && $customFieldValue !== ''){
                $this->log(sprintf('---- %s: %s',$customFieldKey,$customFieldValue));
                $product->update_meta_data($customFieldKey,$customFieldValue);
            }
        }
        if(!$this->isDryRun()){
            $product->save();
        }
        $this->log('-- Fix dei meta dei prezzi');
        if(!$this->isDryRun()){
            adjustPriceMeta($product->get_id());
            $this->setImportedProductMeta($product->get_id());
        }
        if($isNew){
            $this->log(sprintf('--- Prodotto creato #%d', $product->get_id()));
        }else{
            $this->log(sprintf('--- Prodotto aggiornato #%d', $product->get_id()));
        }
        $this->log('--- Parsing up sells...');
        $upSells = $this->fetchUpSellsIds();
        if(!empty($upSells)){
            $this->upSellsToAssign[$product->get_id()] = $upSells;
        }
        $this->log('--- Parsing cross sells...');
        $crossSells = $this->fetchCrossSellsIds();
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
                $id = $parentProduct->save();
                if($id === 0){
                    throw new ImportProductsException(sprintf('ERRORE: impossibile creare prodotto con SKU %s', $CSVRow->getParentSku()));
                }
            }
            if($CSVRow->hasBrand()){
                $this->log('--- Assegnazione brand: '.$CSVRow->getBrand());
                if($CSVRow->isBrandHierarchical()){
                    $this->addHierarchicalTermsToObjectFromTermListString($parentProduct->get_id(), $CSVRow->getBrand(), $this->getBrandTaxonomyName());
                }else{
                    $this->addTermsToObjectFromTermListString($parentProduct->get_id(), $CSVRow->getBrand(), $this->getBrandTaxonomyName());
                }
            }
            if($CSVRow->hasCategory()){
                $this->log('--- Assegnazione categorie: '.$CSVRow->getCategory());
                if($CSVRow->isProductCategoryHierarchical()){
                    $this->addHierarchicalTermsToObjectFromTermListString($parentProduct->get_id(), $CSVRow->getCategory(), 'product_cat');
                }else{
                    $this->addTermsToObjectFromTermListString($parentProduct->get_id(), $CSVRow->getCategory(), 'product_cat');
                }
            }
            // this save is not really necessary because the parent product
            // will be saved again after the creation of its variations
            // $parentProduct->save();
            $this->log(sprintf('--- Creato prodotto variabile con ID #%s', $parentProduct->get_id()));
            $this->log('--- Parsing up sells...');
            $upSells = $this->fetchUpSellsIds();
            if(!empty($upSells)){
                $this->upSellsToAssign[$parentProduct->get_id()] = $upSells;
            }
            $this->log('--- Parsing cross sells...');
            $crossSells = $this->fetchCrossSellsIds();
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
        if(!$this->isDryRun()){
            $this->setImportedProductMeta($parentProduct->get_id());
        }
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
        $attributesForVariableProduct = $CSVRow->getAttributesForVariableAndSimpleProducts();
        foreach ($attributesForVariableProduct as $attributeTaxonomyName => $attributeData){
            if(!$CSVRow->hasAttribute($attributeTaxonomyName)){
                continue;
            }
            try{
                $this->log(sprintf('--- Parsing termini per %s: %s',$attributeTaxonomyName,$CSVRow->getAttribute($attributeTaxonomyName)));
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
            if($CSVRow->hasStock()){
                $product->set_stock_quantity($CSVRow->getStock());
                $product->set_stock_status($CSVRow->getStockStatus());
            }
            if($CSVRow->hasRegularPrice()){
                $product->set_regular_price($CSVRow->getRegularPrice());
            }
        }

        $product->set_name($CSVRow->getTitle());
        if(!empty($attributes)){
            $product->set_attributes($attributes);
        }
        $product->set_manage_stock(true);
        $product->set_status('publish');
        $this->log('--- Assegnazione custom fields...');
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

        if(!$this->isDryRun()){
            $id = $product->save();
            if ($id === 0) {
                throw new ImportProductsException(sprintf('ERRORE: impossibile creare variazione con IDENTIFICATORE %s', $CSVRow->getIdentifier()));
            }
            $this->setImportedProductMeta($id);
        }

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

        if(!$this->isDryRun()){
            $parentProduct->save();
            if(!empty($attributesTermsIdToAdd)){
                foreach ($attributesTermsIdToAdd as $taxonomy => $terms){
                    wp_set_object_terms($parentProduct->get_id(), $terms, $taxonomy, false);
                }
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
                    throw new ImportProductsException(sprintf('Impossibile creare il termine %s nella tassonomia %s: %s',$tm,$taxonomy,$creationResult->get_error_message()));
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
                    throw new ImportProductsException(sprintf('Impossibile creare il termine %s nella tassonomia %s: %s',$tm,$taxonomy,$creationResult->get_error_message()));
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
        if($this->currentCSVRow->isIdentifierTheProductSKU()){
            $productId = wc_get_product_id_by_sku($identifier);
            if(!\is_int($productId) || $productId === 0){
                return null;
            }
            return $productId;
        }
        return Posts::getPostIdByMeta($this->currentCSVRow->getStandardizedColumnNameFromActualColumnName($this->productIdentifierColumnName),$identifier);
    }

    /**
     * @param string $groupId
     * @return \WC_Product|null
     */
    private function getProductByGroupId(string $groupId): ?\WC_Product
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
