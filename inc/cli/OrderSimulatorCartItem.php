<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\DB;
use Waboot\inc\core\DBException;
use Waboot\inc\core\DBUnavailableDependencyException;

class OrderSimulatorCartItem
{
    /**
     * @var string
     */
    protected $code;
    /**
     * @var string
     */
    protected $type;
    /**
     * @var int
     */
    protected $quantity;

    public function __construct($data)
    {
        $code = $data['code'] ?? false;
        if(!$code){
            throw new OrderSimulatorCartItemException('OrderSimulatorCartItem: No code');
        }
        $type = $data['type'] ?? false;
        if(!$type){
            throw new OrderSimulatorCartItemException('OrderSimulatorCartItem: No type');
        }
        $quantity = $data['quantity'] ?? 1;
        if($quantity <= 0){
            $quantity = 1;
        }
        $this->code = $code;
        $this->type = $type;
        $this->quantity = (int) $quantity;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return bool
     */
    public function isCoupon(): bool
    {
        return $this->type === 'coupon';
    }

    /**
     * @param \WC_Cart $cart
     * @return void|null
     * @throws DBException
     * @throws OrderSimulatorCartItemException
     * @throws \Exception
     */
    public function addToCart(\WC_Cart $cart): void
    {
        switch($this->type){
            case 'line_item':
                $productId = wc_get_product_id_by_sku($this->code);
                $product = wc_get_product($productId);
                if(!$product instanceof \WC_Product){
                    return;
                }
                if($product instanceof \WC_Product_Variation){
                    $parentId = $product->get_parent_id();
                    $cart->add_to_cart($parentId,$this->quantity,$productId);
                }else{
                    $cart->add_to_cart($productId,$this->quantity);
                }
                break;
            case 'coupon':
                $db = DB::getInstance();
                $r = $db->getQueryBuilder()::table('posts')
                    ->select('ID')
                    ->where('post_title','=',$this->code)
                    ->where('post_type','=','shop_coupon')
                    ->get()->toArray();
                if(!\is_array($r) || count($r) === 0){
                    return;
                }
                $couponId = (int) $r[0]->ID;
                if($couponId > 0){
                    $applied = $cart->apply_coupon($this->code);
                    if($applied === false){
                        throw new OrderSimulatorCartItemException('Coupon '.$this->getCode().' not applied');
                    }
                }
                break;
        }
    }
}