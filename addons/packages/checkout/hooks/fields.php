<?php

namespace Waboot\addons\packages\checkout\hooks;

/*
 * FIELDS DEFAULT PRIORITIES
 * 'first_name' - 10
 * 'last_name' - 20
 * 'company' - 30
 * 'country' - 40
 * 'address_1' - 50
 * 'address_2' - 60
 * 'postcode' - 65
 * 'city' - 70
 * 'state' - 80
 * 'phone' - 100
 * 'email' - 110
 */

/*
 * Force visibility of company field.
 * Without this, the field is being "unset" here: wp-content/plugins/woocommerce/includes/class-wc-countries.php -> get_default_address_fields()
 */

use function Waboot\addons\packages\checkout\getCustomerCustomBillingFields;

add_filter('pre_option_'.'woocommerce_checkout_company_field', static function ($value, string $option, $defaultValue) {
    return 'optional';
},10, 3);

/*
 * Force visibility of address_2 field
 */
add_filter('pre_option_'.'woocommerce_checkout_address_2_field', static function ($value, string $option, $defaultValue) {
    return 'optional';
},10, 3);

/*
 * Force visibility of phone field
 */
add_filter('pre_option_'.'woocommerce_checkout_phone_field', static function ($value, string $option, $defaultValue) {
    return 'optional';
},10, 3);

/*
 * Adds custom default billing fields
 */
add_filter('wawoo/addons/checkout/customer_custom_billing_fields', static function (array $fields) {
    $fields['billing_birthday'] = [
        'label' => __('Birthday', LANG_TEXTDOMAIN),
        'type' => 'date',
        'priority' => 21
    ];
    $fields['billing_fiscal_code'] = [
        'label' => __('Fiscal code', LANG_TEXTDOMAIN),
        'type' => 'text',
        'priority' => 22
    ];
    $fields['billing_vat_number'] = [
        'label' => __('Vat Number', LANG_TEXTDOMAIN),
        'type' => 'text',
        'priority' => 31
    ];
    $fields['billing_sdi_pec'] = [
        'label' => __('SDI\\PEC', LANG_TEXTDOMAIN),
        'type' => 'text',
        'priority' => 32
    ];
    return $fields;
});

/*
 * Adding custom billing fields
 * BEWARE! "Invoicing" addons add these fields too. We still have to choose what to do about it.
 * @see: woocommerce_form_field()
 */
add_filter('woocommerce_billing_fields', static function(array $addressFields, $country){
    $addressFields['billing_customer_type'] = [
        'label' => __('Customer Type', LANG_TEXTDOMAIN),
        'required' => true,
        'class' => 'form-row-wide',
        'type' => 'radio',
        'options' => [
            'private' => __('Private', LANG_TEXTDOMAIN),
            'company' => __('Company', LANG_TEXTDOMAIN),
        ],
        'default' => 'private',
        'priority' => 9
    ];
    $customBillingFields = getCustomerCustomBillingFields();
    if(!empty($customBillingFields)){
        $addressFields = array_merge($addressFields, $customBillingFields);
    }
    return $addressFields;
}, 10, 2);

/*
 * Saving custom fields to order
 */
add_action('woocommerce_checkout_update_order_meta', static function(int $orderId, array $postedData){
    $wcOrder = wc_get_order($orderId);
    if(!$wcOrder instanceof \WC_Order){
        return;
    }
    if(isset($_POST['billing_customer_type'])){
        $wcOrder->update_meta_data('_billing_customer_type', sanitize_text_field($_POST['billing_customer_type']));
    }
    $customBillingFields = getCustomerCustomBillingFields();
    if(!empty($customBillingFields)){
        foreach ($customBillingFields as $customBillingFieldKey => $customBillingFieldData) {
            $wcOrder->update_meta_data('_'.$customBillingFieldKey, sanitize_text_field($_POST[$customBillingFieldKey]));
        }
    }
    $wcOrder->save_meta_data();
    do_action('wawoo/addons/checkout/update_order_custom_billing_fields', $wcOrder, $postedData);
}, 11, 2);

/*
 * Save custom billing fields to customer
 */
add_action('woocommerce_checkout_update_user_meta', function($customerId, $data){
    if(isset($_POST['billing_customer_type'])){
        update_user_meta($customerId, 'billing_customer_type', sanitize_text_field($_POST['billing_customer_type']));
    }
    $customBillingFields = getCustomerCustomBillingFields();
    if(!empty($customBillingFields)){
        foreach ($customBillingFields as $customBillingFieldKey => $customBillingFieldData) {
            update_user_meta($customerId,$customBillingFieldKey, sanitize_text_field($_POST[$customBillingFieldKey]));
        }
    }
    do_action('wawoo/addons/checkout/update_customer_custom_billing_fields', $customerId, $data);
},11,2);

/*
 * Remove fields from WooCommerce checkout page
 */
//add_filter( 'woocommerce_checkout_fields' , function( $fields ) {
//    // remove billing fields
//    unset($fields['billing']['billing_address_2']);
//    return $fields;
//},20,1);

/**
 *  Move / ReOrder Fields @ Checkout Page, WooCommerce version 3.0+
 */
//add_filter( 'woocommerce_default_address_fields', function($fields) {
//    $fields['company']['priority'] = 120;
//    return $fields;
//},20,1);