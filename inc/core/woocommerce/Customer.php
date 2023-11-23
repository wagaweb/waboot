<?php

namespace Waboot\inc\core\woocommerce;

class Customer
{
    private ?int $id = null;
    private \WC_Customer $wcCustomer;
    private ?ShippingAddress $currentShippingAddress = null;

    /**
     * Customer constructor.
     * @param int|null $userId
     * @throws \RuntimeException
     */
    public function __construct(int $userId = null)
    {
        try{
            if($userId !== null){
                $customer = new \WC_Customer($userId);
                $this->id = $userId;
            }else{
                $customer = new \WC_Customer();
            }
            $this->wcCustomer = $customer;
        }catch (\Exception | \Throwable $e){
            throw new \RuntimeException($e->getMessage());
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return ShippingAddress
     */
    public function getCurrentShippingAddress(): ?ShippingAddress
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

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->getId() !== null && $this->getId() !== 0;
    }

    /**
     * @param string $metaKey
     * @return string|null
     */
    protected function fetchMetaData(string $metaKey): ?string
    {
        if($this->isNew()){
            return null;
        }
        $m = get_user_meta($this->getId(),$metaKey,true);
        if(\is_string($m) && $m !== ''){
            return $m;
        }
        return null;
    }

    /**
     * @return int
     */
    public function save(): int
    {
        if($this->getCurrentShippingAddress() !== null){
            $sa = $this->getCurrentShippingAddress();
            $this->getWcCustomer()->set_shipping_first_name($sa->getFirstName());
            $this->getWcCustomer()->set_shipping_last_name($sa->getLastName());
            $this->getWcCustomer()->set_shipping_address_1($sa->getAddress1());
            $this->getWcCustomer()->set_shipping_address_2($sa->getAddress2());
            $this->getWcCustomer()->set_shipping_company($sa->getCompany());
            $this->getWcCustomer()->set_shipping_city($sa->getCity());
            $this->getWcCustomer()->set_shipping_postcode($sa->getPostCode());
            $this->getWcCustomer()->set_shipping_country($sa->getCountry());
        }
        $id = $this->getWcCustomer()->save();
        if($this->isNew()){
            $this->id = $id;
        }
        return $id;
    }
}