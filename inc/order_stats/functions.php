<?php

namespace Waboot\inc\order_stats;

use Illuminate\Database\Schema\Blueprint;
use Waboot\inc\core\DBException;
use Waboot\inc\core\facades\Query;
use function Waboot\inc\core\helpers\logException;
use function Waboot\inc\core\helpers\Waboot;

function getOrderStatsTableName(): string {
    return apply_filters('waboot/order_stats/table/table_name', 'orders_stats');
}

/**
 * @return void
 * @throws DBException|OrderStatsException
 */
function createOrderStatsTable(): void {
    $statsTableName = getOrderStatsTableName();
    if(Waboot()->DB()->tableExists($statsTableName)){
        throw new OrderStatsException('Table '.$statsTableName.' already exists.');
    }
    $taxonomiesColumns = getTaxonomiesForStats();
    Waboot()->DB()->getSchemaBuilder()->create($statsTableName, function (Blueprint $table) use($taxonomiesColumns) {
        $table->id();
        $table->integer('order_id');
        $table->string('product_id');
        $table->string('product_sku');
        $table->string('product_name');
        $table->string('product_type');
        $table->dateTimeTz('order_date_created')->nullable();
        $table->dateTimeTz('order_date_completed')->nullable();
        $table->string('order_status')->nullable();
        $table->integer('num_items_sold')->default(0);
        $table->integer('num_items_refunded')->default(0);
        $table->float('total')->default(0);
        $table->float('total_tax')->default(0);
        $table->float('total_refunded')->default(0);
        $table->string('shipping_country');
        $table->string('payment_method');
        foreach ($taxonomiesColumns as $taxonomy){
            $table->string($taxonomy)->default('');
            do_action('wawoo/order_stats/table/taxonomy_cols/tax',$table,$taxonomy);
        }
        do_action('wawoo/order_stats/table',$table);
    });
}

/**
 * @return array
 */
function getOrderStatusForStats(): array {
    $allowedOrderStatuses = array_keys(wc_get_order_statuses());
    return apply_filters('wawoo/order_stats/allowed_order_statuses',$allowedOrderStatuses);
}

/**
 * @return array
 */
function getTaxonomiesForStats(): array {
    static $taxonomiesForStats;
    if(isset($taxonomiesForStats)){
        return $taxonomiesForStats;
    }
    $allTaxonomies = get_object_taxonomies('product');
    $taxonomiesForStats = array_diff($allTaxonomies, ['product_type']);
    $taxonomiesForStats = apply_filters('wawoo/order_stats/selected_taxonomies',$taxonomiesForStats);
    return $taxonomiesForStats;
}

/**
 * @param int $orderId
 * @return array
 * @throws OrderStatsException
 */
function generateOrderStatRows(int $orderId): array {
    $rows = [];
    $order = wc_get_order($orderId);
    if(!$order instanceof \WC_Order){
        throw new OrderStatsException('Order ID #'.$orderId.' is invalid');
    }
    $items = $order->get_items(['line_item','shipping']);
    if(!\is_array($items)){
        throw new OrderStatsException('Unable to get items from order ID #'.$orderId);
    }
    foreach($items as $item){
        $row = [];
        if(!$item instanceof \WC_Order_Item_Product){
            continue;
        }
        $productId = $item->get_product_id();
        if(!\is_int($productId) || $productId === 0){
            // - WARNING: Item #$item->get_id() is associated with a non existent product;
            continue;
        }
        $product = $item->get_product();
        if(!$product instanceof \WC_Product){
            // - WARNING: Item #$item->get_id() is associated with a non existent product');
            continue;
        }
        $row['order_id'] = $item->get_order_id();
        $row['product_id'] = $productId;
        $row['product_sku'] = $product->get_sku();
        $row['product_name'] = $product->get_title();
        $row['product_type'] = $product->get_type();
        $orderDateCompleted = $item->get_order()->get_date_completed();
        $orderDateCreated = $item->get_order()->get_date_created();
        $orderStatus = $item->get_order()->get_status();
        if($orderDateCreated){
            $row['order_date_created'] = $orderDateCreated;
        }
        if($orderDateCompleted){
            $row['order_date_completed'] = $orderDateCompleted;
        }
        if(\is_string($orderStatus) && !empty($orderStatus)){
            $row['order_status'] = $orderStatus;
        }
        $row['num_items_sold'] = (int) $item->get_quantity();
        $row['total'] = (float) $item->get_total(); //This is the price of the item times the quantity excluding taxes after coupon discounts.
        $row['total_tax'] = (float) $item->get_total_tax();
        // REFUNDS: BEGIN
        $order = wc_get_order($item->get_order_id());
        $refundedQty = $order->get_qty_refunded_for_item($item->get_id());
        $refundedTotal = $order->get_total_refunded_for_item($item->get_id());
        $row['num_items_refunded'] = (int) $refundedQty * -1;
        $row['total_refunded'] = (float) $refundedTotal;
        // REFUNDS: END
        $row['shipping_country'] = $order->get_shipping_country();
        $row['payment_method'] = getPaymentMethodTitleForOrderStats($order->get_payment_method());
        $allowedTaxonomies = getTaxonomiesForStats();
        foreach ($allowedTaxonomies as $taxonomy){
            $skipTaxonomy = apply_filters('wawoo/order_stats/row/skip_taxonomy',false,$taxonomy,$productId,$item);
            if($skipTaxonomy){
                $row[$taxonomy] = '';
                continue;
            }
            $terms = wp_get_object_terms($productId,$taxonomy);
            $terms = apply_filters('wawoo/order_stats/row/parse_terms',$terms,$productId,$taxonomy,$item);
            if(\is_array($terms) && count($terms) > 0){
                $firstTerm = $terms[0];
                if($firstTerm instanceof \WP_Term){
                    $row[$taxonomy] = htmlspecialchars_decode($firstTerm->name);
                }
            }
            $row = apply_filters('wawoo/order_stats/row/parse_taxonomy',$row,$taxonomy,$terms,$productId,$item);
        }
        $rows[$item->get_id()] = apply_filters('wawoo/order_stats/row',$row,$productId,$item);
    }
    return $rows;
}

/**
 * @param int $productId
 * @param int $orderId
 * @return array|null
 */
function getOrderStatRow(int $productId, int $orderId): ?array {
    try {
        $existingRecord = Query::on(getOrderStatsTableName())->select('*')->where([
            ['product_id', '=', $productId],
            ['order_id', '=', $orderId]
        ])->get()->first();
        if($existingRecord){
            return json_decode(json_encode($existingRecord),true);
        }
        return null;
    } catch (DBException $e) {
        logException($e,'Waboot\inc\order_stats\getOrderStatRow()');
        return null;
    }
}

/**
 * @param $newRecord
 * @param $oldRecord
 * @return bool
 */
function orderStatsRowsAreDifferent($newRecord, $oldRecord): bool {
    if(isset($newRecord['id'])){
        unset($newRecord['id']);
    }
    if(isset($oldRecord['id'])){
        unset($oldRecord['id']);
    }
    $oldRecord = array_filter($oldRecord, function($key){
        return $key !== null && $key !== '';
    });
    $oldRecord = array_filter($oldRecord, function($value){
        return $value !== null && $value !== '';
    });
    $newRecord = array_map(function($value){
        if(is_float($value)){
            return round($value,2);
        }elseif ($value instanceof \WC_DateTime){
            return $value->format('Y-m-d H:i:s');
        }
        return $value;
    },$newRecord);
    $diff = array_diff($newRecord, $oldRecord);
    return !empty($diff);
}

/**
 * @param array $row
 * @return bool
 * @throws OrderStatsException
 */
function insertStatRow(array $row): bool {
    $productId = $row['product_id'];
    $orderId = $row['order_id'];
    $existingRow = getOrderStatRow($productId, $orderId);
    if($existingRow){
        if(!orderStatsRowsAreDifferent($row, $existingRow)){
            return false;
        }
    }
    try {
        Query::on(getOrderStatsTableName())->updateOrInsert(
            ['product_id' => $productId, 'order_id' => $orderId],
            $row
        );
        return true;
    } catch (\Exception|\Throwable $e) {
        logException($e,'Waboot\inc\order_stats\insertStatRow()');
        throw new OrderStatsException($e->getMessage());
    }
}

/**
 * @param string|null $tableName
 * @return array
 * @throws DBException
 */
function getOrderIdsAlreadyIntoStatsTable(string $tableName = null): array {
    if(!$tableName){
        $tableName = getOrderStatsTableName();
    }
    $r = Waboot()->DB()->getQueryBuilder()::table($tableName)
        ->select('order_id')
        ->get()
        ->toArray();
    return array_column($r, 'order_id');
}

/**
 * @param string $paymentMethod
 * @return string
 */
function getPaymentMethodTitleForOrderStats(string $paymentMethod): string {
    $paymentMethodTitle = '';
    switch ($paymentMethod){
        case '':
            $paymentMethodTitle = 'Altro';
            break;
        case 'bacs':
            $paymentMethodTitle = 'Bonifico';
            break;
        case 'cod':
            $paymentMethodTitle = 'Contrassegno';
            break;
        case 'setefi':
        case 'hipayenterprise_credit_card':
        case 'stripe':
            $paymentMethodTitle = 'Carta di Credito';
            break;
        case 'hipayenterprise_mybank':
            $paymentMethodTitle = 'Bonifico istantaneo';
            break;
        case 'hipayenterprise_paypal':
        case 'paypal':
        case 'ppcp-gateway':
            $paymentMethodTitle = 'PayPal';
            break;
        case 'scalapay_gateway':
        case 'wc-scalapay-payin3':
        case 'wc-scalapay-payin4':
            $paymentMethodTitle = 'Scalapay';
            break;
    }
    $paymentMethodTitle = apply_filters('wawoo/order_stats/row/payment_method_title',$paymentMethodTitle, $paymentMethod);
    if(!\is_string($paymentMethodTitle) || $paymentMethodTitle == ''){
        $paymentMethodTitle = $paymentMethod;
    }
    return $paymentMethodTitle;
}