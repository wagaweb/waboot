<?php

namespace Waboot\inc\core\woocommerce;

class Coupon
{
    /**
     * @var string
     */
    private $code;
    /**
     * @var int
     */
    private $orderId;
    /**
     * @var \WP_Post
     */
    private $wpPost;
    /**
     * @var \WC_Coupon
     */
    private $wcCoupon;
    /**
     * @var \WC_Order_Item_Coupon
     */
    private $wcOrderItemCoupon;
    /**
     * @var array
     */
    private $meta;
    /**
     * @var int
     */
    private $orderItemId;
    /**
     * @var array
     */
    private $couponData;
    /**
     * @var float
     */
    private $discountAmount;
    /**
     * @var float
     */
    private $discountAmountTax;
    /**
     * @var string
     */
    private $discountType;

    /**
     * @param \WC_Order_Item_Coupon $coupon
     * @param int $orderId
     * @throws \Exception
     */
    public function __construct(\WC_Order_Item_Coupon $coupon, int $orderId)
    {
        $this->wcOrderItemCoupon = $coupon;
        $this->code = $coupon->get_code();
        $couponPost = get_page_by_title($this->code, OBJECT, 'shop_coupon');
        if($couponPost instanceof \WP_Post){
            $wpPost = $couponPost;
            if(!$wpPost instanceof \WP_Post){
                throw new \Exception('Invalid post');
            }
            $this->wpPost = $wpPost;
            $wcCoupon = new \WC_Coupon($this->wpPost->ID);
            if(!$wcCoupon instanceof \WC_Coupon){
                throw new \Exception('Invalid coupon');
            }
            $this->wcCoupon = $wcCoupon;
        }
        $this->orderId = $orderId;
        $this->fetchOrderItemId();
        $this->fetchMeta();
        $this->fetchData();
        if(!isset($this->orderItemId)){
            throw new \Exception('No order item id');
        }
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getOrderId(): int
    {
        return $this->orderId;
    }

    /**
     * @return \WP_Post
     */
    public function getWpPost(): ?\WP_Post
    {
        return $this->wpPost;
    }

    /**
     * @return \WC_Coupon
     */
    public function getWcCoupon(): ?\WC_Coupon
    {
        return $this->wcCoupon;
    }

    /**
     * @return \WC_Order_Item_Coupon
     */
    public function getWcOrderItemCoupon(): \WC_Order_Item_Coupon
    {
        return $this->wcOrderItemCoupon;
    }

    /**
     * @return array
     */
    public function getCouponData(): array
    {
        if(!isset($this->couponData)){
            $this->fetchData();
        }
        if(!\is_array($this->couponData)){
            return [];
        }
        return $this->couponData;
    }

    /**
     * Get the coupon discount amount (percentage or fixed)
     * @return string|int|float
     */
    public function getAmount()
    {
        if($this->isAPost()){
            return $this->wcCoupon->get_amount();
        }
        $data = $this->getCouponData();
        if(isset($data['amount'])){
            return $data['amount'];
        }
        return 0;
    }

    /**
     * @return float
     */
    public function getDiscountAmount(): float
    {
        if(!isset($this->discountAmount)){
            if(!isset($this->meta)){
                $this->fetchMeta();
            }
            if(isset($this->meta['discount_amount'])){
                $data = (float) $this->meta['discount_amount']['meta_value'];
                if($data !== false){
                    $this->discountAmount = $data;
                }
            }
        }
        return $this->discountAmount;
    }

    /**
     * @return float
     */
    public function getDiscountAmountTax(): float
    {
        if(!isset($this->discountAmountTax)){
            if(!isset($this->meta)){
                $this->fetchMeta();
            }
            if(isset($this->meta['discount_amount_tax'])){
                $data = (float) $this->meta['discount_amount_tax']['meta_value'];
                if($data !== false){
                    $this->discountAmountTax = $data;
                }
            }
        }
        return $this->discountAmountTax;
    }

    /**
     * @return string|null
     */
    public function getDiscountType(): ?string
    {
        if(isset($this->discountType)){
            return $this->discountType;
        }
        $discountType = '';
        $this->fetchMeta();
        if(isset($this->meta['discount_type'])){
            $discountType = $this->meta['discount_type'];
        }
        if($discountType === ''){
            $this->fetchData();
            if(isset($this->couponData['discount_type'])){
                $discountType = $this->couponData['discount_type'];
            }
        }
        if($discountType !== ''){
            $this->discountType = $discountType;
        }
        return $this->discountType;
    }

    /**
     * @return bool
     */
    public function isDiscountTypePercentage(): bool
    {
        return $this->getDiscountType() === 'percent';
    }

    /**
     * @return bool
     */
    public function isDiscountTypeFixed(): bool
    {
        return $this->getDiscountType() !== 'percent';
    }

    /**
     * @return bool
     */
    public function isAPost()
    {
        return $this->wpPost !== null && $this->wcCoupon !== null;
    }

    /**
     * Fetch order item id
     */
    protected function fetchOrderItemId(): void
    {
        global $wpdb;
        $sql = 'SELECT order_item_id FROM `'.$wpdb->prefix.'woocommerce_order_items'.'` WHERE order_item_name = "%s" AND order_id = %d';
        $sql = $wpdb->prepare($sql,$this->code,$this->orderId);
        $r = $wpdb->get_results($sql);
        if(\is_array($r) && count($r) > 0){
            $this->orderItemId = (int) $r[0]->order_item_id;
        }
    }

    /**
     * Fetch coupon meta data
     */
    protected function fetchMeta(): void
    {
        global $wpdb;
        $sql = 'SELECT * FROM `'.$wpdb->prefix.'woocommerce_order_itemmeta'.'` WHERE order_item_id = %d';
        $sql = $wpdb->prepare($sql,$this->orderItemId);
        $r = $wpdb->get_results($sql,ARRAY_A);
        $metas = [];
        if(\is_array($r) && count($r) > 0){
            foreach ($r as $metaData){
                $metaKey = $metaData['meta_key'];
                unset($metaData['meta_key'], $metaData['order_item_id']);
                $metas[$metaKey] = $metaData;
            }
        }
        if($this->isAPost()){
            $postMetas = get_post_meta($this->getWpPost()->ID);
            if(!\is_array($postMetas) && !empty($postMetas)){
                foreach ($postMetas as $metaKey => $metaValue){
                    if(\array_key_exists($metaKey,$metas)){
                        continue;
                    }
                    $metas[$metaKey] = get_post_meta($this->getWpPost()->ID,$metaKey,true);
                }
            }
        }
        $this->meta = $metas;
    }

    /**
     * Fetch coupon additional data
     */
    protected function fetchData(): void
    {
        if(!isset($this->meta)){
            $this->fetchMeta();
        }
        if(isset($this->meta['coupon_data'])){
            $data = @unserialize($this->meta['coupon_data']['meta_value']);
            if($data !== false){
                $this->couponData = $data;
            }
        }
    }
}