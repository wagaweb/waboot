<?php

namespace Waboot\inc\core\woocommerce;

use function Waboot\inc\getProductSalePercentage;
use function Waboot\inc\isBundleProduct;

class WabootOrderItemBundledProduct extends WabootOrderItemProduct
{
    /**
     * @var WabootOrderItemBundle
     */
    private $parent;

    /**
     * AromaVeroOrderItemBundledProduct constructor.
     * @param \WC_Order_Item_Product $item
     * @param WabootOrder $order
     * @param WabootOrderItemBundle $paren
     */
    public function __construct(\WC_Order_Item_Product $item, WabootOrder $order, WabootOrderItemBundle $parent)
    {
        parent::__construct($item, $order);
        $this->order = $order;
        $this->wcOrderItem = $item;
        $this->parent = $parent;
    }

    /**
     * @return float
     */
    public function getDiscountPercentage(): float
    {
        $bundleTotals = $this->parent->getTotals();
        $bundleRegularPrice = $bundleTotals['subtotal']; //Il prezzo che avrebbe il bundle sommando i regular_price di tutti gli items moltiplicati per la loro quantità
        if($bundleRegularPrice === 0){
            return 0;
        }
        $bundleCurrentPrice = (float) $this->parent->getPrice(); //Il prezzo a cui il bundle è venduto
        if($bundleRegularPrice === $bundleCurrentPrice){
            return 0;
        }
        return (($bundleRegularPrice - $bundleCurrentPrice) / $bundleRegularPrice) * 100;
    }

    /**
     * Get the subtotal (regular_price * quantity) of the bundled product
     *
     * @return float|null
     */
    public function getSubTotal(): ?float
    {
        $product = $this->wcOrderItem->get_product();
        $price = 0;
        if($product instanceof \WC_Product){
            $itemPrice = $product->get_regular_price();
            $itemNetPrice = $itemPrice / 1.22; //Prezzo al netto dell'iva
            $price = $itemNetPrice * $this->wcOrderItem->get_quantity();
        }
        return $price;
    }

    /**
     * Get the total (sale_price * quantity) of the bundled product
     *
     * @return float|null
     */
    public function getTotal(): ?float
    {
        $subTotal = $this->getSubTotal();
        $discountPercentage = $this->getDiscountPercentage();
        if($discountPercentage > 0){
            $discount = ($subTotal * $discountPercentage) / 100;
            $total = $subTotal - $discount;
        }else{
            $total = $subTotal;
        }
        return $total;
    }

    /**
     * @return int
     */
    public function getParentBundleId(): int
    {
        return $this->parent->getWcOrderItem()->get_product_id();
    }
}