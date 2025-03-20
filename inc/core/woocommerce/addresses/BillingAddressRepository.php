<?php

namespace Waboot\inc\core\woocommerce\addresses;

use Waboot\inc\core\woocommerce\Customer;

class BillingAddressRepository
{
    public const TABLE_NAME = 'wawoo_shipping_addresses';

    public function __construct()
    {}

    /**
     * @param Customer $customer
     * @return BillingAddress|null
     */
    public function findByCustomer(Customer $customer): ?BillingAddress
    {
        $fields = apply_filters('wawoo/multiple_addresses/billing_address_fields',[
            'firstName' => 'billing_first_name',
            'lastName' => 'billing_last_name',
            'email' => 'billing_email',
            'city' => 'billing_city',
            'state' => 'billing_state',
            'postCode' => 'billing_postcode',
            'country' => 'billing_country',
            'company' => 'billing_company',
            'address1' => 'billing_address_1',
            'address2' => 'billing_address_2',
            'phone' => 'billing_phone',
        ]);
        $mandatoryFields = apply_filters('wawoo/multiple_addresses/billing_address_mandatory_fields',[
            'lastName',
            'email',
            'city',
            'postCode',
            'country',
            'address1',
        ]);
        $billingData = [];
        foreach ($fields as $fieldName => $fieldKey) {
            $billingData[$fieldName] = get_user_meta($customer->getWcCustomer()->get_id(),$fieldKey,true);
        }
        foreach ($mandatoryFields as $fieldName) {
            if(empty($billingData[$fieldName])) {
                return null;
            }
        }
        $ba = new BillingAddress();
        $ba->setUserId($customer->getWcCustomer()->get_id());
        $ba->setFirstName($billingData['firstName']);
        $ba->setLastName($billingData['lastName']);
        $ba->setEmail($billingData['email']);
        $ba->setCity($billingData['city']);
        if(!empty($billingData['state'])) {
            $ba->setState($billingData['state']);
        }
        $ba->setPostCode($billingData['postCode']);
        $ba->setCountry($billingData['country']);
        $ba->setAddress1($billingData['address1']);
        if(!empty($billingData['company'])) {
            $ba->setCompany($billingData['company']);
        }
        if(!empty($billingData['address2'])) {
            $ba->setAddress2($billingData['address2']);
        }
        if(!empty($billingData['phone'])) {
            $ba->setPhone($billingData['phone']);
        }
        do_action('wawoo/multiple_addresses/billing_address_repository/find_by_customer', $ba, $customer);
        return $ba;
    }

    /**
     * @param BillingAddress $address
     * @param Customer $customer
     * @return void
     */
    public function save(BillingAddress $address, Customer $customer): void
    {
        update_user_meta($customer->getWcCustomer()->get_id(), 'billing_first_name', $address->getFirstName());
        update_user_meta($customer->getWcCustomer()->get_id(), 'billing_last_name', $address->getLastName());
        update_user_meta($customer->getWcCustomer()->get_id(), 'billing_city', $address->getCity());
        update_user_meta($customer->getWcCustomer()->get_id(), 'billing_state', $address->getState());
        update_user_meta($customer->getWcCustomer()->get_id(), 'billing_postcode', $address->getPostCode());
        update_user_meta($customer->getWcCustomer()->get_id(), 'billing_country', $address->getCountry());
        update_user_meta($customer->getWcCustomer()->get_id(), 'billing_address_1', $address->getAddress1());
        update_user_meta($customer->getWcCustomer()->get_id(), 'billing_email', $address->getEmail());
        if($address->getAddress2()){
            update_user_meta($customer->getWcCustomer()->get_id(), 'billing_address_2', $address->getAddress2());
        }
        if($address->getCompany()){
            update_user_meta($customer->getWcCustomer()->get_id(), 'billing_company', $address->getCompany());
        }
        if($address->getPhone()){
            update_user_meta($customer->getWcCustomer()->get_id(), 'billing_phone', $address->getPhone());
        }
        do_action('wawoo/multiple_addresses/billing_address_repository/save', $address);
    }
}