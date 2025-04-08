<?php

namespace Waboot\inc\cli;

use Illuminate\Database\Schema\Blueprint;
use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\cli\CLIRuntimeException;
use Waboot\inc\core\DBException;
use Waboot\inc\core\facades\Query;
use function Waboot\inc\core\helpers\Waboot;

class GenerateOrderStatsTable extends AbstractCommand
{
    /**
     * @var string
     */
    protected $logDirName = 'export-order-stats-table';
    /**
     * @var string
     */
    protected $logFileName = 'export-order-stats-table';
    /**
     * @var array
     */
    protected array $selectedOrdersIds;
    /**
     * @var array
     */
    protected array $retrievedOrdersIds;
    /**
     * @var array
     */
    protected array $selectedTaxonomies;
    /**
     * @var string
     */
    protected string $statsTableName;
    /**
     * @var string
     */
    protected $timeZone = 'Europe/Rome';
    /**
     * @var array
     */
    private array $taxonomiesColumns;
    protected bool $mustRebuildTable;
    protected bool $skipExisting;
    protected bool $forceOverwrite;
    protected int $ordersPerPage;
    protected int $currentPage;

    public static function getCommandDescription(): array
    {
        $description = parent::getCommandDescription();
        $description['shortdesc'] = 'Generate a table with orders stats';
        $description['longdesc'] = '## EXAMPLES' . "\n\n" . 'wp wawoo:gen-stat-table';
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'table-name',
            'description' => 'Specifies the table name (default to: orders_stats)',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'taxonomies',
            'description' => 'Comma separated list of taxonomies to includes. If not provided, all product taxonomies will be included.',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'orders',
            'description' => 'Comma separated list of orders to use to generate the table',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'assoc',
            'name' => 'pagination',
            'description' => 'Number of orders to parse per iteration',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'flag',
            'name' => 'rebuild-table',
            'description' => 'Specifies whether to drop and recreate the table',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'flag',
            'name' => 'skip-existing',
            'description' => 'Specifies whether to skip existing entries without comparing them',
            'optional' => true,
        ];
        $description['synopsis'][] = [
            'type' => 'flag',
            'name' => 'force-overwrite',
            'description' => 'Specifies whether to force recreating existing entries',
            'optional' => true,
        ];
        return $description;
    }

    public function run(array $args, array $assoc_args): int
    {
        try{
            if(isset($assoc_args['orders'])){
                $selectedOrders = explode(',',$assoc_args['orders']);
                if(\is_array($selectedOrders)){
                    $this->selectedOrdersIds = $selectedOrders;
                }
            }
            if(isset($assoc_args['taxonomies'])){
                $selectedTaxonomies = explode(',',$assoc_args['taxonomies']);
                if(\is_array($selectedTaxonomies)){
                    $this->selectedTaxonomies = $selectedTaxonomies;
                }
            }
            $allTaxonomies = get_object_taxonomies('product');
            if(isset($this->selectedTaxonomies)){
                $this->taxonomiesColumns = array_filter($this->selectedTaxonomies, static function (string $taxonomy) use($allTaxonomies){
                    return \in_array($taxonomy,$allTaxonomies,true) && $taxonomy !== 'product_type';
                });
            }else{
                $this->taxonomiesColumns = array_filter($allTaxonomies, static function (string $taxonomy){
                    return $taxonomy !== 'product_type'; //exclude product_type
                });
                $this->taxonomiesColumns = apply_filters('wawoo/cli/gen-stat-table/taxonomies',$this->taxonomiesColumns);
            }
            if(isset($assoc_args['table-name']) && \is_string($assoc_args['table-name']) && $assoc_args['table-name'] !== ''){
                $tableTmpName = sanitize_title($assoc_args['table-name']);
                $this->statsTableName = $tableTmpName;
            }else{
                $this->statsTableName = 'orders_stats';
            }
            $this->mustRebuildTable = isset($assoc_args['rebuild-table']);
            $this->skipExisting = isset($assoc_args['skip-existing']);
            $this->forceOverwrite = isset($assoc_args['force-overwrite']);
            $this->ordersPerPage = isset($assoc_args['pagination']) ? (int) $assoc_args['pagination'] : -1;
            if($this->isPaginated()){
                $lastPagination = get_option($this->getStateOptionNameSuffix().'_last_pagination',false);
                if($lastPagination !== false){
                    $lastPagination = (int) $lastPagination;
                    if($lastPagination !== $this->ordersPerPage){
                        // Pagination is changed, lets start over again
                        delete_option($this->getStateOptionNameSuffix().'_last_page');
                    }
                }
                update_option($this->getStateOptionNameSuffix().'_last_pagination',$this->ordersPerPage);
                $lastPage = (int) get_option($this->getStateOptionNameSuffix().'_last_page',0);
                $this->currentPage = $lastPage + 1;
                $this->log('Last page: '.$lastPage, true);
                $this->log('Current page: '.$this->currentPage, true);
                $this->log('Orders per page: '.$this->ordersPerPage, true);
            }
            $this->genTable();
            $this->retrievedOrdersIds = $this->getOrdersIds();
            if(empty($this->retrievedOrdersIds)){
                if($this->isPaginated()){
                    // Reset the last page
                    delete_option($this->getStateOptionNameSuffix().'_last_page');
                    $this->success('Operation completed (last page)', true);
                    return 0;
                }
                throw new \RuntimeException('No orders found');
            }
            $this->populateTable();
            if($this->isPaginated()){
                $this->success('Operation completed (page: '.$this->currentPage.')', true);
                update_option($this->getStateOptionNameSuffix().'_last_page',$this->currentPage);
            }else{
                $this->success('Operation completed (last page)', true);
            }
            return 0;
        }catch (DBException $e){
            return 1;
        }
    }

    function isPaginated(): bool
    {
        return isset($this->ordersPerPage) && $this->ordersPerPage != -1;
    }

    /**
     * @return void
     * @throws DBException
     */
    protected function genTable(): void
    {
        if($this->mustRebuildTable){
            $this->log('Dropping existing table...');
            Waboot()->DB()->getSchemaBuilder()->dropIfExists($this->statsTableName);
        }
        if(Waboot()->DB()->tableExists($this->statsTableName)){
            if(!$this->mustRebuildTable){
                $this->log(sprintf('Table %s already exists...', $this->statsTableName));
                return;
            }
            throw new CLIRuntimeException(sprintf('ERROR: Unable to delete table: %s', $this->statsTableName));
        }
        $this->log(sprintf('Creating table %s...', $this->statsTableName));
        $this->log('Taxonomies to use: '.implode(', ',$this->taxonomiesColumns));
        Waboot()->DB()->getSchemaBuilder()->create($this->statsTableName, function (Blueprint $table){
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
            foreach ($this->taxonomiesColumns as $taxonomy){
                $table->string($taxonomy)->default('');
                do_action('wawoo/cli/gen-stat-table/table/taxonomy_cols/tax',$table,$taxonomy);
            }
            do_action('wawoo/cli/gen-stat-table/table',$table);
        });
        $this->log('Table created');
    }

    /**
     * @throws DBException
     */
    protected function populateTable(): void
    {
        $this->log('Generating records...', true);
        foreach ($this->getItemsFromOrders($this->retrievedOrdersIds) as $item){
            $row = [];
            if(!$item instanceof \WC_Order_Item_Product){
                continue;
            }
            $productId = $item->get_product_id();
            if(!\is_int($productId) || $productId === 0){
                $this->warning('- WARNING: Item #'.$item->get_id().' is associated with a non existent product');
                continue;
            }
            $product = $item->get_product();
            if(!$product instanceof \WC_Product){
                $this->warning('- WARNING: Item #'.$item->get_id().' is associated with a non existent product');
                continue;
            }
            $existingRecord = Query::on($this->statsTableName)->select('*')->where([
                ['product_id', '=', $productId],
                ['order_id', '=', $item->get_order_id()]
            ])->get()->first();
            if($existingRecord && $this->skipExisting){
                continue;
            }
            $order = $item->get_order();
            $row['order_id'] = $item->get_order_id();
            $row['product_id'] = $productId;
            $row['product_sku'] = $product->get_sku();
            $row['product_name'] = $product->get_title();
            $row['product_type'] = $product->get_type();
            $orderDateCompleted = $order->get_date_completed();
            $orderDateCreated = $order->get_date_created();
            $orderStatus = $order->get_status();
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
            $paymentMethod = $order->get_payment_method();
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
            $paymentMethodTitle = apply_filters('wawoo/cli/gen-stat-table/row/payment_method_title',$paymentMethodTitle, $paymentMethod);
            if(!\is_string($paymentMethodTitle) || $paymentMethodTitle == ''){
                $paymentMethodTitle = $paymentMethod;
            }
            $row['payment_method'] = $paymentMethodTitle;
            foreach ($this->taxonomiesColumns as $taxonomy){
                $skipTaxonomy = apply_filters('wawoo/cli/gen-stat-table/row/skip_taxonomy',false,$taxonomy,$productId,$item);
                if($skipTaxonomy){
                    $row[$taxonomy] = '';
                    continue;
                }
                $terms = wp_get_object_terms($productId,$taxonomy);
                $terms = apply_filters('wawoo/cli/gen-stat-table/row/parse_terms',$terms,$productId,$taxonomy,$item);
                if(\is_array($terms) && count($terms) > 0){
                    $firstTerm = $terms[0];
                    if($firstTerm instanceof \WP_Term){
                        $row[$taxonomy] = htmlspecialchars_decode($firstTerm->name);
                    }
                }
                $row = apply_filters('wawoo/cli/gen-stat-table/row/parse_taxonomy',$row,$taxonomy,$terms,$productId,$item);
            }
            $row = apply_filters('wawoo/cli/gen-stat-table/row',$row,$productId,$item);
            try{
                if($existingRecord){
                    $existingRecordToCompare = json_decode(json_encode($existingRecord),true);
                    $recordsAreDifferent = $this->recordsAreDifferent($row,$existingRecordToCompare);
                    if($recordsAreDifferent){
                        $this->log(sprintf('- Record for item #%d of order_id #%d already exists but IS DIFFERENT: recreating...', $item->get_id(), $item->get_order_id()));
                        Query::on($this->statsTableName)->where([
                            ['product_id', '=', $productId],
                            ['order_id', '=', $item->get_order_id()]
                        ])->delete();
                    }elseif($this->forceOverwrite){
                        $this->log(sprintf('- Record for item #%d of order_id #%d already exists but MUST BE OVERWRITTEN: recreating...', $item->get_id(), $item->get_order_id()));
                        Query::on($this->statsTableName)->where([
                            ['product_id', '=', $productId],
                            ['order_id', '=', $item->get_order_id()]
                        ])->delete();
                    }else{
                        $this->log(sprintf('- Record for item #%d of order_id #%d already exists and IS THE SAME: skipping...', $item->get_id(), $item->get_order_id()));
                        continue;
                    }
                }else{
                    $this->log('- Inserting record for item #'.$item->get_id());
                }
                Query::on($this->statsTableName)->insert($row);
                $this->log('-- Record inserted', null, $row);
            }catch (DBException $e){
                $this->warning('-- ERROR: record not inserted: '.$e->getMessage(), true, $row);
            }
        }
    }

    /**
     * @param array $newRecord
     * @param array $oldRecord
     * @return bool
     */
    private function recordsAreDifferent(array $newRecord, array $oldRecord): bool
    {
        unset($oldRecord['id']);
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
     * @param $orderIds
     * @return \Generator
     */
    protected function getItemsFromOrders($orderIds): \Generator
    {
        foreach ($this->getOrdersFromIds($orderIds) as $order){
            if(!$order instanceof \WC_Order){
                continue;
            }
            $this->log('- Parsing order #'.$order->get_id());
            $items = $order->get_items(['line_item','shipping']);
            if(\is_array($items) && count($items)){
                foreach ($items as $item){
                    yield $item;
                }
            }
        }
    }

    /**
     * @param array $orderIds
     * @return \Generator
     */
    protected function getOrdersFromIds(array $orderIds): \Generator
    {
        if(empty($orderIds)){
            throw new \RuntimeException('No orders found');
        }
        foreach ($orderIds as $orderId){
            $order = wc_get_order($orderId);
            if(!$order instanceof \WC_Order){
                $this->warning('ERROR: Order ID #'.$orderId.' is invalid');
                continue;
            }
            yield $order;
        }
    }

    /**
     * @return \WC_Order[]
     */
    protected function getOrdersIds(): array
    {
        try {
            if(!empty($this->selectedOrdersIds)){
                $orderIds = $this->selectedOrdersIds;
            }else{
                $allowedOrderStatuses = wc_get_order_statuses();
                $allowedOrderStatuses = apply_filters('wawoo/cli/gen-stat-table/allowed_order_statuses',$allowedOrderStatuses,get_class($this));
                $qArgs = [
                    'limit' => $this->ordersPerPage,
                    'status' => array_keys($allowedOrderStatuses),
                    'return' => 'ids',
                ];
                if($this->isPaginated()){
                    $qArgs['paged'] = $this->currentPage;
                }
                $qArgs = apply_filters('wawoo/cli/gen-stat-table/get_orders_query_params',$qArgs,get_class($this));
                $this->log('Query params: '.json_encode($qArgs), true);
                $orderIds = wc_get_orders($qArgs);
            }
            if(!\is_array($orderIds) || count($orderIds) === 0){
                return [];
            }
            return $orderIds;
        } catch (\Exception $e) {
            return [];
        }
    }
}