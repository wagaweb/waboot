<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\cli\CLIRuntimeException;
use Waboot\inc\core\DBException;
use Waboot\inc\order_stats\OrderStatsException;
use function Waboot\inc\core\helpers\Waboot;
use function Waboot\inc\order_stats\createOrderStatsTable;
use function Waboot\inc\order_stats\generateOrderStatRows;
use function Waboot\inc\order_stats\getOrderIdsAlreadyIntoStatsTable;
use function Waboot\inc\order_stats\getOrderStatsTableName;
use function Waboot\inc\order_stats\getOrderStatusForStats;
use function Waboot\inc\order_stats\getTaxonomiesForStats;
use function Waboot\inc\order_stats\insertStatRow;

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
            'description' => 'Specifies whether to skip already parsed orders',
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
            $this->taxonomiesColumns = getTaxonomiesForStats();
            if(isset($assoc_args['table-name']) && \is_string($assoc_args['table-name']) && $assoc_args['table-name'] !== ''){
                $tableTmpName = sanitize_title($assoc_args['table-name']);
                add_filter('waboot/order_stats/table/table_name', static function () use ($tableTmpName){
                    return $tableTmpName;
                });
            }
            $this->statsTableName = getOrderStatsTableName();
            $this->skipExisting = isset($assoc_args['skip-existing']);
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
            $this->mustRebuildTable = isset($assoc_args['rebuild-table']);
            if($this->isPaginated() && isset($lastPage) && $lastPage > 0){
                $this->mustRebuildTable = false; // Avoid rebuilding the table if we are paginating and we are on the second page or more
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
        }catch (DBException|OrderStatsException $e){
            return 1;
        }
    }

    function isPaginated(): bool
    {
        return isset($this->ordersPerPage) && $this->ordersPerPage != -1;
    }

    /**
     * @return void
     * @throws DBException|OrderStatsException
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
        createOrderStatsTable();
        $this->log('Table created');
    }

    /**
     * @return void
     */
    protected function populateTable(): void
    {
        $this->log('Generating records...', true);
        foreach ($this->retrievedOrdersIds as $orderId){
            $this->log('Generating rows for order #'.$orderId, false);
            try{
                $rows = generateOrderStatRows($orderId);
            }catch (\Exception|\Throwable $e){
                $this->warning('-- ERROR: '.$e->getMessage(), false);
                $rows = [];
            }
            if(empty($rows)){
                $this->log('- No rows generated for order #'.$orderId, false);
            }else{
                $this->log(sprintf('- %d rows generated for order #%s', count($rows), $orderId));
            }
            foreach ($rows as $itemId => $row){
                try{
                    $this->log('- Inserting record for item #'.$itemId.' (order #'.$orderId.')');
                    $insertRecordResult = insertStatRow($row);
                    if($insertRecordResult){
                        $this->log('-- Record inserted', null, $row);
                    }else{
                        $this->log('-- Record NOT inserted (already existing)', null, $row);
                    }
                }catch (\Exception|\Throwable $e){
                    $this->warning('-- ERROR: record not inserted: '.$e->getMessage(), true, $row);
                }
            }
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
                $allowedOrderStatuses = getOrderStatusForStats();
                $qArgs = [
                    'limit' => $this->ordersPerPage,
                    'status' => $allowedOrderStatuses,
                    'return' => 'ids',
                    'orderby' => 'date',
                    'order' => 'DESC',
                ];
                if($this->skipExisting){
                    $parsedOrderIds = getOrderIdsAlreadyIntoStatsTable($this->statsTableName);
                    $qArgs['exclude'] = $parsedOrderIds;
                }
                if($this->isPaginated()){
                    $qArgs['paged'] = $this->currentPage;
                }
                $qArgs = apply_filters('wawoo/cli/gen-stat-table/get_orders_query_params',$qArgs,get_class($this));
                $qArgsToLog = array_merge($qArgs,[]);
                if(isset($qArgsToLog['exclude']) && count($qArgsToLog['exclude']) > 10){
                    $qArgsToLog['exclude'] = 'Excluded IDs: '.count($qArgsToLog['exclude']);
                }
                $this->log('Query params: '.json_encode($qArgsToLog), true);
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