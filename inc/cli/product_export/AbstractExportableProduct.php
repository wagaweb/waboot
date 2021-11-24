<?php

namespace Waboot\inc\cli\product_export;

use Waboot\inc\core\utils\Utilities;

abstract class AbstractExportableProduct implements ExportableProductInterface
{
    /**
     * @var \WC_Product
     */
    private $wcProduct;

    public function __construct(\WC_Product $product)
    {
        $this->wcProduct = $product;
    }

    /**
     * @return \WC_Product
     */
    public function getWcProduct(): \WC_Product
    {
        return $this->wcProduct;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->getWcProduct()->get_id();
    }

    /**
     * @return string
     */
    public function getRegularPrice(): string
    {
        return $this->getWcProduct()->get_regular_price();
    }

    /**
     * @return string
     */
    public function getSalePrice(): string
    {
        return $this->getWcProduct()->get_sale_price();
    }

    public function getDescription(): string
    {
        $desc = $this->getWcProduct()->get_description();
        $desc = str_replace(';','&#59;',$desc);
        return $desc;
    }

    public function getShortDescription(): string
    {
        $desc = $this->getWcProduct()->get_short_description();
        $desc = str_replace(';','&#59;',$desc);
        return $desc;
    }

    public function createRecord(array $columnData): array
    {
        if(empty($columnData)){
            return [];
        }
        $record = [];
        foreach ($columnData as $dataKey){
            $record[$dataKey] = $this->getData($dataKey);
        }
        return $record;
    }

    /**
     * @param string $dataKey
     * @return string|int
     */
    public function getData(string $dataKey)
    {
        $dataValue = '';
        switch ($dataKey){
            case 'id':
                $dataValue = $this->getWcProduct()->get_id();
                break;
            case 'sku':
                $dataValue = $this->getWcProduct()->get_sku();
                break;
            case 'name':
                $dataValue = $this->getWcProduct()->get_name();
                break;
            case 'description':
                $dataValue = $this->getWcProduct()->get_description();
                break;
            case 'short_description':
                $dataValue = $this->getWcProduct()->get_short_description();
                break;
            case 'meta:_regular_price':
                $dataValue = $this->getWcProduct()->get_regular_price();
                break;
            case 'meta:_sale_price':
                $dataValue = $this->getWcProduct()->get_sale_price();
                break;
        }
        if($dataValue === '' && strpos($dataKey,'meta:') === 0){
            $metaKeyRegEx = preg_match('|meta:([_a-zA-Z0-9]+)|',$dataKey,$matches);
            if(isset($matches) && count($matches) > 1){
                $metaKey = $matches[1];
                $dataValue = get_post_meta($this->getId(),$metaKey,true);
            }
        }
        if($dataValue === '' && strpos($dataKey,'taxonomy:') === 0){
            $taxonomyRegEx = preg_match('|taxonomy:([_a-zA-Z0-9]+)|',$dataKey,$matches);
            if(isset($matches) && count($matches) > 1){
                $taxonomy = $matches[1];
                $terms = $this->getTaxonomyTerms($taxonomy);
                if(\is_array($terms) && count($terms) > 0){
                    $dataValue = implode(' > ',wp_list_pluck($terms,'name'));
                }
            }
        }
        if($dataValue === '' && strpos($dataKey,'attribute:') === 0){
            $dataValue = $this->getAttributeColumnDataValue($dataKey);
        }
        return $dataValue;
    }

    public function getTaxonomyTerms($taxonomyName): array
    {
        $terms = Utilities::getPostTermsHierarchical($this->getId(), $taxonomyName, [],true,true);
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
        $attribute = $matches[1];
        $attributeTermsString = $this->getWcProduct()->get_attribute($attribute); //Comma separated list of attributes values (term names)
        if(!\is_string($attributeTermsString)){
            return '';
        }
        return $attributeTermsString;
    }
}