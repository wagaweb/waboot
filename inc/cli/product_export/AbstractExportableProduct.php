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
        $desc = str_replace(["\r","\n"],'.',$desc);
        $desc = wp_strip_all_tags($desc);
        return $desc;
    }

    public function getShortDescription(): string
    {
        $desc = $this->getWcProduct()->get_short_description();
        $desc = str_replace(';','&#59;',$desc);
        $desc = str_replace(["\r","\n"],'.',$desc);
        $desc = wp_strip_all_tags($desc);
        return $desc;
    }

    public function createRecord(array $columnData): ?array
    {
        if(apply_filters('waboot/cli/product_export/can_create_record', true, $this, $columnData) === false){
            return null;
        }
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
            case 'type':
                $dataValue = $this->getWcProduct()->get_type();
                break;
            case 'sku':
                $dataValue = $this->getWcProduct()->get_sku();
                break;
            case 'name':
                $dataValue = $this->getWcProduct()->get_name();
                break;
            case 'description':
                $dataValue = $this->getDescription();
                break;
            case 'short_description':
                $dataValue = $this->getShortDescription();
                break;
            case 'status':
                $dataValue = $this->getWcProduct()->get_status();
                break;
            case 'featured_image':
                $image = '';
                $p = $this->getWcProduct();
                if($p->get_image_id()){
                    $imageData = wp_get_attachment_image_src( $p->get_image_id(), 'woocommerce_thumbnail', false, []);
                    if(\is_array($imageData) && count($imageData) > 0){
                        $image = $imageData[0];
                    }
                }
                $image = apply_filters('waboot-product-exporter/featured_image_src', $image, $this);
                $dataValue = $image;
                break;
            case 'meta:_regular_price':
                $dataValue = $this->getWcProduct()->get_regular_price();
                break;
            case 'meta:_sale_price':
                $dataValue = $this->getWcProduct()->get_sale_price();
                break;
            case 'meta:_stock':
                $qty = $this->getWcProduct()->get_stock_quantity();
                if($qty === null){
                    $dataValue = '';
                }else{
                    $dataValue = $qty;
                }
                break;
            case 'meta:_stock_status':
                $dataValue = $this->getWcProduct()->get_stock_status();
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
        if($dataValue === '' && strpos($dataKey,'cs:') === 0){
            $dataKey = ltrim($dataKey,'cs:');
            $dataKey = str_replace('\\','/',$dataKey);
            //Try custom columns
            $regExMatch = preg_match('|:([a-zA-Z_/]+)|',$dataKey,$functionNameRegExResults);
            if(isset($functionNameRegExResults) && \is_array($functionNameRegExResults) && !empty($functionNameRegExResults)){
                $functionName = $functionNameRegExResults[1] ?? '';
                if($functionName !== ''){
                    $functionName = str_replace('/','\\',$functionName);
                    if(function_exists($functionName)){
                        $dataValue = $functionName($this);
                    }
                }
            }
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
