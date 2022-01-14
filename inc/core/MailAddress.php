<?php

namespace Waboot\inc\core;

class MailAddress
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $address;

    /**
     * @param string $address
     * @param string|null $name
     * @throws MailException
     */
    public function __construct(string $address, string $name = null)
    {
        $this->name = $name;
        if(!is_email($address)){
            throw new MailException('Invalid email provided');
        }
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getHeaderValue(): string
    {
        if($this->getName() !== null){
            return $this->getName().' <'.$this->getAddress().'>';
        }
        return $this->getAddress();
    }
}