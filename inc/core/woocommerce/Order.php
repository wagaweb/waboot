<?php

namespace Waboot\inc\core\woocommerce;

use function Waboot\inc\isBundledIn;
use function Waboot\inc\isBundleProduct;

class Order
{
    /**
     * @var \WC_Order
     */
    public $wcOrder;
    /**
     * @var int
     */
    public $orderId;
    /**
     * @var int
     */
    protected $orderNumber;
    /**
     * @var OrderItemProduct[]
     */
    public $items;
    /**
     * @var \WC_DateTime
     */
    public $dateCompleted;
    /*
     * @var \WC_DateTime
     */
    public $dateCreated;
    /**
     * @var Coupon[]
     */
    private $coupons;
    /**
     * @var array
     */
    private $percentageCoupons;

    public function __construct(int $wcOrderId)
    {
        $wcOrder = wc_get_order($wcOrderId);
        if(!$wcOrder instanceof \WC_Order){
            throw new \RuntimeException('Invalid order id: '.$wcOrderId);
        }
        $this->wcOrder = $wcOrder;
        $this->orderId = $wcOrder->get_id();
    }

    /**
     * @return \WC_Order
     */
    public function getWcOrder(): \WC_Order
    {
        return $this->wcOrder;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return OrderItemProduct[]
     */
    public function getItems(): array
    {
        if(!isset($this->items)){
            $this->populateItems();
        }
        if(!\is_array($this->items)){
            return [];
        }
        return $this->items;
    }

    /**
     * Parse all order items and populate $this->items
     */
    public function populateItems(): void
    {
        //Add line items
        $lineItems = $this->getWcOrder()->get_items(['line_item']);
        $currentBundleId = null;
        $currentBundle = null;
        foreach ($lineItems as $itemId => $lineItem){
            if(!$lineItem instanceof \WC_Order_Item_Product){
                continue;
            }
            $productId = $lineItem->get_product_id();
            if(isBundleProduct($productId)){
                $currentBundleId = $productId;
                $currentBundle = new OrderItemBundle($lineItem);
                $this->items[$itemId] = $currentBundle;
                continue;
            }
            if($currentBundleId !== null){
                //Check if the current item is part of the previously parsed bundle
                if(isBundledIn($productId,$currentBundleId)){
                    $itemObj = new OrderItemBundledProduct($lineItem, $this, $currentBundle);
                    $currentBundle->addItem($itemObj);
                }else{
                    //If not, we assume that we reached a product not in a bundle
                    $currentBundleId = null;
                    $currentBundle = null;
                    //And add the current product
                    $itemObj = new OrderItemProduct($lineItem, $this);
                    $this->items[$itemId] = $itemObj;
                }
            }else{
                $itemObj = new OrderItemProduct($lineItem, $this);
                $this->items[$itemId] = $itemObj;
            }
        }

        //Add shipping and coupons
        $otherItems = $this->getWcOrder()->get_items(['shipping','coupon']);
        foreach ($otherItems as $itemId => $item){
            if($item instanceof \WC_Order_Item_Coupon){
                try{
                    $this->items[$itemId] = new Coupon($item,$this->orderId);
                }catch (\Exception $e){
                    continue;
                }
            }else{
                $this->items[$itemId] = $item;
            }
        }
    }

    /**
     * @return array
     */
    public function getRawItems(): array
    {
        return $this->getWcOrder()->get_items(['line_item','shipping','coupon']);
    }

    /**
     * @param bool $fallbackToOrderId
     * @return int|null
     */
    public function getOrderNumber(bool $fallbackToOrderId = false): ?int
    {
        if(isset($this->orderNumber)){
            return $this->orderNumber;
        }
        $orderNumber = get_post_meta($this->orderId, '_order_number', true);
        if($orderNumber && $orderNumber !== ''){
            $this->orderNumber = $orderNumber;
            return (int) $orderNumber;
        }
        if($fallbackToOrderId){
            return $this->orderId;
        }
        return null;
    }

    /**
     * Fetch order coupons and populate $this->coupons
     */
    public function fetchCoupons(): void
    {
        $r = [];
        $coupons = $this->getWcOrder()->get_coupons();
        if(!\is_array($coupons) || count($coupons) === 0){
            $this->coupons = [];
            return;
        }
        foreach ($coupons as $coupon){
            try{
                $couponCode = $coupon->get_code();
                $couponObj = new Coupon($coupon,$this->orderId);
                $r[$couponCode] = $couponObj;
            }catch (\Exception $e){
                continue;
            }
        }
        $this->coupons = $r;
    }

    /**
     * Get coupons which apply percentage discount
     *
     * @return Coupon[]
     */
    public function getPercentageCoupons(): array
    {
        if(!isset($this->percentageCoupons)){
            $this->fetchCoupons();
            $coupons = $this->coupons;
            if(!\is_array($coupons) || count($coupons) === 0){
                $this->percentageCoupons = [];
                return $this->percentageCoupons;
            }
            $this->percentageCoupons = array_filter($coupons,static function(Coupon $c){
                return $c->isDiscountTypePercentage();
            });
        }
        return $this->percentageCoupons;
    }

    /**
     * Get coupons which apply fixed amount discount
     *
     * @return Coupon[]
     */
    public function getFixedPriceCoupons(): array
    {
        if(!isset($this->coupons)){
            $this->fetchCoupons();
        }
        $coupons = $this->coupons;
        if(!\is_array($coupons) || count($coupons) === 0){
            return [];
        }
        $fixedCoupons = array_filter($coupons,static function(Coupon $c){
            return $c->isDiscountTypeFixed();
        });
        if(!\is_array($fixedCoupons)){
            return [];
        }
        return $fixedCoupons;
    }
}
