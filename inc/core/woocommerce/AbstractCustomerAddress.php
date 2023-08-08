<?php

namespace Waboot\inc\core\woocommerce;

abstract class AbstractCustomerAddress
{
    /**
     * @var string
     */
    protected $firstName;
    /**
     * @var string
     */
    protected $lastName;
    /**
     * @var string
     */
    protected $country;
    /**
     * @var string
     */
    protected $state;
    /**
     * @var string
     */
    protected $postCode;
    /**
     * @var string
     */
    protected $address1;
    /**
     * @var string
     */
    protected $address2;
    /**
     * @var string
     */
    protected $city;
    /**
     * @var string
     */
    protected $company;
    /**
     * @var string
     */
    protected $email;
    /**
     * @var string
     */
    protected $phone;

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        if(!$this->firstName){
            return '';
        }
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        if(!$this->lastName){
            return '';
        }
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        if(!$this->country){
            return '';
        }
        return $this->country;
    }

    /**
     * @param string $country
     */
    public function setCountry(string $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        if(!$this->state){
            return '';
        }
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getPostCode(): string
    {
        if(!$this->postCode){
            return '';
        }
        return $this->postCode;
    }

    /**
     * @param string $postCode
     */
    public function setPostCode(string $postCode): void
    {
        $this->postCode = $postCode;
    }

    /**
     * @return string
     */
    public function getAddress1(): string
    {
        if(!$this->address1){
            return '';
        }
        return $this->address1;
    }

    /**
     * @param string $address1
     */
    public function setAddress1(string $address1): void
    {
        $this->address1 = $address1;
    }

    /**
     * @return string
     */
    public function getAddress2(): string
    {
        if(!$this->address2){
            return '';
        }
        return $this->address2;
    }

    /**
     * @param string $address2
     */
    public function setAddress2(string $address2): void
    {
        $this->address2 = $address2;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        if(!$this->city){
            return '';
        }
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        if(!$this->company){
            return '';
        }
        return $this->company;
    }

    /**
     * @param string $company
     */
    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        if(!$this->email){
            return '';
        }
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        if(!$this->phone){
            return '';
        }
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return [
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'country' => $this->getCountry(),
            'state' => $this->getState(),
            'postCode' => $this->getPostCode(),
            'address1' => $this->getAddress1(),
            'address2' => $this->getAddress2(),
            'city' => $this->getCity(),
            'company' => $this->getCompany(),
            'email' => $this->getEmail(),
            'phone' => $this->getPhone(),
        ];
    }
}