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
     * @return int
     */
    public function getId(): int
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
     * @param bool $generate
     * @return string|null
     */
    public function getName(bool $generate = true): ?string
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
        $this->name = $name;
    }
}