<?php

namespace Waboot\addons\packages\catalog_custom_tables\inc;

add_action('rest_api_init', function(){
    /*
     * Register endpoint for catalog
     * @uri /wp-json/api/v1/news
     */
    register_rest_route('api/v1','/products_ids', [
        'methods' => 'GET',
        'callback' => static function(){
            $api = new \Waboot\addons\packages\catalog_custom_tables\inc\API();
            return $api->getProductsIds(24);
        }
    ]);
});
