<?php

namespace Waboot\inc\core\woocommerce\addresses;

class ShippingAddress extends AbstractCustomerAddress
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $previousName;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return bool
     */
    public function hasName(): bool
    {
        return $this->name !== null;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        if(!$this->name){
            if($generate){
                // Generate a name
                $name = $this->getAddress1().$this->getCity().$this->getPostCode().$this->getCountry().$this->getState();
                $name = str_replace(" ", "_", $name);
                $this->name = $name;
            }
        }
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        if(isset($this->name) && !isset($this->previousName)){
            $this->previousName = $this->name;
        }
        $this->name = $name;
    }

    /**
     * @param array $data
     * @return void
     */
    public function updateFromData(array $data): void {
        if(isset($data['shipping_id'])){
            $this->setName($data['shipping_id']);
        }
        if(isset($data['shipping_first_name'])){
            $this->setFirstName($data['shipping_first_name']);
        }
        if(isset($data['shipping_last_name'])){
            $this->setLastName($data['shipping_last_name']);
        }
        if(isset($data['shipping_company'])){
            $this->setCompany($data['shipping_company']);
        }
        if(isset($data['shipping_address_1'])){
            $this->setAddress1($data['shipping_address_1']);
        }
        if(isset($data['shipping_address_2'])){
            $this->setAddress2($data['shipping_address_2']);
        }
        if(isset($data['shipping_city'])){
            $this->setCity($data['shipping_city']);
        }
        if(isset($data['shipping_state'])){
            $this->setState($data['shipping_state']);
        }
        if(isset($data['shipping_postcode'])){
            $this->setPostCode($data['shipping_postcode']);
        }
        if(isset($data['shipping_country'])){
            $this->setCountry($data['shipping_country']);
        }
    }
}