<?php

namespace Waboot\inc\core\woocommerce;

use Waboot\inc\core\DBException;
use Waboot\inc\core\woocommerce\addresses\BillingAddress;
use Waboot\inc\core\woocommerce\addresses\BillingAddressRepository;
use Waboot\inc\core\woocommerce\addresses\ShippingAddress;
use Waboot\inc\core\woocommerce\addresses\ShippingAddressRepository;
use function Waboot\inc\core\helpers\logException;
use function Waboot\inc\core\Waboot;

class Customer
{
    private ?int $id = null;
    private \WC_Customer $wcCustomer;
    private ShippingAddressRepository $shippingAddressRepository;
    private BillingAddressRepository $billingAddressRepository;
    private ?ShippingAddress $currentShippingAddress = null;
    private array $shippingAddresses = [];
    private ?BillingAddress $billingAddress = null;

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
            $this->shippingAddressRepository = new ShippingAddressRepository();
            $this->billingAddressRepository = new BillingAddressRepository();
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
     * @return ShippingAddress|null
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
        $this->currentShippingAddress = $this->shippingAddressRepository->getCurrentByCustomer($this);
    }

    /**
     * @return bool
     */
    public function hasShippingAddresses(): bool
    {
        $this->fetchShippingAddresses();
        return !empty($this->shippingAddresses);
    }

    /**
     * @return void
     */
    public function fetchShippingAddresses(): void
    {
        try{
            $addresses = $this->shippingAddressRepository->findByCustomer($this);
            if(!empty($addresses)){
                $this->shippingAddresses = $addresses;
            }else{
                $this->shippingAddresses = [];
            }
        }catch (DBException $e){
            $this->shippingAddresses = [];
            Waboot()->logToFile('waboot-debug',$e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function populateDefaultShippingAddress(): void
    {
        if($this->hasShippingAddresses()){
            return;
        }
        $current = $this->shippingAddressRepository->getCurrentByCustomer($this);
        if(!$current->isComplete()){
            return;
        }
        // Save the current to user
        if(!$current->hasName()){
            $current->setName('default');
        }
        try{
            $this->shippingAddressRepository->save($current);
            $this->fetchCurrentShippingAddress();
        }catch (DBException $e){
            Waboot()->logToFile('waboot-debug',$e->getMessage());
        }
    }

    /**
     * @return ShippingAddress[]
     */
    public function getShippingAddresses(): array
    {
        try{
            if(!$this->hasShippingAddresses()){
                $this->populateDefaultShippingAddress();
            }
            $addresses = $this->shippingAddressRepository->findByCustomer($this);
            if(empty($addresses)){
                return [];
            }
            return $addresses;
        }catch (\Exception | \Throwable $e){
            Waboot()->logToFile('waboot-debug', $e->getMessage());
            return [];
        }
    }

    /**
     * @param string $addressName
     * @return ShippingAddress|null
     */
    public function getShippingAddressByName(string $addressName): ?ShippingAddress {
        try{
            $addresses = $this->getShippingAddresses();
            if(empty($addresses)){
                return null;
            }
            foreach($addresses as $address){
                if($address->getName() === $addressName){
                    return $address;
                }
            }
            return null;
        }catch (\Exception | \Throwable $e){
            return null;
        }
    }

    /**
     * @param BillingAddress $billingAddress
     * @return void
     */
    public function setBillingAddress(BillingAddress $billingAddress): void
    {
        $this->billingAddress = $billingAddress;
    }

    /**
     * @return BillingAddress|null
     */
    public function getBillingAddress(): ?BillingAddress
    {
        if(!$this->billingAddress){
            $this->fetchBillingAddress();
        }
        return $this->billingAddress;
    }

    /**
     * @return void
     */
    public function fetchBillingAddress(): void
    {
        $this->billingAddress = $this->billingAddressRepository->findByCustomer($this);
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
     * @param bool $preventPasswordChangeEmailNotification
     * @return int
     */
    public function save(bool $preventPasswordChangeEmailNotification = false): int
    {
        if($preventPasswordChangeEmailNotification){
            //@see: wp-includes/user.php
            add_filter('send_password_change_email', '__return_false', 99, 2);
        }
        $id = $this->getWcCustomer()->save();
        if($this->isNew()){
            $this->id = $id;
        }
        if($this->getBillingAddress() !== null){
            $this->getBillingAddressRepository()->save($this->getBillingAddress(),$this);
        }
        if($this->getCurrentShippingAddress() !== null){
            $this->getShippingAddressRepository()->setCurrentToCustomer($this->getCurrentShippingAddress(),$this);
        }
        if($this->getShippingAddresses() !== null){
            foreach ($this->getShippingAddresses() as $shippingAddress){
                if(!$shippingAddress instanceof ShippingAddress){
                    continue;
                }
                try{
                    $this->getShippingAddressRepository()->save($shippingAddress);
                }catch (\Exception | \Throwable $e){
                    logException($e,self::class);
                }
            }
        }
        return $id;
    }

    /**
     * @return ShippingAddressRepository
     */
    public function getShippingAddressRepository(): ShippingAddressRepository
    {
        return $this->shippingAddressRepository;
    }

    /**
     * @return BillingAddressRepository
     */
    public function getBillingAddressRepository(): BillingAddressRepository
    {
        return $this->billingAddressRepository;
    }
}