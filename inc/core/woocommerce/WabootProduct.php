<?php

namespace Waboot\inc\core\woocommerce;

use Waboot\inc\core\utils\Terms;

class WabootProduct
{
    public const BRAND_TAXONOMY_NAME = 'brand';
    /**
     * @var int
     */
    private $id;
    /**
     * @var \WC_Product_Variable
     */
    private $wcProduct;
    /**
     * @var string
     */
    private $sku;
    /**
     * @var \WP_Term
     */
    private $brand;
    /**
     * @var \WP_Term
     */
    private $size;
    /**
     * @var boolean
     */
    private $isVariable;
    /**
     * @var WabootProductVariation[]
     */
    private $variations;

    /**
     * FCVariableProduct constructor.
     * @param int|\WC_Product $product
     * @throws \RuntimeException
     */
    public function __construct($product)
    {
        if(\is_int($product)){
            $this->id = $product;
            $pType = get_post_type($product);
            if($pType !== 'product'){
                throw new \RuntimeException('#'.$this->id.' is not Product');
            }
        }elseif($product instanceof \WC_Product){
            $this->id = $product->get_id();
            $this->wcProduct = $product;
        }else{
            throw new \RuntimeException('Provided $product is not Product');
        }

        $this->sku = isset($this->wcProduct) ? $this->wcProduct->get_sku() : get_post_meta($this->id, '_sku', true);
    }

    /**
     * @return \WC_Product
     * @throws \RuntimeException
     */
    public function getWcProduct(): \WC_Product
    {
        if(!isset($this->wcProduct)){
            $this->wcProduct = wc_get_product($this->id);
        }
        return $this->wcProduct;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @return string
     */
    public function getPermalink(): string
    {
        return get_the_permalink($this->getId());
    }

    /**
     * @return bool
     */
    public function isVariableProduct(): bool{
        return $this->getWcProduct() instanceof \WC_Product_Variable;
    }

    /**
     * @return array
     */
    public function fetchVariations(): array
    {
        try{
            $result = [];
            $product = $this->getWcProduct();
            if(!$product instanceof \WC_Product_Variable){
                return [];
            }
            $variations = $product->get_available_variations('objects');
            if(!\is_array($variations) || count($variations) <= 0){
                return [];
            }
            foreach ($variations as $variation){
                if(!$variation instanceof \WC_Product_Variation){
                    continue;
                }
                $result[] = $this->createVariationInstance($variation);
            }
            return $result;
        }catch (\RuntimeException $e){
            return [];
        }
    }

    /**
     * @return array|WabootProductVariation[]
     */
    public function getVariations(): array
    {
        if(!isset($this->variations)){
            $this->variations = $this->fetchVariations();
        }
        return $this->variations;
    }

    /**
     * @return bool
     */
    public function hasVariations(): bool
    {
        $v = $this->getVariations();
        return \is_array($v) && count($v) > 0;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return array|\WP_Term[]
     */
    private function fetchBrands(): array
    {
        if(isset($this->brands)){
            return $this->brands;
        }
        $terms = wp_get_post_terms($this->id, self::BRAND_TAXONOMY_NAME);
        if(!\is_array($terms) || count($terms) <= 0){
            return [];
        }
        $this->brands = $terms;
        return $this->brands;
    }

    /**
     * @return \WP_Term|false
     */
    public function getBrand()
    {
        if($this->brand instanceof \WP_Term){
            return $this->brand;
        }
        $terms = $this->fetchBrands();
        if(count($terms) <= 0){
            return false;
        }
        $brand = array_shift($terms);
        if(!$brand instanceof \WP_Term){
            return false;
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
    public function getCategories(bool $reverse, $asString = false, $separator = ' > '){
        //$categoryTerms = \wc_get_product_category_list($productId, $separator);
        //$categoryTerms = \strip_tags($categoryTerms);
        //$categoryTerms = \get_the_terms($productId, 'product_cat');
        $categoryTerms = Terms::getPostTermsHierarchical($this->getId(),'product_cat',[],true,true);
        if(\is_wp_error($categoryTerms)){
            return $asString ? '' : [];
        }
        if(empty($categoryTerms)){
            return $asString ? '': [];
        }
        if($reverse){
            $categoryTerms = \array_reverse($categoryTerms);
        }
        $categoryTermsNames = array_unique(wp_list_pluck($categoryTerms,'name'));
        /*if($categoryTermsNames[count($categoryTermsNames)-1] === 'Uomo'){
            $categoryTermsNames = array_reverse($categoryTermsNames);
        }*/
        if($asString){
            $categoryTermsNames = \implode($separator, $categoryTermsNames);
        }
        return $categoryTermsNames;
    }

    /**
     * @param \WC_Product_Variation $variation
     * @return WabootProductVariation
     */
    protected function createVariationInstance(\WC_Product_Variation $variation): WabootProductVariation
    {
        return new WabootProductVariation($variation->get_id(),$this);
    }
}