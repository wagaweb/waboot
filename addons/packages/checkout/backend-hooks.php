<?php

namespace Waboot\addons\packages\checkout;

use Waboot\inc\core\utils\WordPress;

WordPress::addAjaxEndpoint('is_customer_logged_in', static function(){
    wp_send_json_success([
        'is_logged_in' => is_user_logged_in()
    ]);
});

WordPress::addAjaxEndpoint('retrieve_user', static function(){
    if(!is_user_logged_in()){
        wp_send_json_success([
            'is_logged_in' => false
        ]);
    }
    $customer = new \WC_Customer(get_current_user_id());
    if(!$customer instanceof \WC_Customer){
        wp_send_json_error([
            'error' => 'Cannot retrieve user with id: '.get_current_user_id()
        ]);
    }
    wp_send_json_success([
        'is_logged_in' => true,
        'profile_data' => [
            'email' => $customer->get_email(),
            'firstName' => $customer->get_first_name(),
            'lastName' => $customer->get_last_name(),
            'phone' => $customer->get_billing_phone(),
            'birthDay' => '',
        ],
        'shipping_data' => [
            'country' => $customer->get_shipping_country(),
            'address' => $customer->get_shipping_address_1(),
            'postcode' => $customer->get_shipping_postcode(),
            'city' => $customer->get_shipping_city(),
            'state' => $customer->get_shipping_state(),
            'notes' => '',
        ],
        'billing_data' => [
            'email' => $customer->get_billing_email(),
            'country' => $customer->get_billing_country(),
            'address' => $customer->get_billing_address_1(),
            'postcode' => $customer->get_billing_postcode(),
            'city' => $customer->get_billing_city(),
            'state' => $customer->get_billing_state(),
        ]
    ]);
});

WordPress::addAjaxEndpoint('is_email_registered', static function(){
    $email = $_POST['email'] ?? false;
    if(!$email){
        wp_send_json_error(['error' => 'invalid_email']);
    }
    $email = sanitize_email($email);
    $user = get_user_by('email',$email);
    if($user instanceof \WP_User){
        wp_send_json_success([
            'is_email_registered' => true
        ]);
    }else{
        wp_send_json_success([
            'is_email_registered' => false
        ]);
    }
});