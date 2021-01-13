<?php

namespace Waboot\addons\packages\catalog_manager;

/*
 * Please clone https://github.com/wagaweb/waga-woocommerce-csv-cli-importer
 */
if(\is_file(__DIR__.'/waga-woocommerce-csv-cli-importer/src/index.php')){
    define('WWCCSV_USE_OWN_AUTOLOADER', false);
    require_once __DIR__.'/waga-woocommerce-csv-cli-importer/src/index.php';
}