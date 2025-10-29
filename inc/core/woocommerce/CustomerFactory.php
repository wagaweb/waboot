<?php

namespace Waboot\inc\core\woocommerce;

use Waboot\inc\core\woocommerce\repositories\CustomerRepository;

class CustomerFactory
{
    /**
     * @throws CustomerNotFoundException
     * @throws CustomerException
     */
    public static function create(int $customerId): Customer
    {
        return new Customer($customerId);
    }

    /**
     * @param string $email
     * @return Customer
     * @throws CustomerFactoryException
     */
    public static function getFromEmail(string $email): Customer
    {
        $customer = (new CustomerRepository())->findByEmail($email);
        if(!$customer){
            throw new CustomerFactoryException('CustomerFactory::getFromEmail - No user found');
        }
        return $customer;
    }
}