<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\woocommerce\ExportableOrderItemInterface;
use Waboot\inc\core\woocommerce\WabootCoupon;
use Waboot\inc\core\woocommerce\WabootOrderItemBundle;
use Waboot\inc\core\woocommerce\WabootOrderItemBundledProduct;
use Waboot\inc\core\woocommerce\WabootOrderItemProduct;

class ExportableOrderItem implements ExportableOrderItemInterface
{
    /**
     * @var WabootOrderItemProduct|WabootOrderItemBundle|WabootCoupon|\WC_Order_Item_Shipping
     */
    private $orderItem;
    /**
     * @var ExportableOrder
     */
    private $order;

    public function __construct($item, ExportableOrder $order)
    {
        $this->orderItem = $item;
        $this->order = $order;
    }

    /**
     * @return WabootCoupon|WabootOrderItemBundle|WabootOrderItemProduct|\WC_Order_Item_Shipping
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * @return ExportableOrder
     */
    public function getOrder(): ExportableOrder
    {
        return $this->order;
    }

    /**
     * @return bool
     */
    public function isProduct(): bool
    {
        return $this->orderItem instanceof WabootOrderItemProduct;
    }

    /**
     * @return bool
     */
    public function isBundle(): bool
    {
        return $this->orderItem instanceof WabootOrderItemBundle;
    }

    /**
     * @return bool
     */
    public function isCoupon(): bool
    {
        return $this->orderItem instanceof WabootCoupon;
    }

    /**
     * @return bool
     */
    public function isShipping(): bool
    {
        return $this->orderItem instanceof \WC_Order_Item_Shipping;
    }

    /**
     * @return bool
     */
    public function canBeAddedAsItem(): bool
    {
        if(!$this->isCoupon()){
            return true; //Items other than coupons must be always added
        }
        try {
            /**
             * @var WabootCoupon
             */
            $item = $this->orderItem;
            return $item->getDiscountType() !== 'percent'; //We include only coupons with fixed-price discount
        }catch (\Exception $e){
            return false;
        }
    }

    /**
     * @return array
     * @throws \RuntimeException
     */
    public function generateExportData(): array
    {
        if($this->isBundle()){
            $data = [];
            $bundledItems = $this->orderItem->getItems();
            if(\is_array($bundledItems) && count($bundledItems) > 0){
                foreach ($bundledItems as $bundledItem){
                    if(!$bundledItem instanceof WabootOrderItemBundledProduct){
                        continue;
                    }
                    $product = $bundledItem->getProduct();
                    if(!$product instanceof \WC_Product){
                        continue;
                    }
                    $data[] = [
                        'type' => 'article',
                        'description' => $bundledItem->getWcOrderItem()->get_name(),
                        'subtotal' => (float) $bundledItem->getSubTotal(),
                        'total' => (float) $bundledItem->getTotal(),
                        'quantity' => $bundledItem->getWcOrderItem()->get_quantity(),
                        'vat' => '22',
                        'code' => $product->get_sku(),
                        'discount' => $bundledItem->getDiscountPercentage(),
                        'tax' => (float) $bundledItem->getWcOrderItem()->get_total_tax(),
                    ];
                }
            }
            return $data;
        }

        if($this->isProduct()){
            $item = $this->getOrderItem()->getWcOrderItem();
            $itemDiscountPercentage = 0;
            $itemFullPrice = $item->get_subtotal(); //this value is already account for quantity
            $itemDiscountedPrice = $item->get_total(); //this value is already account for quantity
            if($itemFullPrice !== $itemDiscountedPrice) {
                $priceDifference = $itemFullPrice - $itemDiscountedPrice;
                $itemDiscountPercentage = ($priceDifference / $itemFullPrice) * 100;
            }
            $data = [
                'type' => 'article',
                'description' => $item->get_name(),
                'subtotal' => (float) $itemFullPrice,
                'total' => (float) $itemDiscountedPrice,
                'quantity' => $item->get_quantity(),
                'vat' => '22', //22
                'code' => $item->get_product()->get_sku(),
                'discount' => $itemDiscountPercentage,
                'tax' => (float) $item->get_total_tax(),
            ];
            return $data;
        }

        if ($this->isShipping()) {
            /**
             * @var \WC_Order_Item_Shipping
             */
            $item = $this->getOrderItem();
            $data = [
                'type' => 'shipping',
                'description' => $item->get_name(),
                'subtotal' => (float) $item->get_total(),
                'total' => (float) $item->get_total(),
                'quantity' => 1,
                'vat' => '22',
                'code' => '',
                'discount' => 0,
                'tax' => (float) $item->get_total_tax()
            ];
            return $data;
        }

        if($this->isCoupon()) {
            /**
             * @var \WC_Order_Item_Coupon
             */
            $item = $this->getOrderItem()->getWcOrderItemCoupon();
            $data = [
                'type' => 'coupon',
                'description' => $item->get_name(),
                'subtotal' => (float) $item->get_discount() * -1,
                'total' => (float) $item->get_discount() * -1,
                'quantity' => 1,
                'vat' => '22',
                'code' => '',
                'discount' => 0,
                'tax' => (float) $item->get_discount_tax()
            ];
            return $data;
        }

        throw new \RuntimeException('Invalid order item type');
    }
}