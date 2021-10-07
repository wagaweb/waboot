<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\cli\CLIRuntimeException;

class ExportOrders extends AbstractCommand
{
    /**
     * @var string
     */
    protected $logDirName = 'export-orders';
    /**
     * @var string
     */
    protected $logFileName = 'export-orders';
    /**
     * @var array
     */
    protected $selectedOrdersIds;
    /**
     * @var bool
     */
    protected $forceAlreadyExported;

    /**
     * Export orders
     *
     * ## OPTIONS
     *
     * [--force-already-exported]
     * : Export already parsed orders
     *
     * [--orders]
     * : Comma separated orders id to export
     *
     * [--marker]
     * : Put a text marker at the beginning and at the end of the log (eg: "Testing" will put "### Testing BEGIN" and "### Testing END")
     *
     * [--quiet]
     * : Do not output log messages
     *
     * [--progress]
     * : Display progress bar
     *
     * [--dry-run]
     * : Dry run
     *
     * ## EXAMPLES
     *
     *      wp wawoo:export-orders
     *
     *      wp wawoo:export-orders --dry-run
     *
     *      wp wawoo:export-orders --force-already-exported --orders=23,44,56
     *
     *      wp wawoo:export-orders --force-already-exported --orders=23,44,56 --marker="Testing"
     */
    public function __invoke(array $args, array $assoc_args): int
    {
        parent::__invoke($args, $assoc_args);
        if($this->dryRun){
            $this->log('### DRY-RUN ###');
        }
        $this->forceAlreadyExported = isset($assoc_args['force-already-exported']);
        if(isset($assoc_args['orders'])){
            $selectedOrders = explode(',',$assoc_args['orders']);
            if(\is_array($selectedOrders)){
                $this->selectedOrdersIds = $selectedOrders;
            }
        }
        try{
            $this->beginCommandExecution();
            $this->exportOrders();
            $this->success('Operation completed');
            $this->endCommandExecution();
            return 0;
        }catch (CLIRuntimeException | OrderExportException | \RuntimeException | \Exception $e){
            $this->error($e->getMessage());
            return 1;
        }
    }

    private function exportOrders(): void
    {
        $this->log('Searching for valid orders...');
        $orders = $this->getOrders();
        if(!\is_array($orders) || count($orders) === 0){
            $this->log('No exportable order found');
            return;
        }
        if($this->showProgressBar){
            $progressBar = $this->makeProgressBar('Exporting',count($orders));
        }
        foreach ($orders as $order){
            try{
                $this->log('- Exporting order: '.$order->getOrderNumber().' (#'.$order->getOrderId().')');
                $exportStatus = $order->export($this->dryRun);
                if($exportStatus){
                    $this->log('-- exported successfully');
                    if(!$this->dryRun){
                        $order->setAsExported();
                    }
                }
                if($this->showProgressBar && isset($progressBar)){
                    $this->tickProgressBar($progressBar);
                }
            }catch (OrderExportException $e){
                $this->log('EXPORT ERROR: '.$e->getMessage());
                continue;
            }
        }
        if($this->showProgressBar && isset($progressBar)){
            $this->completeProgressBar($progressBar);
        }
    }

    /**
     * @return ExportableOrder[]
     */
    private function getOrders(): array
    {
        try{
            if(\is_array($this->selectedOrdersIds)){
                $orderIds = $this->selectedOrdersIds;
            }else{
                $qArgs = [
                    'limit' => -1,
                    //'limit' => 10,
                    'return' => 'ids',
                    'orderby' => 'date',
                    'order' => 'ASC',
                    'status' => ['processing','on-hold'],
                ];
                $q = new \WC_Order_Query($qArgs);
                $orderIds = $q->get_orders();
                if(!\is_array($orderIds) || count($orderIds) === 0){
                    $this->log('The query did not return any order');
                    return [];
                }
            }
            $orders = [];
            foreach ($orderIds as $orderId){
                try{
                    $omsOrder = new ExportableOrder($orderId);
                    if($omsOrder->isAlreadyExported()){
                        $this->log('- Found order: '.$omsOrder->getOrderNumber().' (#'.$orderId.'; status: '.$omsOrder->getWcOrder()->get_status().' ) -- already exported');
                        if($this->forceAlreadyExported){
                            $orders[] = $omsOrder;
                        }
                    }else{
                        $this->log('- Found order: '.$omsOrder->getOrderNumber().' (#'.$orderId.'; status: '.$omsOrder->getWcOrder()->get_status().' )');
                        $orders[] = $omsOrder;
                    }
                }catch (\RuntimeException $e){
                    $this->log($e->getMessage());
                    continue;
                }
            }
            return $orders;
        }catch (\Exception $e){
            return [];
        }
    }
}