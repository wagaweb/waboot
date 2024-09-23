<?php

namespace Waboot\inc\core\woocommerce;

use Waboot\inc\core\utils\Utilities;
use function Waboot\inc\getProductType;

class ProductVariation extends Product
{
    public const SIZE_ATTRIBUTE_NAME = 'taglia';
    /**
     * @var int
     */
    protected $id;
    /**
     * @var \WC_Product_Variation
     */
    protected $wcProduct;
    /**
     * @var int
     */
    private $parentId;
    /**
     * @var VariableProduct
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
     * @var \WP_Term
     */
    private $size;
    /**
     * @var string
     */
    private $nameWithSize;

    /**
     * @param null|int|\WC_Product $variation
     * @param null|int|\WC_Product_Variable|VariableProduct $parent
     * @throws ProductException|ProductFactoryException
     */
    public function __construct($variation = null, $parent = null)
    {
        if(!isset($variation)){
            parent::__construct($variation,ProductFactory::PRODUCT_TYPE_VARIATION);
            if ($parent instanceof VariableProduct){
                $this->parent = $parent;
                $this->parentId = $parent->getId();
            }elseif($parent instanceof \WC_Product_Variable){
                $wbParent = ProductFactory::createVariableProduct($parent);
                $this->parent = $wbParent;
                $this->parentId = $wbParent->getId();
            }else{
                throw new \RuntimeException('ProductVariation - Invalid parent provided');
            }
            return;
        }
        $pType = \is_int($variation) ? getProductType($variation) : getProductType($variation->get_id());
        if($pType !== ProductFactory::PRODUCT_TYPE_VARIATION){
            throw new ProductException('ProductVariation - provided $variation is not a variation');
        }
        parent::__construct($variation);

        if(!isset($parent)){
            $parentId = Utilities::getPostParentId($this->getId());
            if(!$parentId){
                throw new \RuntimeException('ProductVariation - #'.$this->id.' has no parent');
            }
            $this->parentId = $parentId;
        }elseif ($parent instanceof VariableProduct){
            $this->parent = $parent;
            $this->parentId = $parent->getId();
        }elseif($parent instanceof \WC_Product_Variable){
            $wbParent = ProductFactory::createVariableProduct($parent);
            $this->parent = $wbParent;
            $this->parentId = $wbParent->getId();
        }else{
            throw new \RuntimeException('ProductVariation - Invalid parent provided');
        }
    }

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @return \WC_Product_Variation
     * @throws \RuntimeException|ProductException
     */
    public function getWcProduct(): \WC_Product_Variation
    {
        if(!isset($this->wcProduct)){
            if($this->isNew()){
                $wcProduct = wc_get_product_object(ProductFactory::PRODUCT_TYPE_VARIATION);
            }else{
                $wcProduct = wc_get_product($this->id);
                if(!$wcProduct instanceof \WC_Product_Variation){
                    throw new ProductException('ProductVariation - #'.$this->id.' is not a Product Variation');
                }
            }
            $this->wcProduct = $wcProduct;
        }
        return $this->wcProduct;
    }

    /**
     * @return VariableProduct
     * @throws ProductException
     */
    public function getParent(): VariableProduct
    {
        if(isset($this->parent)){
            return $this->parent;
        }
        if(!isset($this->parentId)){
            throw new ProductException('ProductVariation - No parent_id found');
        }
        try{
            $parent = ProductFactory::createProductVariation($this->getParentId());
            $this->parent = $parent;
            return $this->parent;
        }catch (ProductFactoryException $e){
            throw new ProductException('ProductVariation - '.$e->getMessage());
        }
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
        }catch (ProductException | \Throwable $e){
            return '';
        }
    }

    /**
     * @param bool $reverse
     * @param bool $asString
     * @param string $separator
     * @return array|string
     */
    public function getCategories(bool $reverse, bool $asString = false, string $separator = ' > ')
    {
        try{
            return $this->getParent()->getCategories($reverse, $asString, $separator);
        }catch (ProductException | \Throwable $e){
            return null;
        }
    }

    /**
     * @return null|\WP_Term
     */
    public function getBrand(): ?\WP_Term
    {
        try{
            return $this->getParent()->getBrand();
        }catch (ProductException | \Throwable $e){
            return null;
        }
    }

    /**
     * @return null|\WP_Term
     */
    public function getSize(): ?\WP_Term
    {
        if($this->isNew()){
            return null;
        }
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
        return null;
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
            'name' => $this->getName(),
            'brand' => $this->getBrand() !== null ? $this->getBrand()->name : '',
            'size' => $this->getSize() !== null ? $this->getSize()->name : '',
        ];
        $data['name_size'] = $this->getNameWithSize();
        $this->data = $data;
        return $this->data;
    }

    /**
     * Add a term attribute to the variation. Needs to be saved afterwards.
     *
     * @param \WP_Term $term
     * @return void
     * @throws ProductException
     */
    public function addTermAttribute(\WP_Term $term): void
    {
        $wcProduct = $this->getWcProduct();
        $currentAttributes = $wcProduct->get_attributes();
        $currentAttributes[$term->taxonomy] = $term->slug;
        $wcProduct->set_attributes($currentAttributes);
    }
}