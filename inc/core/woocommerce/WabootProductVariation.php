<?php

namespace Waboot\inc\core\woocommerce;

class WabootProductVariation
{
    public const SIZE_ATTRIBUTE_NAME = 'taglia';
    /**
     * @var int
     */
    private $id;
    /**
     * @var \WC_Product_Variation
     */
    private $wcProduct;
    /**
     * @var int
     */
    private $parentId;
    /**
     * @var WabootProduct
     */
    private $parent;
    /**
     * @var array
     */
    private $data;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $nameWithSize;

    public function __construct($variation, $parent = null)
    {
        if(\is_int($variation)){
            $this->id = $variation;
            $pType = get_post_type($variation);
            if($pType !== 'product_variation'){
                throw new \RuntimeException('#'.$this->id.' is not Product Variation');
            }
        }elseif ($variation instanceof \WC_Product_Variation){
            $this->id = $variation->get_id();
            $this->wcProduct = $variation;
        }else{
            throw new \RuntimeException('#'.$this->id.' is not Product Variation');
        }

        if(!isset($parent)){
            global $wpdb;
            $posts_table = $wpdb->prefix."posts";
            $parentId = $wpdb->get_var("SELECT post_parent FROM {$posts_table} WHERE ID = {$this->id}");
            if(!\is_string($parentId)){
                throw new \RuntimeException('#'.$this->id.' has no parent');
            }
            $this->parentId = (int) $parentId;
        }elseif ($parent instanceof WabootProduct){
            $this->parent = $parent;
            $this->parentId = $parent->getId();
        }else{
            throw new \RuntimeException(' Invalid parent provided');
        }
    }

    /**
     * @return \WC_Product_Variation
     * @throws \RuntimeException
     */
    public function getWcProduct(): \WC_Product_Variation
    {
        if(!isset($this->wcProduct)){
            $this->wcProduct = wc_get_product($this->id);
            if(!$this->wcProduct instanceof \WC_Product_Variation){
                throw new \RuntimeException('#'.$this->id.' is not a Product Variation');
            }
        }
        return $this->wcProduct;
    }

    /**
     * @return WabootProduct
     * @throws \RuntimeException
     */
    public function getParent(): WabootProduct
    {
        if(!isset($this->parent)){
            if(!isset($this->parentId)){
                throw new \RuntimeException('No parent_id found');
            }
            try{
                $parent = $this->createVariableProductInstance($this->parentId);
                $this->parent = $parent;
            }catch (\RuntimeException $e){
                throw new \RuntimeException($e->getMessage());
            }
        }
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if(isset($this->name)){
            return $this->name;
        }
        try{
            $p = $this->getWcProduct();
            $this->name = $p->get_title();
            return $this->name;
        }catch (\RuntimeException $e){
            return '';
        }
    }

    /**
     * @return false|\WP_Term
     */
    public function getBrand()
    {
        try{
            return $this->getParent()->getBrand();
        }catch (\RuntimeException $e){
            return false;
        }
    }

    /**
     * @return false|\WP_Term
     */
    public function getSize()
    {
        if(isset($this->size)){
            return $this->size;
        }
        $attributes = wc_get_product_variation_attributes($this->id);
        if(array_key_exists($this->getSizeAttributeFullName(),$attributes)){
            $sizeTerm = get_term_by('slug', $attributes[$this->getSizeAttributeFullName()], $this->getSizeAttributeTaxonomyName());
            if($sizeTerm instanceof \WP_Term){
                $this->size = $sizeTerm;
                return $this->size;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getSizeAttributeTaxonomyName(): string
    {
        return 'pa_'.self::SIZE_ATTRIBUTE_NAME;
    }

    /**
     * @return string
     */
    public function getSizeAttributeFullName(): string
    {
        return 'attribute_'.$this->getSizeAttributeTaxonomyName();
    }

    /**
     * @return string
     */
    public function getNameWithSize(): ?string
    {
        if(isset($this->nameWithSize)){
            return $this->nameWithSize;
        }
        $size = $this->getSize();
        if(!$size){
            $this->nameWithSize = $this->getName();
        }else{
            $this->nameWithSize = $this->getName().' - '.$size->name;
        }
        return $this->nameWithSize;
    }

    /**
     * @return string[]
     */
    public function getData(): array
    {
        if(isset($this->data) && \is_array($this->data)){
            return $this->data;
        }
        $data = [
            'name' => '',
            'brand' => '',
            'size' => '',
        ];
        $data['name'] = $this->getName();
        $data['name_size'] = $this->getNameWithSize();
        $data['brand'] = $this->getBrand() !== false ? $this->getBrand()->name : '';
        $data['size'] = $this->getSize() !== false ? $this->getSize()->name : '';
        $this->data = $data;
        return $this->data;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @return string
     */
    public function getPermalink(): string
    {
        return get_the_permalink($this->getId());
    }

    /**
     * @param int $parentId
     * @return WabootProduct
     */
    protected function createVariableProductInstance(int $parentId): WabootProduct
    {
        return new WabootProduct($parentId);
    }
}