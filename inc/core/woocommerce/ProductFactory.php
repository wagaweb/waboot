<?php

namespace Waboot\inc\core\woocommerce;

use function Waboot\inc\getProductType;

class ProductFactory
{
    public const PRODUCT_TYPE_SIMPLE = 'simple';
    public const PRODUCT_TYPE_VARIABLE = 'variable';
    public const PRODUCT_TYPE_VARIATION = 'variation';

    /**
     * @param int|\WC_Product $product
     * @param \WC_Product_Variable|VariableProduct|null $parent
     * @param string|null $type
     * @return Product|ProductVariation|VariableProduct
     * @throws ProductFactoryException
     */
    public static function create($product, $parent = null, string $type = null)
    {
        try{
            if(!isset($product) || $product === 0){
                throw new ProductFactoryException('ProductFactory - Invalid product provided');
            }
            $pId = \is_int($product) ? $product : $product->get_id();
            $pType = $type ?? getProductType($pId);
            switch ($pType){
                case self::PRODUCT_TYPE_VARIABLE:
                    return new VariableProduct($product);
                case self::PRODUCT_TYPE_VARIATION:
                    if(!isset($parent)){
                        $parentPost = get_post_parent($pId);
                        if(!$parentPost){
                            throw new ProductFactoryException('ProductFactory - Unable to find parent for variation #'.$pId);
                        }
                        $parentProduct = wc_get_product($parentPost->ID);
                        return new ProductVariation($product, $parentProduct);
                    }
                    if($parent instanceof \WC_Product_Variable || $parent instanceof VariableProduct){
                        return new ProductVariation($product, $parent);
                    }
                    throw new ProductFactoryException('ProductFactory - Invalid parent provided for variation #'.$pId);
                case self::PRODUCT_TYPE_SIMPLE:
                default:
                    return new Product($product);
            }
        }catch (ProductException | \Throwable $e){
            throw new ProductFactoryException('Product Factory - '.$e->getMessage());
        }
    }

    /**
     * @param int|\WC_Product $product
     * @return VariableProduct
     * @throws ProductFactoryException
     */
    public static function createVariableProduct($product)
    {
        return self::create($product,null,self::PRODUCT_TYPE_VARIABLE);
    }

    /**
     * @param int|\WC_Product $product
     * @param \WC_Product_Variable|VariableProduct|null $parent
     * @return ProductVariation
     * @throws ProductFactoryException
     */
    public static function createProductVariation($product, $parent = null)
    {
        return self::create($product,$parent,self::PRODUCT_TYPE_VARIATION);
    }
}