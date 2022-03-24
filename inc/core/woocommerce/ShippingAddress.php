<?php

namespace Waboot\inc\core\woocommerce;

class ShippingAddress extends AbstractCustomerAddress
{
    /**
     * @param Customer $customer
     * @return ShippingAddress
     */
    public static function fromCustomer(Customer $customer): ShippingAddress
    {
        $firstName = get_user_meta($customer->getWcCustomer()->get_id(),'shipping_first_name',true);
        $lastName = get_user_meta($customer->getWcCustomer()->get_id(),'shipping_last_name',true);
        $city = get_user_meta($customer->getWcCustomer()->get_id(),'shipping_city',true);
        $state = get_user_meta($customer->getWcCustomer()->get_id(),'shipping_state',true);
        $postcode = get_user_meta($customer->getWcCustomer()->get_id(),'shipping_postcode',true);
        $country = get_user_meta($customer->getWcCustomer()->get_id(),'shipping_country',true);
        $company = get_user_meta($customer->getWcCustomer()->get_id(),'shipping_company',true);
        $address1 = get_user_meta($customer->getWcCustomer()->get_id(),'shipping_address_1',true);
        $address2 = get_user_meta($customer->getWcCustomer()->get_id(),'shipping_address_2',true);
        $phone = get_user_meta($customer->getWcCustomer()->get_id(),'shipping_phone',true);
        $sa = new self();
        $sa->setFirstName($firstName);
        $sa->setLastName($lastName);
        $sa->setCity($city);
        $sa->setState($state);
        $sa->setPostCode($postcode);
        $sa->setCountry($country);
        $sa->setCompany($company);
        $sa->setAddress1($address1);
        $sa->setAddress2($address2);
        $sa->setPhone($phone);
        return $sa;
    }
}