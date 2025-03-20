<?php

namespace Waboot\addons\packages\checkout\base_mods;

use function Waboot\addons\getAddonDirectory;

remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
add_action( 'woocommerce_thankyou', function(){
    include getAddonDirectory('checkout').'/templates/thankyou-order-buttons.php';
}, 10 );