<?php

namespace Waboot\inc\core\woocommerce;

use Waboot\inc\core\utils\Terms;
use function Waboot\inc\getProductType;

class Product
{
    public const BRAND_TAXONOMY_NAME = 'product_brand';
    /**
     * @var int
     */
    protected $id;
    /**
     * @var \WC_Product
     */
    protected $wcProduct;
    /**
     * @var string
     */
    protected $sku;
    /**
     * @var \WP_Term
     */
    protected $brand;
    /**
     * @var string
     */
    protected $productType;
    /**
     * @var array
     */
    protected $terms;
    /**
     * @var array
     */
    protected $orderedTerms;
    /**
     * @var float[]
     */
    protected $prices;
    /**
     * @var array
     */
    protected $wcAttributes;

    /**
     * Product constructor.
     * @param null|int|\WC_Product $product
     * @throws ProductException
     */
    public function __construct($product = null, string $type = null)
    {
        if(!isset($product)){
            $this->productType = $type ?? ProductFactory::PRODUCT_TYPE_SIMPLE;
            return;
        }
        if(\is_int($product)){
            $this->id = $product;
            $pType = getProductType($product);
            if(!$pType){
                throw new ProductException('#'.$this->id.' is not valid product');
            }
            $this->productType = $pType;
        }elseif($product instanceof \WC_Product){
            $this->id = $product->get_id();
            $this->wcProduct = $product;
            $this->productType = getProductType($product->get_id());
        }else{
            throw new ProductException('Provided $product is not valid product');
        }

        $this->sku = isset($this->wcProduct) ? $this->wcProduct->get_sku() : get_post_meta($this->id, '_sku', true);
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return !isset($this->id);
    }

    /**
     * @return \WC_Product
     * @throws \RuntimeException|ProductException
     */
    public function getWcProduct()
    {
        if(!isset($this->wcProduct)){
            if($this->isNew()){
                $wcProduct = wc_get_product_object($this->getProductType());
            }else{
                $wcProduct = wc_get_product($this->id);
                if(!$wcProduct){
                    throw new ProductException('Product - Unable to fetch WC_Product instance');
                }
            }
            $this->wcProduct = $wcProduct;
        }
        return $this->wcProduct;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getProductType(): string
    {
        return $this->productType;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        try{
            if($this->isNew()){
                $sku = $this->getWcProduct()->get_sku();
                if(!\is_string($sku)){
                    return '';
                }
                return $sku;
            }
            return $this->sku;
        }catch (ProductException $e){
            return '';
        }
    }

    /**
     * @return string
     */
    public function getPermalink(): string
    {
        if($this->isNew()){
            return '';
        }
        return get_the_permalink($this->getId());
    }

    /**
     * @return float|null
     */
    public function getCost(): ?float
    {
        try {
            $cost = $this->getWcProduct()->get_meta('_cost');
        } catch (ProductException $e) {
            $cost = '';
        }
        $cost = str_replace(',', '.', $cost);
        if (!is_numeric($cost)) {
            return null;
        }
        return (float) $cost;
    }

    /**
     * @param bool $refetch
     * @return void
     * @throws ProductException
     */
    public function fetchPrices(bool $refetch = false): void
    {
        $wcProduct = $this->getWcProduct();
        if($refetch || !isset($this->prices['regular_price'])){
            $this->prices['regular_price'] = (float) $wcProduct->get_regular_price();
        }
        if($refetch || !isset($this->prices['regular_price_displayed'])){
            $this->prices['regular_price_displayed'] = (float) wc_get_price_to_display($this->wcProduct,[
                'price' => $wcProduct->get_regular_price()
            ]);
        }
        if($refetch || !isset($this->prices['sale_price'])){
            $this->prices['sale_price'] = (float) $wcProduct->get_regular_price();
        }
        if($refetch || !isset($this->prices['sale_price_displayed'])){
            $this->prices['sale_price_displayed'] = (float) wc_get_price_to_display($this->wcProduct,[
                'price' => $wcProduct->get_sale_price()
            ]);
        }
        if($refetch || !isset($this->prices['price'])){
            $this->prices['price'] = (float) $wcProduct->get_price();
        }
        if($refetch || !isset($this->prices['price_displayed'])){
            $this->prices['price_displayed'] = (float) wc_get_price_to_display($this->wcProduct);
        }
    }

    /**
     * @param bool $raw whether return the meta value or the price to display (which takes into account the taxes)
     * @return float
     */
    public function getRegularPrice(bool $raw = false)
    {
        try{
            $this->fetchPrices();
            if($raw){
                return $this->prices['regular_price'];
            }
            return $this->prices['regular_price_displayed'];
        }catch (ProductException $e) {
            return 0;
        }
    }

    /**
     * @param bool $raw
     * @return float
     */
    public function getSalePrice(bool $raw = false)
    {
        try{
            $this->fetchPrices();
            if($raw){
                return $this->prices['sale_price'];
            }
            return $this->prices['sale_price_displayed'];
        }catch (ProductException $e) {
            return 0;
        }
    }

    /**
     * @param bool $raw
     * @return float
     */
    public function getPrice(bool $raw = false)
    {
        try{
            $this->fetchPrices();
            if($raw){
                return $this->prices['price'];
            }
            return $this->prices['price_displayed'];
        }catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * @param string $taxonomyName
     * @param bool $hierarchical
     * @return void
     * @throws ProductException
     */
    private function fetchTerms(string $taxonomyName, bool $hierarchical = false): void
    {
        if(!taxonomy_exists($taxonomyName)){
            throw new ProductException('Product - invalid taxonomy provided to fetchTerms()');
        }
        if($hierarchical && isset($this->terms[$taxonomyName])){
            return;
        }
        if(!$hierarchical && isset($this->orderedTerms[$taxonomyName])){
            return;
        }
        if($this->isNew()){
            if($hierarchical){
                $this->orderedTerms[$taxonomyName] = [];
            }else{
                $this->terms[$taxonomyName] = [];
            }
            return;
        }
        $terms = $hierarchical ? Terms::getPostTermsHierarchical($this->id,$taxonomyName,[],true,true) : wp_get_post_terms($this->id, $taxonomyName);
        if(!\is_array($terms) || count($terms) <= 0){
            if($hierarchical){
                $this->orderedTerms[$taxonomyName] = [];
            }else{
                $this->terms[$taxonomyName] = [];
            }
        }else{
            if($hierarchical){
                $this->orderedTerms[$taxonomyName] = $terms;
            }else{
                $this->terms[$taxonomyName] = $terms;
            }
        }
    }

    /**
     * @param string $taxonomyName
     * @param bool $hierarchical
     * @param bool $reverse
     * @return array
     */
    public function getTerms(string $taxonomyName, bool $hierarchical = false, bool $reverse = false): array
    {
        try{
            $this->fetchTerms($taxonomyName,$hierarchical);
            if($reverse){
                return $hierarchical ? array_reverse($this->orderedTerms[$taxonomyName]) : array_reverse($this->terms[$taxonomyName]);
            }
            return $hierarchical ? $this->orderedTerms[$taxonomyName] : $this->terms[$taxonomyName];
        }catch (ProductException $e){
            return [];
        }
    }

    /**
     * @param string $taxonomyName
     * @param string $separator
     * @param bool $reverse
     * @return void
     */
    public function getTermsList(string $taxonomyName, string $separator, bool $reverse): string
    {
        $terms = $this->getTerms($taxonomyName,true,$reverse);
        if(!\is_array($terms) || empty($terms)){
            return '';
        }
        $termNames = array_unique(wp_list_pluck($terms,'name'));
        return \implode($separator, $termNames);
    }

    /**
     * @param string $taxonomyName
     * @return \WP_Term|null
     */
    public function getFirstTerm(string $taxonomyName): ?\WP_Term
    {
        $terms = $this->getTerms($taxonomyName, true);
        if(!\is_array($terms) || count($terms) <= 0){
            return null;
        }
        $terms = array_reverse($terms);
        $term = array_shift($terms);
        if(!$term instanceof \WP_Term){
            return null;
        }
        return $term;
    }

    /**
     * @return \WP_Term|null
     */
    public function getBrand(): ?\WP_Term
    {
        if($this->brand instanceof \WP_Term){
            return $this->brand;
        }
        $brand = $this->getFirstTerm(self::BRAND_TAXONOMY_NAME);
        if(!$brand){
            return null;
        }
        $this->brand = $brand;
        return $this->brand;
    }

    /**
     * @param bool $reverse
     * @param bool $asString
     * @param string $separator
     * @return array|string
     */
    public function getCategories(bool $reverse, bool $asString = false, string $separator = ' > ')
    {
        if($asString){
            return $this->getTermsList('product_cat',$separator,$reverse);
        }
        return $this->getTerms('product_cat',true,$reverse);
    }

    /**
     * @return array
     */
    public function getWcAttributes(): array
    {
        if(!isset($this->wcAttributes)){
            $this->fetchWCAttributes();
        }
        return $this->wcAttributes;
    }

    /**
     * Fetch the attributes
     */
    public function fetchWCAttributes(): void
    {
        try{
            $wcAttributes = $this->getWcProduct()->get_attributes('edit');
            if(!\is_array($wcAttributes)){
                $this->wcAttributes = [];
            }else{
                $this->wcAttributes = $wcAttributes;
            }
        }catch (\Exception $e){
            $this->wcAttributes = [];
        }
    }

    /**
     * @return bool
     */
    public function hasAttributes(): bool
    {
        if(!isset($this->wcAttributes)){
            $this->fetchWCAttributes();
        }
        return count($this->wcAttributes) !== 0;
    }

    /**
     * @param \WP_Term $term
     * @param bool $usedForVariations
     * @throws ProductException
     */
    public function addTermAttribute(\WP_Term $term): void
    {
        $wcProduct = $this->getWcProduct();
        $currentAttributes = $this->getWcAttributes();
        $currentAttributesCount = count($currentAttributes);
        $taxonomy = $term->taxonomy;
        if(isset($currentAttributes[$taxonomy])){
            $wcProduct->set_attributes([]); //This will trigger the notify of changes in the \WC_Product_Variation->set_prop() method
            $attObj = $currentAttributes[$taxonomy];
            unset($currentAttributes[$taxonomy]); //We will re-add this later
            if(!$attObj instanceof \WC_Product_Attribute){
                throw new ProductException('Product->addTermAttribute(): invalid existing attribute object found');
            }
            $currentOptions = $attObj->get_options();
            $newOptions = array_unique(array_merge($currentOptions,[$term->term_id]));
            $attObj->set_options($newOptions);
        }else{
            $attObj = new \WC_Product_Attribute();
            $attObj->set_id(wc_attribute_taxonomy_id_by_name($taxonomy));
            $attObj->set_name($taxonomy);
            $attObj->set_visible(true);
            $attObj->set_options([$term->term_id]);
            $attObj->set_variation(false);
            $nextPosition = $currentAttributesCount;
            $attObj->set_position($nextPosition);
        }
        if($currentAttributesCount === 0){
            $wcProduct->set_attributes([$attObj]);
        }else{
            $attributes = array_values($currentAttributes);
            $attributes[] = $attObj;
            $wcProduct->set_attributes($attributes);
        }
        $this->fetchWCAttributes();
    }

    /**
     * @return void
     * @throws ProductException
     */
    public function save(): int
    {
        return $this->getWcProduct()->save();
    }
}