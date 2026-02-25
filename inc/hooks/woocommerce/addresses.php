<?php

namespace Waboot\inc\woocommerce;

use Waboot\inc\core\DBException;
use Waboot\inc\core\woocommerce\addresses\AddressUserMetaKeys;
use Waboot\inc\core\woocommerce\addresses\ShippingAddressFactory;
use Waboot\inc\core\woocommerce\addresses\ShippingAddressFactoryException;
use Waboot\inc\core\woocommerce\addresses\ShippingAddressRepository;
use Waboot\inc\core\woocommerce\Customer;
use waboot\inc\core\woocommerce\CustomerException;
use waboot\inc\core\woocommerce\CustomerNotFoundException;
use function Waboot\inc\core\defaultShippingAddressNameIsMandatory;
use function Waboot\inc\core\generateShippingAddressName;
use function Waboot\inc\core\generateShippingAddressNameFromOrderAndPostedData;
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
    if(!mustDisplayDefaultShippingAddressName()){
        $addressFields['shipping_id']['label_class'] = ['hidden'];
    }
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
        $shippingId = generateShippingAddressNameFromOrderAndPostedData($orderId, $postedData);
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
        $shippingId = generateShippingAddressNameFromOrderAndPostedData($order->get_id(), $_POST);
    }else{
        $shippingId = sanitize_text_field($_POST['shipping_id']);
    }
    if(!$shippingId){
        return;
    }
    try{
        $customer = new Customer(get_current_user_id());
        $saRepo = $customer->getShippingAddressRepository();
        $sa = $saRepo->findByNameAndCustomer($shippingId, $customer);
        if(!$sa){
            // Save the new address
            $sa = (new ShippingAddressFactory())->createFromPostedData($shippingId);
            $addressId = $saRepo->save($sa);
            $saRepo->setCurrentToCustomer($sa, $customer);
        }else{
            $addressId = $sa->getId();
        }
        $order->update_meta_data('shipping_index', $addressId);
        $order->save_meta_data();
        do_action('wawoo/multiple_addresses/woocommerce_checkout_order_created/save_shipping_address_on_user',$customer,$sa,$order);
    }catch (DBException|ShippingAddressFactoryException|CustomerNotFoundException|CustomerException $e){
        logException($e,'woocommerce_checkout_order_created',[],'wawoo-multiaddress-debug');
    }
},11);

/*add_action('woocommerce_update_customer', static function(int $customerId, \WC_Customer $customer){
   xdebug_break();
},10,2);*/

/*
 * Output the numeric id of the current shipping address in my account edit address page
 */
add_action('woocommerce_after_edit_address_form_'.'shipping', static function(){
    if(!is_account_page()){
        return;
    }
    $customer = new Customer(get_current_user_id());
    $customer->fetchCurrentShippingAddress();
    $shippingAddress = $customer->getCurrentShippingAddress();
    if(!$shippingAddress){
        return;
    }
    if(!$shippingAddress->getId()){
        return;
    }
    ?>
    <input type="hidden" name="shipping_address_numeric_id" value="<?php echo $shippingAddress->getId(); ?>">
    <?php
});

/*
 * Saving address in my account edit address page
 */
add_action('woocommerce_customer_save_address', static function(int $customerId, string $addressType){
    if(!is_account_page()){
        return;
    }
    if($addressType !== 'shipping'){
        return;
    }
    try{
        if(defaultShippingAddressNameIsMandatory()){
            if(!isset($_POST['shipping_id']) || empty($_POST['shipping_id'])){
                return;
            }
            $shippingId = sanitize_text_field($_POST['shipping_id']);
        }else{
            if(isset($_POST['shipping_id']) && !empty($_POST['shipping_id'])){
                $shippingId = sanitize_text_field($_POST['shipping_id']);
            }else{
                $shippingId = generateShippingAddressName($_POST);
            }
        }
        if(!$shippingId){
            return;
        }
        $postedShippingAddress = (new ShippingAddressFactory())->createFromPostedData($shippingId);
        $customer = new Customer($customerId);
        $saRepo = $customer->getShippingAddressRepository();
        $existingAddress = null;
        if(isset($_POST['shipping_address_numeric_id'])){
            $shippingNumericId = sanitize_text_field($_POST['shipping_address_numeric_id']);
            if(!empty($shippingNumericId)){
                $existingAddress = $saRepo->findById((int) $shippingNumericId);
            }
        }
        if(!$existingAddress){
            $existingAddress = $saRepo->findByNameAndCustomer($postedShippingAddress->getName(), $customer);
        }
        if($existingAddress){
            $updatedData = [];
            foreach ($_POST as $key => $value) {
                if(!str_contains($key, 'shipping_')){
                    continue;
                }
                $parsedValue = sanitize_text_field($value);
                if($parsedValue){
                    $updatedData[$key] = $parsedValue;
                }
            }
            $existingAddress->updateFromData($updatedData);
            $saRepo->save($existingAddress);
            update_user_meta($customerId,AddressUserMetaKeys::currentShippingAddress->value, $existingAddress->getId());
        }else{
            $postedShippingAddress->setUserId($customerId);
            $saRepo->save($postedShippingAddress);
            update_user_meta($customerId,AddressUserMetaKeys::currentShippingAddress->value, $existingAddress->getId());
        }
    }catch (\Exception|\Throwable $e){
        logException($e,'woocommerce_customer_save_address',[],'wawoo-multiaddress-debug');
    }
},10,2);