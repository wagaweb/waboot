<?php

namespace Waboot\inc\core\woocommerce\repositories;

use Illuminate\Database\Capsule\Manager;
use Waboot\inc\core\DBException;
use waboot\inc\core\woocommerce\Customer;
use Waboot\inc\core\woocommerce\CustomerException;
use Waboot\inc\core\woocommerce\CustomerFactory;
use Waboot\inc\core\woocommerce\CustomerNotFoundException;
use function Waboot\inc\core\helpers\Waboot;

class CustomerRepository
{
    private Manager $db;

    /**
     * @throws DBException
     */
    public function __construct(Manager $queryBuilder = null)
    {
        if(!$queryBuilder){
            $this->db = Waboot()->DB()->getQueryBuilder();
        }
    }

    /**
     * @param int $id
     * @return Customer|null
     * @throws CustomerException
     * @throws CustomerNotFoundException
     */
    public function find(int $id): ?Customer
    {
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