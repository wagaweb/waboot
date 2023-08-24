<?php

namespace Waboot\addons\packages\checkout;

use Waboot\inc\core\utils\Utilities;
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
    try{
        $customerData = fetchCustomerData(get_current_user_id());
        $customerData['is_logged_in'] = true;
        wp_send_json_success($customerData);
    }catch (\Exception | \Throwable $e){
        wp_send_json_error([
            'error' => '[retrieve_user] Cannot retrieve user: '.$e->getMessage()
        ]);
    }
});

WordPress::addAjaxEndpoint('is_email_registered', static function(){
    $email = $_POST['email'] ?? false;
    if(!$email){
        wp_send_json_error(['error' => '[is_email_registered] Invalid email: '.$email]);
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

WordPress::addAjaxEndpoint('signin_user_by_email_and_password', static function(){
    $email = $_POST['email'] ?? false;
    if(!$email){
        wp_send_json_error(['error' => '[signin_user_by_email_and_password] Invalid email: '.$email]);
    }
    $password = $_POST['password'] ?? false;
    if($password === false || (\is_string($password) && $password === '')){
        wp_send_json_error(['error' => '[signin_user_by_email_and_password] Invalid password provided']);
    }
    $email = sanitize_email($email);
    $user = get_user_by('email',$email);
    if($user instanceof \WP_User){
        $r = Utilities::signinByCredentials($user->user_login,$password);
        if(\is_wp_error($r)){
            wp_send_json_error(['error' => '[signin_user_by_email_and_password] Unable signin user: '.$r->get_error_message()]);
        }
        try{
            $customerData = fetchCustomerData($user->ID);
            $customerData['is_logged_in'] = true;
            wp_send_json_success($customerData);
        }catch (\Exception | \Throwable $e){
            wp_send_json_error([
                'error' => '[retrieve_user] Cannot retrieve user: '.$e->getMessage()
            ]);
        }
        wp_send_json_success();
    }else{
        wp_send_json_error(['error' => '[signin_user_by_email_and_password] Unable to initialize user with email: '.$email]);
    }
});

/**
 * @param int $userId
 * @return array[]
 * @throws \Exception
 */
function fetchCustomerData(int $userId): array {
    $customer = new \WC_Customer($userId);
    return [
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
    ];
}