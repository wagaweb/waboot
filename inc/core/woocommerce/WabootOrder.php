<?php

namespace Waboot\inc\core\woocommerce;


class WabootOrder
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
     * @var WabootOrderItem[]
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
     * @var WabootCoupon[]
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
     * @return WabootOrderItem[]
     */
    public function getItems(): array
    {
        if(!isset($this->items)){
            $items = $this->wcOrder->get_items();
            if(!\is_array($items) || count($items) === 0){
                return [];
            }
            $r = [];
            foreach ($items as $itemId => $item){
                if(!$item instanceof \WC_Order_Item_Product){
                    continue;
                }
                $r[$itemId] = new WabootOrderItem($item, $this);
            }
            $this->items = $r;
        }
        if(!\is_array($this->items)){
            return [];
        }
        return $this->items;
    }

    public function fetchCoupons(): void
    {
        $r = [];
        $coupons = $this->wcOrder->get_coupons();
        if(!\is_array($coupons) || count($coupons) === 0){
            $this->coupons = [];
            return;
        }
        foreach ($coupons as $coupon){
            try{
                $couponCode = $coupon->get_code();
                $couponObj = new WabootCoupon($coupon,$this->orderId);
                $r[$couponCode] = $couponObj;
            }catch (\Exception $e){
                continue;
            }
        }
        $this->coupons = $r;
    }

    /**
     * @return WabootCoupon[]
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
            $this->percentageCoupons = array_filter($coupons,static function(WabootCoupon $c){
                return $c->isDiscountTypePercentage();
            });
        }
        return $this->percentageCoupons;
    }

    /**
     * @return WabootCoupon[]
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
        $fixedCoupons = array_filter($coupons,static function(WabootCoupon $c){
            return $c->isDiscountTypeFixed();
        });
        if(!\is_array($fixedCoupons)){
            return [];
        }
        return $fixedCoupons;
    }

    /**
     * @return array
     */
    public function getDiscountsPercentagesByCoupons(): array
    {
        $percentages = [0,0,0];
        $i = 0;
        $coupons = $this->getPercentageCoupons();
        foreach($coupons as $coupon){
            $percentages[$i] = $coupon->getAmount();
            $i++;
            if($i > 3){
                break;
            }
        }
        return $percentages;
    }
}
