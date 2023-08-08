<?php

namespace Waboot\inc\core\woocommerce;

class CustomerFactory
{
    /**
     * @param string $email
     * @return Customer
     * @throws CustomerFactoryException
     */
    public static function getFromEmail(string $email): Customer
    {
        $user = get_user_by('email', $email);
        if(!$user instanceof \WP_User){
            throw new CustomerFactoryException('CustomerFactory::getFromEmail - No user found');
        }
        $customer = new Customer($user->ID);
        return $customer;
    }
}