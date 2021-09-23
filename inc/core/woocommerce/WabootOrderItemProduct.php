<?php

namespace Waboot\inc\core\woocommerce;

use function Waboot\inc\getProductSalePercentage;
use function Waboot\inc\isBundleProduct;

class WabootOrderItemProduct
{
    /**
     * @var \WC_Order_Item_Product
     */
    protected $wcOrderItem;
    /**
     * @var WabootOrder
     */
    protected $order;
    /**
     * @var \WC_Product
     */
    protected $product;
    /**
     * @var string
     */
    private $sku;

    public function __construct(\WC_Order_Item_Product $item, WabootOrder $order)
    {
        $this->wcOrderItem = $item;
        $this->order = $order;
    }

    /**
     * @return \WC_Order_Item_Product
     */
    public function getWcOrderItem(): \WC_Order_Item_Product
    {
        return $this->wcOrderItem;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->wcOrderItem->get_id();
    }

    /**
     * @return int|null
     */
    public function getProductId(): ?int
    {
        $p = $this->getProduct();
        if($p instanceof \WC_Product){
            return $p->get_id();
        }
        return null;
    }

    /**
     * @return WabootOrder
     */
    public function getOrder(): WabootOrder
    {
        return $this->order;
    }

    /**
     * @return \WC_Product|null
     */
    public function getProduct(): ?\WC_Product
    {
        if(!isset($this->product)){
            if($this->isProductVariation()){
                $product = wc_get_product($this->wcOrderItem->get_variation_id());
            }else{
                $product = wc_get_product($this->wcOrderItem->get_product_id());
            }
            if($product instanceof \WC_Product){
                $this->product = $product;
            }
        }
        return $this->product;
    }

    /**
     * @return string|null
     */
    public function getSku(): ?string
    {
        if(!isset($this->sku)){
            $product = $this->getProduct();
            if($product !== null){
                $this->sku = $product->get_sku();
            }
        }
        return $this->sku;
    }

    /**
     * @return bool
     */
    public function isProductVariation(): bool
    {
        $variationId = (int) $this->wcOrderItem->get_variation_id();
        return $variationId !== 0;
    }

    /**
     * @return bool
     */
    public function isOnSale(): bool
    {
        return $this->wcOrderItem->get_product()->is_on_sale();
    }

    /**
     * Get the item price for quantity = 1
     */
    public function getUnitPrice($useSubTotal = false)
    {
        $qty = $this->getWcOrderItem()->get_quantity();
        if($qty > 1){
            $itemPrice = $useSubTotal ? $this->getWcOrderItem()->get_subtotal() : $this->getWcOrderItem()->get_total();
            $unitPrice = (float) $itemPrice / (float) $qty;
            return $unitPrice;
        }
        return $useSubTotal ? $this->getWcOrderItem()->get_subtotal() : $this->getWcOrderItem()->get_total();
    }

    /**
     * @return float
     */
    public function getDiscountPercentage(): float
    {
        $itemFullPrice = $this->getWcOrderItem()->get_subtotal();
        $itemDiscountedPrice = $this->getWcOrderItem()->get_total();
        if($itemFullPrice !== $itemDiscountedPrice){
            $priceDifference = $itemFullPrice - $itemDiscountedPrice;
            $discountPercentage = ($priceDifference / $itemFullPrice) * 100;
            return $discountPercentage;
        }
        return 0;
    }
}