<?php

namespace Waboot\inc\cli\product_export;

use Waboot\inc\core\utils\Utilities;

class ExportableProductVariation extends AbstractExportableProduct
{
    /**
     * @var int
     */
    protected $parentId;
    /**
     * @var ExportableProductVariable
     */
    protected $parent;

    public function __construct(\WC_Product_Variation $product)
    {
        parent::__construct($product);
        $this->parentId = $product->get_parent_id();
    }

    public function createRecord(array $columnData): array
    {
        $record = parent::createRecord($columnData);
        $record['parent_id'] = $this->getParentProduct()->getId();
        if(isset($record['featured_image']) && $record['featured_image'] === ''){
            $image = '';
            $imageData = wp_get_attachment_image_src( $this->getParentProduct()->getWcProduct()->get_image_id(), 'woocommerce_thumbnail', false, []);
            if(\is_array($imageData) && count($imageData) > 0){
                $image = $imageData[0];
            }
            $image = apply_filters('waboot-product-exporter/variation/featured_image_src', $image, $this);
            $record['featured_image'] = $image;
        }
        return $record;
    }

    /**
     * @return ExportableProductVariable
     * @throws \RuntimeException
     */
    public function getParentProduct(): ExportableProductVariable
    {
        if(isset($this->parent)){
            return $this->parent;
        }
        $parent = wc_get_product($this->parentId);
        if(!$parent instanceof \WC_Product_Variable){
            throw new \RuntimeException('Variation #'.$this->getId().' has no parent');
        }
        $this->parent = ExportableProductFactory::create($parent);
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        try {
            $parent = $this->getParentProduct();
            return $parent->getDescription();
        }catch (\RuntimeException $e){
            return '';
        }
    }

    /**
     * @return string
     */
    public function getShortDescription(): string
    {
        try {
            $parent = $this->getParentProduct();
            return $parent->getShortDescription();
        }catch (\RuntimeException $e){
            return '';
        }
    }

    public function getTaxonomyTerms($taxonomyName): array
    {
        $terms = Utilities::getPostTermsHierarchical($this->getParentProduct()->getId(), $taxonomyName, [],true,true);
        if(!\is_array($terms)){
            return [];
        }
        return $terms;
    }

    public function getAttributeColumnDataValue($columnKey): string
    {
        $attRegEx = preg_match('|attribute:([_a-zA-Z0-9]+)|',$columnKey,$matches);
        if(!isset($matches) || count($matches) === 0){
            return '';
        }
        if(!strpos($columnKey,':variations')){
            return '';
        }
        $attribute = $matches[1];
        $attributeTermsString = $this->getWcProduct()->get_attribute($attribute); //Comma separated list of attributes values (term names)
        if(!\is_string($attributeTermsString)){
            return '';
        }
        return $attributeTermsString;
    }
}