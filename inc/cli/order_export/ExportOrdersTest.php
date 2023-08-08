<?php

namespace Waboot\inc\cli\order_export;

use Waboot\inc\core\cli\AbstractCommand;
use Waboot\inc\core\cli\CLIRuntimeException;

class ExportOrdersTest extends AbstractCommand
{
    /**
     * @var string
     */
    protected $logDirName = 'export-orders';
    /**
     * @var string
     */
    protected $logFileName = 'export-orders-test';
    /**
     * @var array
     */
    protected $selectedOrdersIds;

    /**
     * Export orders
     *
     * ## OPTIONS
     *
     * [--orders]
     * : Comma separated orders id to export
     *
     * ## EXAMPLES
     *
     *      wp wawoo:test:export-orders --orders=23,44,56
     */
    public function __invoke(array $args, array $assoc_args): int
    {
        parent::__invoke($args, $assoc_args);
        $this->skipLog = true;
        if(isset($assoc_args['orders'])){
            $selectedOrders = explode(',',$assoc_args['orders']);
            if(\is_array($selectedOrders)){
                $this->selectedOrdersIds = $selectedOrders;
            }
        }else{
            $this->error('No orders provided');
            return 1;
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
        $orders = $this->getOrders();
        if(!\is_array($orders) || count($orders) === 0){
            \WP_CLI::line('No exportable order found');
            return;
        }
        foreach ($orders as $order){
            try{
                \WP_CLI::line('### Order: '.$order->getOrderNumber().' (#'.$order->getOrderId().')');
                $orderData = $order->generateExportData();
                if(class_exists('\cli\Table')){
                    $generalData = $orderData;
                    unset($generalData['items'],$generalData['extraData']);
                    $items = $orderData['items'];
                    $orderTable = new \cli\Table();
                    $orderTable->setHeaders(array_keys($generalData));
                    $orderTable->addRow(array_values($generalData));
                    foreach($orderTable->getDisplayLines() as $line){
                        \WP_CLI::line($line);
                    }
                    if(count($items) > 0){
                        \WP_CLI::line('###### Items');
                        $itemsTable = new \cli\Table();
                        $itemsTable->setHeaders(array_keys($items[0]));
                        foreach ($items as $item){
                            $itemsTable->addRow(array_values($item));
                        }
                        foreach($itemsTable->getDisplayLines() as $line){
                            \WP_CLI::line($line);
                        }
                    }
                }else{
                    var_dump($orderData);
                }
            }catch (\Exception $e){
                $this->log('EXPORT ERROR: '.$e->getMessage());
                continue;
            }
        }
    }

    /**
     * @return ExportableOrder[]
     */
    private function getOrders(): array
    {
        try{
            $orderIds = $this->selectedOrdersIds;
            $orders = [];
            foreach ($orderIds as $orderId){
                try{
                    $omsOrder = new ExportableOrder($orderId);
                    $orders[] = $omsOrder;
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