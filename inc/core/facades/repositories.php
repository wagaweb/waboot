<?php

namespace Waboot\inc\core\facades;

use Waboot\inc\core\woocommerce\repositories\CustomerRepository;

function customer(): CustomerRepository {
    return new CustomerRepository();
}