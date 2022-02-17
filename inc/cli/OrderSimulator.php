<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\DB;
use Waboot\inc\core\DBException;
use Waboot\inc\core\DBUnavailableDependencyException;

class OrderSimulator extends AbstractCommand
{
    /**
     * @var string
     */
    protected $logDirName = 'simulate-orders';
    /**
     * @var string
     */
    protected $logFileName = 'simulate-orders';
    /**
     * @var string
     */
    protected $sourceFilePath;
    /**
     * @var array
     */
    protected $simulationData;
    /**
     * @var DB
     */
    protected $dbConnection;

    /**
     * Simulate orders based on a source file
     *
     * ## OPTIONS
     *
     * [--file]
     * : The name of the person to greet.
     *
     * [--delete]
     * : Delete all simulated orders
     *
     * [--dry-run]
     * : Do not do anything with the simulated order
     *
     * ## EXAMPLES
     *
     *      wp wawoo:simulate-orders path/to/source/file.json
     */
    public function __invoke($args, $assoc_args)
    {
        try{
            $this->dbConnection = DB::getInstance();
            if(isset($assoc_args['delete'])){
                $this->log('Deleting simulated orders...');
                $this->deleteSimulatedOrders();
                $this->success('Operation completed');
                return 0;
            }
            if(!isset($assoc_args['file'])){
                throw new \RuntimeException('No file specified');
            }
            $sourceFile = $assoc_args['file'];
            if(!\is_file($sourceFile)){
                $sourceFile = WP_CONTENT_DIR.'/'.$sourceFile;
            }
            if(!is_file($sourceFile)){
                throw new \RuntimeException('Invalid source file: '.$sourceFile);
            }
            $this->sourceFilePath = $sourceFile;
            if(!defined('JSON_THROW_ON_ERROR')) {
                define('JSON_THROW_ON_ERROR',4194304);
            }
            $sourceFileContent = file_get_contents($sourceFile);
            if($sourceFileContent === false){
                throw new \RuntimeException('Unable to read: '.$sourceFile);
            }
            $simulationData = json_decode($sourceFileContent,true,512,JSON_THROW_ON_ERROR);
            $this->simulationData = $simulationData;
            $this->simulateOrders();
            $this->success('Operation completed');
            return 0;
        }catch (\JsonException | DBException | DBUnavailableDependencyException | \RuntimeException $e){
            $this->error($e->getMessage(),false);
            return 1;
        }
    }

    /**
     * @throws DBException
     */
    private function deleteSimulatedOrders()
    {
        $r = $this->dbConnection->getQueryBuilder()::table('postmeta')
            ->select('post_id')
            ->where('meta_key','_simulation')
            ->where('meta_value','1')
            ->get()->toArray();
        if(!\is_array($r) && count($r) <= 0){
            return;
        }
        $idsToDelete = wp_list_pluck($r,'post_id');
        foreach ($idsToDelete as $idToDelete){
            $this->log('- Deleting order #'.$idToDelete);
            wp_delete_post($idToDelete);
            try{
                $this->dbConnection->getQueryBuilder()::table('postmeta')->where('post_id',$idToDelete)->delete();
            }catch (DBException $e){
                continue;
            }
        }
    }

    /**
     * @see set_data_from_cart() in class-wc-checkout.php
     */
    private function simulateOrders()
    {
        $carts = $this->simulationData['carts'] ?? false;
        if(!$carts){
            throw new \RuntimeException('No "carts" data in selected file');
        }
        $generatedOrderIds = [];
        foreach ($carts as $k => $cart){
            try {
                $this->log('Parsing cart #'.$cart);
                $orderId = $this->generateOrderByCart($cart);
                $generatedOrderIds[] = $orderId;
                $this->log('- Generated order #'.$orderId);
            }catch (\RuntimeException | \Exception $e){
                continue;
            }
        }
        $this->log('Generated orders: '.implode(',',$generatedOrderIds));
    }

    /**
     * @param array $cartData
     * @throws \Exception
     * @return int
     */
    private function generateOrderByCart(array $cartData): int
    {
        //Init the cart
        $wcCart = new \WC_Cart();
        WC()->cart = $wcCart;
        WC()->cart->empty_cart();
        //Parsing items
        $items = $cartData['items'] ?? [];
        if(!\is_array($items) || count($items) === 0){
            throw new \RuntimeException('No items in cart');
        }
        $couponsToApply = [];
        //-- Parsing items
        foreach ($items as $itemData){
            try{
                $simulatorOrderItem = new OrderSimulatorCartItem($itemData);
                $this->log('-- Parsing item with code: '.$simulatorOrderItem->getCode());
                if($simulatorOrderItem->isCoupon()){
                    $this->log('--- Its a coupon: save for later');
                    $couponsToApply[] = $simulatorOrderItem;
                    continue;
                }
                $simulatorOrderItem->addToCart($wcCart);
                $this->log('--- Added to cart');
            }catch (OrderSimulatorCartItemException | \Exception $e){
                $this->log('--- Error: '.$e->getMessage());
                continue;
            }
        }
        //-- Applying coupons
        foreach ($couponsToApply as $simulatorOrderItem){
            $this->log('-- Applying coupon with code: '.$simulatorOrderItem->getCode());
            try{
                $simulatorOrderItem->addToCart($wcCart);
            }catch (OrderSimulatorCartItemException | \Exception $e){
                $this->log('--- Error: '.$e->getMessage());
                continue;
            }
        }
        //Create order
        $checkout = \WC_Checkout::instance();
        if(!$checkout instanceof \WC_Checkout){
            throw new \RuntimeException('Unable to get the Checkout instance');
        }
        $availableGateways = WC()->payment_gateways->get_available_payment_gateways();
        $paymentMethod = $cartData['payment_method'] ?? array_keys($availableGateways)[0];
        if(!array_key_exists($paymentMethod, $availableGateways)){
            throw new \RuntimeException('Invalid payment method');
        }
        $customerId = $cartData['customer_id'] ?? 1;
        $customer = new \WC_Customer($customerId);
        $customerData = $cartData['customer'] ?? [];
        $data = [
            'billing_first_name' => $customer->get_billing_first_name(),
            'billing_last_name' => $customer->get_billing_last_name(),
            'billing_address_1' => $customer->get_billing_address_1(),
            'billing_address_2' => $customer->get_billing_address_2(),
            'billing_city' => $customer->get_city(),
            'billing_state' => $customer->get_state(),
            'billing_postcode' => $customer->get_postcode(),
            'billing_country' => $customer->get_country(),
            'billing_email' => $customer->get_billing_email(),
            'billing_phone' => $customer->get_billing_phone(),
            'shipping_first_name' => $customer->get_shipping_first_name(),
            'shipping_last_name' => $customer->get_shipping_last_name(),
            'shipping_address_1' => $customer->get_shipping_address_1(),
            'shipping_address_2' => $customer->get_shipping_address_2(),
            'shipping_city' => $customer->get_shipping_city(),
            'shipping_state' => $customer->get_shipping_state(),
            'shipping_postcode' => $customer->get_shipping_postcode(),
            'shipping_country' => $customer->get_shipping_country(),
            'payment_method' => $paymentMethod
        ];
        foreach ($data as $fieldName => $fieldValue){
            if($fieldValue === '' && isset($customerData[$fieldName]) && $customerData[$fieldName] !== ''){
                $data[$fieldName] = $customerData[$fieldName];
            }
        }
        //@see: create_order() in class-wc-checkout.php;
        $cart_hash = WC()->cart->get_cart_hash();
        $order = new \WC_Order();
        //COPY-PASTE: Begin
        $fields_prefix = array(
            'shipping' => true,
            'billing'  => true,
        );
        $shipping_fields = array(
            'shipping_method' => true,
            'shipping_total'  => true,
            'shipping_tax'    => true,
        );
        foreach ( $data as $key => $value ) {
            if ( is_callable( array( $order, "set_{$key}" ) ) ) {
                $order->{"set_{$key}"}( $value );
                // Store custom fields prefixed with wither shipping_ or billing_. This is for backwards compatibility with 2.6.x.
            } elseif ( isset( $fields_prefix[ current( explode( '_', $key ) ) ] ) ) {
                if ( ! isset( $shipping_fields[ $key ] ) ) {
                    $order->update_meta_data( '_' . $key, $value );
                }
            }
        }
        //COPY-PASTE: END
        $order->hold_applied_coupons( $data['billing_email'] );
        $order->set_created_via( 'simulator' );
        $order->set_cart_hash( $cart_hash );
        $order->set_customer_id( $customerId );
        $order->set_currency( get_woocommerce_currency() );
        $order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
        $order->set_customer_ip_address( \WC_Geolocation::get_ip_address() );
        $order->set_customer_user_agent( wc_get_user_agent() );
        $order->set_customer_note( '' );
        $order->set_payment_method( isset( $availableGateways[ $data['payment_method'] ] ) ? $availableGateways[ $data['payment_method'] ] : $data['payment_method'] );
        $checkout->set_data_from_cart( $order );
        if(!$this->isDryRun()){
            $orderId = $order->save();
            if(!\is_int($orderId) || $orderId <= 0){
                throw new \RuntimeException('Unable to create order');
            }
            update_post_meta($orderId,'_simulation','1');
            return $orderId;
        }
        return 0;
    }
}