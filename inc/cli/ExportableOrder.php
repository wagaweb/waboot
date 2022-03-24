<?php

namespace Waboot\inc\cli;

use Waboot\inc\core\woocommerce\ExportableOrderInterface;
use Waboot\inc\core\woocommerce\Order;

class ExportableOrder extends Order implements ExportableOrderInterface
{
    /**
     * @var \Exception[]
     */
    protected $exportErrors = [];

    /**
     * @return string
     */
    public function getDateAtom(): string
    {
        $orderDate = $this->getWcOrder()->get_date_created();
        if(!$orderDate instanceof \WC_DateTime){
            return '';
        }
        return $orderDate->__toString();
    }

    public function getPaymentMethod(): string
    {
        $orderPaymentMethod = $this->getWcOrder()->get_payment_method();
        $omsPaymentMethod = 'cc';
        switch ($orderPaymentMethod){
            case 'cod':
                $omsPaymentMethod = 'mark';
                break;
            case 'paypal':
                $omsPaymentMethod = 'paypal';
                break;
            case 'bacs':
                $omsPaymentMethod = 'wt';
                break;
        }
        return $omsPaymentMethod;
    }

    public function setAsExported(): void
    {
        update_post_meta($this->orderId, '_exported', '1' );
        try{
            $exportedDate = new \DateTime('now',new \DateTimeZone('Europe/Rome'));
            update_post_meta($this->orderId, '_exWabootProductVariationported_date', $exportedDate->format('Y-m-d'));
        }catch (\Exception $e){}
    }

    /**
     * @return bool
     */
    public function isAlreadyExported(): bool
    {
        $meta = get_post_meta($this->orderId, '_exported', true);
        return $meta === '1';
    }

    /**
     * @return array
     */
    public function generateExportData(): array
    {
        $WcOrder = $this->getWcOrder();

        $subtotal = (float) $WcOrder->get_subtotal();
        $total = (float) $WcOrder->get_total();
        $status = $WcOrder->get_status();
        $notes = $WcOrder->get_customer_note();

        $data = [
            'order' => (string) $this->getOrderNumber(),
            'subtotal' => $subtotal,
            'total' => $total,
            'paymentMethod' => $this->getPaymentMethod(),
            'channel' => 'website',
            'date' => $this->getDateAtom(),
            'status' => $status,
            'extraData' => new \stdClass(),
            'notes' => $notes,
            'items' => []
        ];

        $this->populateItems();

        foreach ($this->getItems() as $k => $item){
            try{
                $itemObj = new ExportableOrderItem($item,$this);
                if(!$itemObj->canBeAddedAsItem()){
                    continue;
                }
                $itemData = $itemObj->generateExportData();
                if(isset($itemData[0])){
                    foreach ($itemData as $itemDatum){
                        $data['items'][] = $itemDatum;
                    }
                }else{
                    $data['items'][] = $itemData;
                }
            }catch (\Exception $e){
                $this->exportErrors[] = $e;
                continue;
            }
        }

        return $data;
    }

    /**
     * @param bool $dryRun
     * @return bool
     */
    public function export(bool $dryRun = false): bool
    {
        // TODO: Implement export() method.
        return true;
    }
}