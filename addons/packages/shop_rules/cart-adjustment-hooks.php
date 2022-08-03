<?php

namespace Waboot\addons\packages\shop_rules;

use Waboot\addons\packages\shop_rules\rule_params\Discount;

/**
 * @return ShopRule[]
 */
function getCartAdjustmentRules(): array
{
    return array_filter(fetchShopRules(), function (ShopRule $shopRule): bool {
        return $shopRule->getType() === ShopRule::TYPE_CART_ADJUSTMENT;
    });
}

function registerManualDiscount(ShopRule $rule): void
{
    add_filter(
        'woocommerce_get_shop_coupon_data',
        function ($newData, $data) use ($rule) {
            $couponCode = wc_format_coupon_code($rule->getKey());
            $discount = $rule->getDiscountParam();
            if ($data !== $couponCode) {
                return $newData;
            }

            switch ($discount->getType()) {
                case Discount::PERCENTAGE:
                    $type = 'percent';
                    break;
                case Discount::FLAT:
                default:
                    $type = 'fixed_cart';
                    break;
            }

            return [
                'discount_type' => $type,
                'amount' => $discount->getAmount(),
                'description' => $discount->getLabel(),
            ];
        },
        10,
        2
    );
    add_filter(
        'woocommerce_cart_totals_coupon_label',
        function (string $label, \WC_Coupon $coupon) use ($rule): string {
            if ($coupon->get_code() === wc_format_coupon_code($rule->getKey())) {
                return $rule->getDiscountParam()->getLabel();
            }

            return $label;
        },
        10,
        2
    );
}

add_action(
    'wp',
    function (): void {
        if (is_admin()) {
            return;
        }

        $cart = WC()->cart;
        $rules = getCartAdjustmentRules();
        if (empty($rules)) {
            return;
        }

        foreach ($rules as $r) {
            if (!$r->isActive()) {
                continue;
            }

            $discount = $r->getDiscountParam();
            if ($discount === null) {
                continue;
            }
            registerManualDiscount($r);

            $res = getRuleConditionsResult($r);
            $couponCode = wc_format_coupon_code($r->getKey());
            if ($res['conditionsMet']) {
                if (in_array($couponCode, $cart->applied_coupons)) {
                    continue;
                }

                $cart->apply_coupon($couponCode);
            } else {
                $cart->remove_coupon($couponCode);
            }
        }
    },
    10
);
