<?php

namespace Waboot\inc\core;

function defaultShippingAddressNameIsMandatory(): bool {
    return apply_filters('wawoo/multiple_shipping_address/default_shipping_address_name_is_mandatory', true);
}

function mustDisplayDefaultShippingAddressName(): bool {
    return apply_filters('wawoo/multiple_shipping_address/must_display_default_shipping_address_name', true);
}

function generateShippingAddressName(int $orderId, array $postedData): string {
    $shippingAddress1 = $postedData['shipping_address_1'] ?? '';
    $shippingCity = $postedData['shipping_city'] ?? '';
    $shippingAddressName = $shippingAddress1.', '.$shippingCity;
    if($shippingAddressName === ', '){
        $order = wc_get_order($orderId);
        if($order){
            $shippingAddress1 = $order->get_shipping_address_1();
            $shippingCity = $order->get_shipping_city();
            $shippingAddressName = $shippingAddress1.', '.$shippingCity;
            if($shippingAddressName === ', '){
                $shippingAddressName = '';
            }
        }
    }
    return $shippingAddressName;
}