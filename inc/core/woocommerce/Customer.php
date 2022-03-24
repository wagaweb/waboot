<?php

namespace Waboot\inc\core\woocommerce;

class Customer
{
    /**
     * @var \WC_Customer
     */
    private $wcCustomer;
    /**
     * @var ShippingAddress
     */
    private $currentShippingAddress;

    /**
     * Customer constructor.
     * @param $userId
     * @throws \RuntimeException
     */
    public function __construct($userId)
    {
        try{
            $customer = new \WC_Customer($userId);
            $this->wcCustomer = $customer;
        }catch (\Exception $e){
            throw new \RuntimeException('Invalid userId');
        }
    }

    /**
     * @return \WC_Customer
     */
    public function getWcCustomer(): \WC_Customer
    {
        return $this->wcCustomer;
    }

    /**
     * @return ShippingAddress
     */
    public function getCurrentShippingAddress(): ShippingAddress
    {
        return $this->currentShippingAddress;
    }

    /**
     * @param ShippingAddress $currentShippingAddress
     */
    public function setCurrentShippingAddress(ShippingAddress $currentShippingAddress): void
    {
        $this->currentShippingAddress = $currentShippingAddress;
    }

    /**
     * @return void
     */
    public function fetchCurrentShippingAddress(): void
    {
        $this->currentShippingAddress = ShippingAddress::fromCustomer($this);
    }
}