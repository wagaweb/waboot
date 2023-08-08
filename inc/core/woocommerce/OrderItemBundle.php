<?php

namespace Waboot\inc\core\woocommerce;

use function Waboot\inc\getBundleRealTotals;
use function Waboot\inc\isBundleProduct;

class OrderItemBundle
{
    /**
     * @var \WC_Product_Bundle
     */
    private $wcOrderItem;
    /**
     * @var OrderItemBundledProduct[]
     */
    private $items;

    /**
     * WabootOrderItemBundle constructor.
     * @param \WC_Order_Item_Product $item
     * @throws \RuntimeException
     */
    public function __construct(\WC_Order_Item_Product $item)
    {
        if(!isBundleProduct($item->get_product_id())){
            throw new \RuntimeException('WabootOrderItemBundle: The provided item is not a bundle');
        }
        $this->wcOrderItem = $item;
    }

    /**
     * @param OrderItemBundledProduct $itemProduct
     */
    public function addItem(OrderItemBundledProduct $itemProduct): void
    {
        $this->items[] = $itemProduct;
    }

    /**
     * @return OrderItemBundledProduct[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return float[]
     */
    public function getTotals(): array
    {
        $quantities = [];
        foreach ($this->items as $item){
            if(!$item instanceof OrderItemBundledProduct){
                continue;
            }
            $product = $item->getProduct();
            if(!$product instanceof \WC_Product){
                continue;
            }
            $quantities[$product->get_id()] = (int) $item->getWcOrderItem()->get_quantity();
        }
        return getBundleRealTotals($this->wcOrderItem->get_product_id(),$quantities);
    }

    /**
     * @return \WC_Product_Bundle
     */
    public function getWcOrderItem()
    {
        return $this->wcOrderItem;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return (float) $this->getWcOrderItem()->get_product()->get_price() * $this->wcOrderItem->get_quantity();
    }

    /**
     * @return float
     */
    public function getRegularPrice(): float
    {
        return (float) $this->getWcOrderItem()->get_product()->get_regular_price() * $this->wcOrderItem->get_quantity();
    }

    /**
     * @return float
     */
    public function getSalePrice(): float
    {
        return (float) $this->getWcOrderItem()->get_product()->get_sale_price() * $this->wcOrderItem->get_quantity();
    }
}