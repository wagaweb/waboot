<?php

namespace Waboot\inc\core\woocommerce;

use function Waboot\inc\getAllProductVariationIds;
use function Waboot\inc\getProductType;
use function Waboot\inc\syncVariableProductData;

class VariableProduct extends Product
{
    /**
     * @var ProductVariation[]
     */
    private $variations;

    /**
     * @param int|\WC_Product $product
     * @throws ProductException
     */
    public function __construct($product = null)
    {
        if(!$product){
            parent::__construct($product,ProductFactory::PRODUCT_TYPE_VARIABLE);
            return;
        }
        $pType = \is_int($product) ? getProductType($product) : getProductType($product->get_id());
        if($pType !== ProductFactory::PRODUCT_TYPE_VARIABLE){
            throw new ProductException('VariableProduct - provided $variation is not a variable product');
        }
        parent::__construct($product);
    }

    /**
     * @return \WC_Product_Variable
     * @throws \RuntimeException|ProductException
     */
    public function getWcProduct(): \WC_Product_Variable
    {
        if(!isset($this->wcProduct)){
            if($this->isNew()){
                $wcProduct = wc_get_product_object(ProductFactory::PRODUCT_TYPE_VARIABLE);
            }else{
                $wcProduct = wc_get_product($this->getId());
                if(!$wcProduct instanceof \WC_Product_Variable){
                    throw new ProductException('VariableProduct - #'.$this->getId().' is not a variable product');
                }
            }
            $this->wcProduct = $wcProduct;
        }
        return $this->wcProduct;
    }

    /**
     * @return array
     */
    public function fetchVariations(): array
    {
        try{
            $result = [];
            if($this->isNew()){
                return $result;
            }
            $variationIds = getAllProductVariationIds($this->getId());
            if(!\is_array($variationIds) || count($variationIds) <= 0){
                return [];
            }
            foreach ($variationIds as $variationId){
                try{
                    $result[] = ProductFactory::createProductVariation($variationId,$this);
                }catch (ProductFactoryException $e){}
            }
            return $result;
        }catch (\Throwable $e){
            return [];
        }
    }

    /**
     * @return array|ProductVariation[]
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
     * @return array
     */
    public function getVariationWcAttributes(): array
    {
        $wcAttributes = $this->getWcAttributes();
        if(!\is_array($this->wcAttributes) || empty($wcAttributes)){
            return [];
        }
        return array_filter($wcAttributes, static function(\WC_Product_Attribute $att){
            return $att->get_variation() === true;
        });
    }

    /**
     * @param \WP_Term $term
     * @param bool $usedForVariations
     * @throws ProductException
     */
    public function addTermAttribute(\WP_Term $term, bool $usedForVariations = false): void
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
            if($usedForVariations){
                $attObj->set_variation(true);
            }else{
                $attObj->set_variation(false);
            }
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
     * @param bool $performAdditionalFixes
     * @return int
     * @throws ProductException
     */
    public function save(bool $performAdditionalFixes = true): int
    {
        $id = parent::save($performAdditionalFixes);
        if($performAdditionalFixes){
            syncVariableProductData($id);
        }
        return $id;
    }
}