<?php

namespace Waboot\inc\core;

function defaultShippingAddressNameIsMandatory(): bool {
    return apply_filters('wawoo/multiple_shipping_address/default_shipping_address_name_is_mandatory', true);
}

function mustDisplayDefaultShippingAddressName(): bool {
    return apply_filters('wawoo/multiple_shipping_address/must_display_default_shipping_address_name', true);
}

/**
 * Generate a name for a shipping address. Will return empty string if it can't generate anything.
 * @param array $addressData must contain "shipping_*" fields.
 * @return string
 */
function generateShippingAddressName(array $addressData): string {
    $shippingAddress1 = $addressData['shipping_address_1'] ?? '';
    $shippingCity = $addressData['shipping_city'] ?? '';
    $shippingAddressName = $shippingAddress1.', '.$shippingCity;
    if($shippingAddressName === ', '){
        return '';
    }
    return $shippingAddressName;
}

/**
 * Generate a name for the shipping address in the posted data
 * @param int $orderId
 * @param array $postedData
 * @return string
 */
function generateShippingAddressNameFromOrderAndPostedData(int $orderId, array $postedData): string {
    $shippingAddressName = generateShippingAddressName($postedData);
    if($shippingAddressName === ''){
        $order = wc_get_order($orderId);
        if($order){
            $shippingAddress1 = $order->get_shipping_address_1();
            $shippingCity = $order->get_shipping_city();
            $shippingAddressName = generateShippingAddressName(['shipping_address_1' => $shippingAddress1, 'shipping_city' => $shippingCity]);
        }
    }
    return $shippingAddressName;
}