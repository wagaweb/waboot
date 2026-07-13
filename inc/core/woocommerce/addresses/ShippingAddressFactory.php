<?php

namespace Waboot\inc\core\woocommerce\addresses;

use function Waboot\inc\getOrderMeta;

class ShippingAddressFactory
{
    public function __construct()
    {}

    public function createFromDBRecord(\stdClass $record): ShippingAddress
    {
        $sa = new ShippingAddress();
        $sa->setId($record->id);
        $sa->setUserId($record->user_id);
        $sa->setName($record->name);
        $sa->setFirstName($record->first_name);
        $sa->setLastName($record->last_name);
        $sa->setCity($record->city);
        if($record->state){
            $sa->setState($record->state);
        }
        $sa->setPostCode($record->postcode);
        $sa->setCountry($record->country);
        if($record->company){
            $sa->setCompany($record->company);
        }
        $sa->setAddress1($record->address_1);
        if($record->address_2){
            $sa->setAddress2($record->address_2);
        }
        if($record->phone){
            $sa->setPhone($record->phone);
        }
        do_action('wawoo/multiple_addresses/shipping_address_repository/create_from_record', $sa, $record);
        return $sa;
    }

    /**
     * @param string|null $shippingId
     * @return ShippingAddress
     * @throws ShippingAddressFactoryException
     */
    public function createFromPostedData(string $shippingId = null): ShippingAddress
    {
        if(!$shippingId){
            $shippingId = sanitize_text_field($_POST['shipping_id']);
        }
        if(!$shippingId){
            throw new ShippingAddressFactoryException('createFromPostedData(): Invalid shipping id');
        }
        $sa = new ShippingAddress();
        $sa->setName($shippingId);
        $sa->setUserId(get_current_user_id());
        $sa->setFirstName(sanitize_text_field($_POST['shipping_first_name']));
        $sa->setLastName(sanitize_text_field($_POST['shipping_last_name']));
        $sa->setAddress1(sanitize_text_field($_POST['shipping_address_1']));
        $sa->setAddress2(sanitize_text_field($_POST['shipping_address_2']));
        $sa->setCity(sanitize_text_field($_POST['shipping_city']));
        $sa->setState(sanitize_text_field($_POST['shipping_state']));
        $sa->setCountry(sanitize_text_field($_POST['shipping_country']));
        $sa->setPostcode(sanitize_text_field($_POST['shipping_postcode']));
        do_action('wawoo/multiple_addresses/shipping_address_repository/create_from_posted_data', $sa);
        return $sa;
    }

    /**
     * @param \WC_Order $order
     * @return ShippingAddress|null
     */
    public function createFromOrder(\WC_Order $order): ?ShippingAddress
    {
        $userId = $order->get_user_id();
        if(!$userId) {
            return null;
        }
        $name = getOrderMeta($order, 'shipping_id');
        $addressData = [
            'userId' => $userId,
            'firstName' => $order->get_shipping_first_name(),
            'lastName' => $order->get_shipping_last_name(),
            'country' => $order->get_shipping_country(),
            'state' => $order->get_shipping_state(),
            'postcode' => $order->get_shipping_postcode(),
            'address1' => $order->get_shipping_address_1(),
            'address2' => $order->get_shipping_address_2(),
            'city' => $order->get_shipping_city(),
            'company' => $order->get_shipping_company(),
            'phone' => $order->get_shipping_phone(),
        ];
        $sa = new ShippingAddress();
        if(\is_string($name) && $name !== '') {
            $sa->setName($name);
        }
        $sa->setUserId($addressData['userId']);
        $sa->setFirstName($addressData['firstName']);
        $sa->setLastName($addressData['lastName']);
        $sa->setCountry($addressData['country']);
        $sa->setState($addressData['state']);
        $sa->setPostcode($addressData['postcode']);
        $sa->setAddress1($addressData['address1']);
        $sa->setAddress2($addressData['address2']);
        $sa->setCity($addressData['city']);
        $sa->setCompany($addressData['company']);
        $sa->setPhone($addressData['phone']);
        do_action('wawoo/multiple_addresses/create_from_order',$sa,$order);
        return $sa;
    }
}