<?php

namespace Waboot\addons\packages\productgallery;

use function Waboot\addons\getAddonDirectory;

remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);
add_action('woocommerce_before_single_product_summary', function(){
    require_once getAddonDirectory('product_gallery').'/templates/productGallery.php';
},14);
