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

/**
 * Retrieve plugin custom meta from an order
 *
 * @param $order_id
 *
 * @return array;
 */
function getCustomMetaFromOrder($order_id): array {
    $custom_meta = [];

    $custom_meta[FIELD_CUSTOMER_TYPE] = get_post_meta($order_id,FIELD_CUSTOMER_TYPE,true);
    $custom_meta[FIELD_VAT] = get_post_meta($order_id,FIELD_VAT,true);
    $custom_meta[FIELD_FISCAL_CODE] = get_post_meta($order_id,FIELD_FISCAL_CODE,true);
    $custom_meta[FIELD_PEC] = get_post_meta($order_id,FIELD_PEC,true);
    $custom_meta[FIELD_UNIQUE_CODE] = get_post_meta($order_id,FIELD_UNIQUE_CODE,true);
    $custom_meta[FIELD_REQUEST_INVOICE] = get_post_meta($order_id,FIELD_REQUEST_INVOICE,true);

    //todo: se non ci sono campi custom, settare la request incoice a false, se ci sono, settarla a true (per gli ordini già salvati)
    if($custom_meta[FIELD_VAT] !== "" || $custom_meta[FIELD_FISCAL_CODE] !== ""){
        if($custom_meta[FIELD_REQUEST_INVOICE] === ""){
            $custom_meta[FIELD_REQUEST_INVOICE] = true;
        }
    }

    $custom_meta = array_filter($custom_meta);

    return $custom_meta;
}