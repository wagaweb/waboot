<?php

namespace Waboot\addons\packages\catalog_custom_tables\inc;

class DBProduct extends AbstractDBProduct
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var int
     */
    private $parentId;
    /**
     * @var int
     */
    private $mainCategoryId;
    /**
     * @var int[]
     */
    private $categoriesIds;
    /**
     * @var float
     */
    private $price;
    /**
     * @var int
     */
    private $stock;
    /**
     * @var int
     */
    private $wcProductId;
    /**
     * @var \WC_Product
     */
    private $wcProduct;
    /**
     * @var \WP_Term[]
     */
    private $wcCategories;

    /**
     * WCProduct constructor.
     * @param \WC_Product|int $wcProduct
     * @param CatalogDB|null $dbManager
     * @param CapsuleWP|null $dbConnector
     * @throws DBProductException
     */
    public function __construct($wcProduct, CatalogDB $dbManager = null, CapsuleWP $dbConnector = null)
    {
        if(\is_int($wcProduct)){
            $this->wcProductId = $wcProduct;
            $this->wcProduct = wc_get_product($this->wcProductId);
            if(!$this->wcProduct instanceof \WC_Product){
                throw new DBProductException('Invalid product');
            }
        }elseif ($wcProduct instanceof \WC_Product){
            $this->wcProduct = $wcProduct;
            $this->wcProductId = $wcProduct->get_id();
        }else{
            throw new DBProductException('Invalid product');
        }

        if(!isset($dbConnector)){
            $this->dbConnector = new CapsuleWP();
        }else{
            $this->dbConnector = $dbConnector;
        }

        if(!isset($dbManager)){
            $this->dbManager = new CatalogDB($this->dbConnector);
        }else{
            $this->dbManager = $dbManager;
        }

        $this->fetchExistingRecordData();
    }

    /**
     * @throws DBProductException
     */
    private function fetchExistingRecordData(): void
    {
        $existingId = $this->dbManager->searchProductIdBySku($this->getWcProduct()->get_sku());
        if($existingId === false){
            return;
        }
        $this->id = $existingId;
        $existingRecord = $this->dbManager->getProductRecordById($existingId);
        if(!$existingRecord instanceof \stdClass){
            return;
        }
        $this->parentSku = property_exists($existingRecord,'parent_sku') ? $existingRecord->parent_sku : null;
        if(isset($this->parentSku)){
            $parentId = $this->dbManager->searchProductIdBySku($this->parentSku);
            if($parentId !== false){
                $this->parentId = $parentId;
            }
        }
        //Taxonomies
        $this->mainCategoryId = property_exists($existingRecord,'main_category_id') ? $existingRecord->main_category_id : null;
        //Custom table columns:
        $this->sku = property_exists($existingRecord,'sku') ? $existingRecord->sku : null;
        $this->slug = property_exists($existingRecord,'slug') ? $existingRecord->slug : null;
        $this->title = property_exists($existingRecord,'title') ? $existingRecord->title : null;
        $this->price = property_exists($existingRecord,'price') ? (float) $existingRecord->price : null;
        $this->stock = property_exists($existingRecord,'stock') ? (int) $existingRecord->stock : null;
    }

    /**
     * @return \WC_Product
     * @throws DBProductException
     */
    public function getWcProduct(): \WC_Product
    {
        if(!isset($this->wcProduct)){
            $wcProduct = wc_get_product($this->wcProductId);
            if(!$wcProduct instanceof \WC_Product){
                throw new DBProductException('Invalid product');
            }
            $this->wcProduct = $wcProduct;
        }
        return $this->wcProduct;
    }

    /**
     * @return \WP_Term[]
     */
    private function getWcCategories(): array
    {
        if(isset($this->wcCategories)){
            return $this->wcCategories;
        }
        $categories = \wp_get_post_terms($this->wcProductId, 'product_cat', ['orderby' => 'parent']);
        if(!\is_array($categories)){
            $categories = [];
        }
        $categories = array_filter($categories,static function ($el){ return $el instanceof \WP_Term; });
        $this->wcCategories = \is_array($categories) ? $categories : [];
        return $this->wcCategories;
    }

    /**
     * @throws DBProductException
     */
    public function save()
    {
        //Parent Sku may be changed, so overwrite
        if($this->isVariation()){
            $parent = wc_get_product($this->getWcProduct()->get_parent_id());
            if($parent instanceof \WC_Product_Variable){
                $this->parentSku = $parent->get_sku();
            }
        }

        //Row data
        $data = [
            'wc_id' => $this->getWcProduct()->get_id(),
            'sku' => $this->getSku(),
            'parent_id' => $this->getParentId(),
            'parent_sku' => $this->getParentSku(),
            'title' => $this->getTitle(),
            'price' => $this->getPrice(),
            'stock' => $this->getStock()
        ];
        if($this->isNew()){
            $newId = $this->dbConnector->getManager()::table(WB_CUSTOM_PRODUCTS_TABLE)->insertGetId($data);
        }else{
            $this->dbConnector->getManager()::table(WB_CUSTOM_PRODUCTS_TABLE)
                ->where('id',$this->id)
                ->update($data);
            $newId = $this->id;
        }

        //Insert data in related tables (here we assume that related tables are empty)
        $wcCategories = $this->getWcCategories();
        foreach ($wcCategories as $wpTerm){
            $data = [
                'wc_id' => $wpTerm->term_id,
                'name' => $wpTerm->name,
                'slug' => $wpTerm->slug,
            ];
            $newId = $this->dbConnector->getManager()::table(WB_CUSTOM_CATEGORIES_TABLE)->insertGetId($data);
            $this->dbConnector->getManager()::table(WB_CUSTOM_PRODUCTS_CATEGORIES_TABLE)->insert([
                'product_id' => $this->getId(),
                'category_id' => $newId
            ]);
        }

        return (int) $newId;
    }

    /**
     * @return bool
     */
    public function isParent(): bool
    {
        try{
            return $this->getWcProduct() instanceof \WC_Product_Variable;
        }catch (DBProductException $e){
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isVariation(): bool
    {
        try{
            return $this->getWcProduct() instanceof \WC_Product_Variation;
        }catch (DBProductException $e){
            return false;
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @return int
     */
    public function getMainCategoryId(): int
    {
        return $this->mainCategoryId;
    }

    /**
     * @return int[]
     */
    public function getCategoriesIds(): array
    {
        return $this->categoriesIds;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getStock(): int
    {
        return $this->stock;
    }

    /**
     * @return int
     */
    public function getWcProductId(): int
    {
        return $this->wcProductId;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return !isset($this->id);
    }
}