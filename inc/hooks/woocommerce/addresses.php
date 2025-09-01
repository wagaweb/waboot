<?php

namespace Waboot\inc\woocommerce;

use Waboot\inc\core\DBException;
use Waboot\inc\core\woocommerce\addresses\ShippingAddressFactory;
use Waboot\inc\core\woocommerce\addresses\ShippingAddressFactoryException;
use Waboot\inc\core\woocommerce\addresses\ShippingAddressRepository;
use Waboot\inc\core\woocommerce\Customer;
use function Waboot\inc\core\defaultShippingAddressNameIsMandatory;
use function Waboot\inc\core\generateShippingAddressName;
use function Waboot\inc\core\helpers\logException;
use function Waboot\inc\core\mustDisplayDefaultShippingAddressName;

/*
 * Delete shippping addresses on user deletion
 */
add_action('deleted_user', function(int $userId, $reassign, \WP_User $user) {
    (new ShippingAddressRepository())->deleteByCustomerId($userId);
},10,3);

/*
 * Add shipping_id as user meta
 */
add_filter('woocommerce_customer_meta_fields', static function (array $fields) {
    $fields['shipping']['fields']['shipping_id'] = [
        'label' => __('Address name', LANG_TEXTDOMAIN),
        'description' => ''
    ];
    return $fields;
},10);

/*
 * Adding shipping_id as shipping_field
 */
add_filter('woocommerce_shipping_fields', static function(array $addressFields, $country){
    $addressFields['shipping_id'] = [
        'label' => __('Address name', LANG_TEXTDOMAIN),
        'type' => mustDisplayDefaultShippingAddressName() ? 'text' : 'hidden',
        'required' => defaultShippingAddressNameIsMandatory(),
        'class' => ['form-row-wide'],
        'priority' => 9
    ];
    return $addressFields;
}, 10, 2);

/*
 * Saving shipping_id to order
 */
add_action('woocommerce_checkout_update_order_meta', static function(int $orderId, array $postedData){
    if(!isset($_POST['shipping_id']) || empty($_POST['shipping_id'])){
        if(defaultShippingAddressNameIsMandatory()){
            return;
        }
        $shippingId = generateShippingAddressName($orderId, $postedData);
    }else{
        $shippingId = sanitize_text_field($_POST['shipping_id']);
    }
    if(!$shippingId){
        return;
    }
    $wcOrder = wc_get_order($orderId);
    if(!$wcOrder instanceof \WC_Order){
        return;
    }
    $wcOrder->update_meta_data('shipping_id', $shippingId);
    $wcOrder->save_meta_data();
}, 11, 2);

//add_action('woocommerce_checkout_update_user_meta', function($customer_id, $data){
//    return;
//}, 11, 2);

/*
 * Saving addresses to the user after order
 */
add_action('woocommerce_checkout_order_created', static function(\WC_Order $order){
    if(!get_current_user_id()){
        return;
    }
    if(!isset($_POST['shipping_id']) || empty($_POST['shipping_id'])){
        if(defaultShippingAddressNameIsMandatory()){
            return;
        }
        $shippingId = generateShippingAddressName($order->get_id(), $_POST);
    }else{
        $shippingId = sanitize_text_field($_POST['shipping_id']);
    }
    if(!$shippingId){
        return;
    }
    try{
        $customer = new Customer(get_current_user_id());
        $saRepo = $customer->getShippingAddressRepository();
        $existingAddress = $saRepo->findByNameAndCustomer($shippingId, $customer);
        if(!$existingAddress){
            // Save the new address
            $sa = (new ShippingAddressFactory())->createFromPostedData();
            $saRepo->save($sa);
            $saRepo->setCurrentToCustomer($sa, $customer);
        }
    }catch (DBException|ShippingAddressFactoryException $e){
        logException($e,'woocommerce_checkout_order_created',[],'wawoo-multiaddress-debug');
    }
},11);