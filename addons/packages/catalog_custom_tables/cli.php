<?php

namespace Waboot\addons\packages\catalog_custom_tables;

use Waboot\addons\packages\catalog_custom_tables\cli\ImportWCProducts;
use Waboot\addons\packages\catalog_custom_tables\cli\SetupDB;

try{
    \WP_CLI::add_command('esp:import-wc-products', ImportWCProducts::class);
    \WP_CLI::add_command('esp:setup-db', SetupDB::class);
}catch (\Exception $e){}