<?php

namespace Waboot\addons\packages\checkout\hooks;


namespace Waboot\addons\packages\checkout;

use Waboot\inc\core\utils\Utilities;
use Waboot\inc\core\woocommerce\addresses\ShippingAddress;
use Waboot\inc\core\woocommerce\Customer;

Utilities::addAjaxEndpoint('is_customer_logged_in', static function () {
    wp_send_json_success([
        'is_logged_in' => is_user_logged_in()
    ]);
});

Utilities::addAjaxEndpoint('fetch_store_states', static function () {
    $country = $_POST['country'] ?? false;
    if (!$country) {
        wp_send_json_error(['error' => '[fetch_store_states] Invalid country']);
    }
    $country = sanitize_text_field($country);
    $states = WC()->countries->get_states($country);
    $response = [];
    if (\is_array($states) && !empty($states)) {
        foreach ($states as $slug => $label) {
            $response[] = [
                'slug' => $slug,
                'label' => $label
            ];
        }
    }
    wp_send_json_success($response);
});

Utilities::addAjaxEndpoint('fetch_store_countries', static function () {
    try {
        $shippingCountries = [];
        foreach (WC()->countries->get_shipping_countries() as $slug => $label) {
            $shippingCountries[] = [
                'slug' => $slug,
                'label' => $label
            ];
        }
        /*$sellCountries = [];
        foreach (WC()->countries->get_allowed_countries() as $slug => $label){
            $sellCountries[] = [
                'slug' => $slug,
                'label' => $label
            ];
        }*/
        wp_send_json_success($shippingCountries);
    } catch (\Exception|\Throwable $e) {
        wp_send_json_error([
            'error' => '[fetch_store_countries] error: ' . $e->getMessage()
        ]);
    }
});

Utilities::addAjaxEndpoint('retrieve_user', static function () {
    if (!is_user_logged_in()) {
        wp_send_json_success([
            'is_logged_in' => false
        ]);
    }
    try {
        $customerData = fetchCustomerData(get_current_user_id());
        $customerData['id'] = get_current_user_id();
        $customerData['is_logged_in'] = true;
        wp_send_json_success($customerData);
    } catch (\Exception|\Throwable $e) {
        wp_send_json_error([
            'error' => '[retrieve_user] Cannot retrieve user: ' . $e->getMessage()
        ]);
    }
});

Utilities::addAjaxEndpoint('is_email_registered', static function () {
    $email = $_POST['email'] ?? false;
    if (!$email) {
        wp_send_json_error(['error' => '[is_email_registered] Invalid email: ' . $email]);
    }
    $email = sanitize_email($email);
    $user = get_user_by('email', $email);
    if ($user instanceof \WP_User) {
        wp_send_json_success([
            'is_email_registered' => true
        ]);
    } else {
        wp_send_json_success([
            'is_email_registered' => false
        ]);
    }
});

Utilities::addAjaxEndpoint('signin_user_by_email_and_password', static function () {
    $email = $_POST['email'] ?? false;
    if (!$email) {
        wp_send_json_error(['error' => 'Invalid email: ' . $email]);
    }
    $password = $_POST['password'] ?? false;
    if ($password === false || (\is_string($password) && $password === '')) {
        wp_send_json_error(['error' => 'Invalid password provided']);
    }
    $email = sanitize_email($email);
    $user = get_user_by('email', $email);
    if ($user instanceof \WP_User) {
        $r = Utilities::signinByCredentials($user->user_login, $password);
        if (\is_wp_error($r)) {
            $errorMessage = $r->get_error_message();
            $errorMessage = strip_tags($errorMessage);
            $errorMessage = preg_replace('/\.[^?]*\?/', '.', $errorMessage); // Strip "Password dimenticata?"
            wp_send_json_error(['error' => $errorMessage]);
        }
        try {
            $customerData = fetchCustomerData($user->ID);
            $customerData['is_logged_in'] = true;
            wp_send_json_success($customerData);
        } catch (\Exception|\Throwable $e) {
            wp_send_json_error([
                'error' => '[retrieve_user] Cannot retrieve user: ' . $e->getMessage()
            ]);
        }
        wp_send_json_success();
    } else {
        wp_send_json_error(['error' => 'Unable to initialize user with email: ' . $email]);
    }
});

Utilities::addAjaxEndpoint('retrieve_shipping_addresses', static function () {
    $userId = $_POST['user_id'] ?? false;
    $userId = sanitize_text_field($userId);
    if (!$userId) {
        wp_send_json_error(['error' => '[retrieve_shipping_addresses] Invalid user id: ' . $userId]);
    }
    $user = get_user_by('id', $userId);
    if ($user instanceof \WP_User) {
        $c = new Customer($userId);
        $addrs = $c->getShippingAddresses();
        wp_send_json_success(array_map(function (ShippingAddress $address) use($userId) {
            return apply_filters('wawoo/addons/checkout/retrieve_shipping_addresses/address', [
                'name' => $address->getName(),
                'firstName' => $address->getFirstName(),
                'first_name' => $address->getFirstName(), // Backward compatibility
                'lastName' => $address->getLastName(),
                'last_name' => $address->getLastName(), // Backward compatibility
                'country' => $address->getCountry(),
                'address1' => $address->getAddress1(),
                'address2' => $address->getAddress2(),
                'postcode' => $address->getPostcode(),
                'city' => $address->getCity(),
                'state' => $address->getState(),
                'notes' => '',
            ], $userId);
        }, $addrs));
    } else {
        wp_send_json_error(['error' => '[retrieve_shipping_addresses] Invalid user']);
    }
});

/**
 * @param int $userId
 * @return array[]
 * @throws \Exception
 */
function fetchCustomerData(int $userId): array {
    $customer = new Customer($userId);
    $customer->fetchCurrentShippingAddress();
    $shippingAddress = $customer->getCurrentShippingAddress();
    $billingAddress = $customer->getBillingAddress();
    $billingEmail = '';
    if($billingAddress) {
        $billingEmail = $billingAddress->getEmail();
    }
    if(empty($billingEmail)) {
        $billingEmail = $customer->getWcCustomer()->get_email();
    }
    $customerType = get_user_meta($userId, 'billing_customer_type', true);
    $company = $customer->getWcCustomer()->get_billing_company() ?? '';
    $customerData = [
        'profile_data' => [
            'email' => $billingEmail,
            'profileType' => $customerType,
            'company' => $company,
        ],
        'billing_data' => $billingAddress ? [
            'firstName' => $customer->getWcCustomer()->get_first_name(),
            'lastName' => $customer->getWcCustomer()->get_last_name(),
            'phone' => $customer->getWcCustomer()->get_billing_phone(),
            'country' => $billingAddress->getCountry(),
            'address1' => $billingAddress->getAddress1(),
            'address2' => $billingAddress->getAddress2(),
            'postcode' => $billingAddress->getPostcode(),
            'city' => $billingAddress->getCity(),
            'state' => $billingAddress->getState()
        ] : [
            'firstName' => '',
            'lastName' => '',
            'phone' => '',
            'country' => '',
            'address1' => '',
            'address2' => '',
            'postcode' => '',
            'city' => '',
            'state' => ''
        ],
        'shipping_data' => $shippingAddress ? [
            'id' => $shippingAddress->getId() ?? '',
            'name' => $shippingAddress->getName(),
            'firstName' => $shippingAddress->getFirstName(),
            'lastName' => $shippingAddress->getLastName(),
            'phone' => $shippingAddress->getPhone(),
            'country' => $shippingAddress->getCountry(),
            'address1' => $shippingAddress->getAddress1(),
            'address2' => $shippingAddress->getAddress2(),
            'postcode' => $shippingAddress->getPostcode(),
            'city' => $shippingAddress->getCity(),
            'state' => $shippingAddress->getState(),
            'notes' => ''
        ] : [
            'id' => '',
            'name' => '',
            'firstName' => '',
            'lastName' => '',
            'phone' => '',
            'country' => '',
            'address1' => '',
            'address2' => '',
            'postcode' => '',
            'city' => '',
            'state' => '',
            'notes' => ''
        ]
    ];
    $birthDay = get_user_meta($userId, 'billing_birthday', true);
    if($birthDay){
        $birthDayDate = date_create_from_format('Y-m-d', $birthDay);
        if($birthDayDate instanceof \DateTime){
            $birthDay = $birthDayDate->format('Y-m-d');
        }
    }
    if($birthDay){
        $customerData['profile_data']['birthday'] = $birthDay;
    }
    $fiscalCode = get_user_meta($userId, 'billing_fiscal_code', true);
    if($fiscalCode){
        $customerData['profile_data']['fiscalCode'] = $fiscalCode;
    }
    $vatNumber = get_user_meta($userId, 'billing_vat_number', true);
    if($vatNumber){
        $customerData['profile_data']['vatNumber'] = $vatNumber;
    }
    $sdiPec = get_user_meta($userId, 'billing_sdi_pec', true);
    if($sdiPec){
        $customerData['profile_data']['sdiPec'] = $sdiPec;
    }
    return apply_filters('wawoo/addons/checkout/fetchCustomerData/customer_data', $customerData, $userId);
}