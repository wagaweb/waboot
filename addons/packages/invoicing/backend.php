<?php

namespace Waboot\addons\packages\invoicing;

use Waboot\inc\core\mvc\HTMLView;
use function Waboot\addons\getAddonDirectory;

add_action("woocommerce_admin_order_data_after_billing_address", __NAMESPACE__.'\displayCustomMetaOnOrder', 10, 1);
add_action("manage_shop_order_posts_custom_column", __NAMESPACE__.'\displayCustomMetaOnOrderListing', 3, 2);
add_filter("woocommerce_email_customer_details_fields", __NAMESPACE__.'\addCustomMetaFieldsOnNewOrderEmail', 10, 3);

/**
 * Display plugin custom meta in order details
 *
 * @hooked 'woocommerce_admin_order_data_after_order_details'
 *
 * @param \WC_Order $order
 */
function displayCustomMetaOnOrder(\WC_Order $order): void {
    try{
        $custom_meta = getCustomMetaFromOrder($order->get_id());

        $v = new HTMLView(getAddonDirectory('invoicing')."/templates/order-custom-meta.php",false);

        //Order data:
        $billing_company = $order->get_billing_company();

        $v->display([
            'company_name' => $billing_company ?? "",
            'fiscal_code' => $custom_meta[FIELD_FISCAL_CODE] ?? "",
            'vat' => $custom_meta[FIELD_VAT] ?? "",
            'customer_type' => isset($custom_meta[FIELD_CUSTOMER_TYPE]) ? getCustomerTypeLabel($custom_meta[FIELD_CUSTOMER_TYPE]) : "",
            'unique_code' => $custom_meta[FIELD_UNIQUE_CODE] ?? "",
            'pec' => $custom_meta[FIELD_PEC] ?? "",
            'textdomain' => 'waboot'
        ]);
    }catch (\Exception $e){
        echo '';
    }
}

/**
 * @param string $column
 * @param int $postId
 * @hooked 'manage_shop_order_posts_custom_column'
 */
function displayCustomMetaOnOrderListing(string $column, int $postId){
    global $post, $woocommerce, $the_order;

    $order_id = $postId;

    if ( empty( $the_order ) || $order_id !== $post->ID ) {
        $the_order = wc_get_order( $post->ID );
    }

    if(!$the_order instanceof \WC_Order){
        return;
    }

    switch($column){
        case 'billing_address':
        case 'shipping_address':
            $custom_meta = getCustomMetaFromOrder($order_id);
            if(isset($custom_meta[FIELD_REQUEST_INVOICE]) && $custom_meta[FIELD_REQUEST_INVOICE]){
                try{
                    $v = new HTMLView(getAddonDirectory('invoicing')."/templates/order-custom-meta.php",false);

                    //Order data:
                    $billing_company = $the_order->billing_company;

                    $v->display([
                        'company_name' => $billing_company ?? "",
                        'fiscal_code' => $custom_meta[FIELD_FISCAL_CODE] ?? "",
                        'vat' => $custom_meta[FIELD_VAT] ?? "",
                        'customer_type' => isset($custom_meta[FIELD_CUSTOMER_TYPE]) ? getCustomerTypeLabel($custom_meta[FIELD_CUSTOMER_TYPE]) : "",
                        'textdomain' => 'waboot'
                    ]);
                }catch (\Exception $e){
                    echo '';
                }
            }
            break;
    }
}

/**
 * Adds the custom fields to admin new order email
 *
 * @param array $fields
 * @param bool $sent_to_admin
 * @param \WC_Order $order
 *
 * @hooked 'woocommerce_email_customer_details_fields'
 *
 * @return array
 */
function addCustomMetaFieldsOnNewOrderEmail(array $fields, bool $sent_to_admin, \WC_Order $order): array {
    if($sent_to_admin){
        $order_id = $order->get_id();
        $custom_meta = getCustomMetaFromOrder($order_id);
        if(isset($custom_meta[FIELD_REQUEST_INVOICE]) && $custom_meta[FIELD_REQUEST_INVOICE]){
            $fields['customer_type'] = [
                'label' => __('Customer Type', 'waboot'),
                'value' => $custom_meta[FIELD_CUSTOMER_TYPE]
            ];

            if(isset($custom_meta[FIELD_CUSTOMER_TYPE]) && $custom_meta[FIELD_CUSTOMER_TYPE] === "company"){
                $fields['company_name'] = [
                    'label' => __('Company name', 'waboot'),
                    'value' => $order->get_billing_company()
                ];
            }

            $fields['vat'] = [
                'label' => __('VAT','waboot'),
                'value' => $custom_meta[FIELD_VAT]
            ];

            $fields['fiscal_code'] = [
                'label' => __('Fiscal code','waboot'),
                'value' => $custom_meta[FIELD_FISCAL_CODE]
            ];
        }
    }
    return $fields;
}