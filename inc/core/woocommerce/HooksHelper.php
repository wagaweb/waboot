<?php

namespace Waboot\inc\core\woocommerce;

use Waboot\inc\core\woocommerce\hooks_shortcuts\CartHooksHelperTrait;

class HooksHelper
{
    use CartHooksHelperTrait;

    public static function getInstance(): ?HooksHelper
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    protected function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }
}