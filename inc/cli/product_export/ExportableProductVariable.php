<?php

namespace Waboot\inc\cli\product_export;

use function Waboot\inc\getAllProductVariationIds;

class ExportableProductVariable extends AbstractExportableProduct
{
    public function __construct(\WC_Product_Variable $product)
    {
        parent::__construct($product);
    }

    public function createRecord(array $columnData): array
    {
        if(!$this->hasVariations()){
            return parent::createRecord($columnData);
        }
        $records = [];
        $records[] = parent::createRecord($columnData);
        foreach ($this->getVariations() as $exportableProductVariation){
            if(!$exportableProductVariation instanceof ExportableProductVariation){
                continue;
            }
            $records[] = $exportableProductVariation->createRecord($columnData);
        }
        return $records;
    }

    /**
     * @return bool
     */
    public function hasVariations(): bool
    {
        return $this->getVariations() !== null;
    }

    /**
     * @return \Generator|null
     */
    public function getVariations(): ?\Generator
    {
        $variationIds = getAllProductVariationIds($this->getId());
        if(empty($variationIds)){
            return null;
        }
        foreach ($variationIds as $variationId){
            $variation = wc_get_product($variationId);
            if(!$variation instanceof \WC_Product_Variation){
                continue;
            }
            yield ExportableProductFactory::create($variation);
        }
    }
}