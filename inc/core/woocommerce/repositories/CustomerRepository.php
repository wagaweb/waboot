<?php

namespace Waboot\inc\core\woocommerce\repositories;

use Waboot\inc\core\repositories\AbstractRepository;
use waboot\inc\core\woocommerce\Customer;
use Waboot\inc\core\woocommerce\CustomerException;
use Waboot\inc\core\woocommerce\CustomerFactory;
use Waboot\inc\core\woocommerce\CustomerNotFoundException;

class CustomerRepository extends AbstractRepository
{
    /**
     * @param int|string $id
     * @return Customer|null
     * @throws CustomerException
     * @throws CustomerNotFoundException
     */
    public function find(int|string $id): ?Customer
    {
        if(!is_numeric($id)) {
            return null;
        }
        $id = (int) $id;
        $user = get_user_by('id', $id);
        if(!$user instanceof \WP_User) {
            return null;
        }
        return CustomerFactory::create($user->ID);
    }

    /**
     * @param string $email
     * @return Customer|null
     * @throws CustomerException
     * @throws CustomerNotFoundException
     */
    public function findByEmail(string $email): ?Customer
    {
        $user = get_user_by('email', $email);
        if(!$user instanceof \WP_User){
            return null;
        }
        return CustomerFactory::create($user->ID);
    }
}