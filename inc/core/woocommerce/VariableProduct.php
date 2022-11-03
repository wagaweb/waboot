<?php

namespace Waboot\inc\core\woocommerce;

use function Waboot\inc\getAllProductVariationIds;
use function Waboot\inc\getProductType;

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
}