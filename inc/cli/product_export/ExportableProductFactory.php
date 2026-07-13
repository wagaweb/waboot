<?php

namespace Waboot\inc\cli\product_export;

class ExportableProductFactory
{
    /**
     * @param \WC_Product $product
     * @return ExportableProduct|ExportableProductVariable|ExportableProductVariation
     */
    public static function create(\WC_Product $product)
    {
        if ($product instanceof \WC_Product_Variable) {
            return new ExportableProductVariable($product);
        }
        if($product instanceof \WC_Product_Variation) {
            return new ExportableProductVariation($product);
        }
        return new ExportableProduct($product);
    }
}