<?php

namespace Waboot\addons\packages\invoicing;

/**
 * Tell the addons to forcefully display invoicing fields to the users
 *
 * @return bool
 */
function forceInvoiceDataFieldsOnCheckout(): bool {
    return FORCE_INVOICING;
}

/**
 * @return string|bool
 */
function forceCustomerType() {
    if(!\is_string(FORCE_CUSTOMER_TYPE)){
        return false;
    }
    return FORCE_CUSTOMER_TYPE;
}

/**
 * Checks if the invoicing fields must be presented forcefully to the user during checkout
 *
 * @return bool
 */
function mustForceDisplayInvoiceFieldsOnCheckout(): bool {
    $r = false;
    if(is_user_logged_in() && !WC()->customer instanceof \WC_Customer){
        return $r;
    }
    return forceInvoiceDataFieldsOnCheckout() || WC()->customer->get_meta(FIELD_REQUEST_INVOICE);
}

function getCustomerTypeLabel($slug): string {
    switch($slug){
        case "individual":
            return _x("Privato","WC Field",'waboot');
            break;
        case "company":
            return _x("Società","WC Field",'waboot');
            break;
    }
    return '';
}

/**
 * Gets the shop billing country
 *
 * @return string
 */
function getShopBillingCountry(): string {
    $bc = apply_filters('wb-woo-eut/default_shop_billing_country','IT');
    if(!\is_string($bc)){
        $bc = 'IT';
    }
    return $bc;
}

/**
 * @param $field_key
 * @return bool
 */
function isFillableField($field_key): bool {
    return \in_array($field_key,[
        FIELD_VAT,
        FIELD_FISCAL_CODE,
        //self::FIELD_UNIQUE_CODE,
        //self::FIELD_PEC
    ], true);
}

/**
 * Adds custom data to WC Customer object
 *
 * @param $data
 */
function injectCustomerData($data){
    foreach($data as $key => $value){
        WC()->customer->$key = $value;
    }
}