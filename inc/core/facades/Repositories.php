<?php

namespace Waboot\inc\core\facades;

use Waboot\inc\core\woocommerce\repositories\CustomerRepository;

class Repositories
{
    public static function customer(): CustomerRepository
    {
        return new CustomerRepository();
    }
}