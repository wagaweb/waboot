<?php

namespace Waboot\addons\packages\sizeguide;

use Waboot\inc\core\mvc\HTMLView;
use function Waboot\addons\getAddonDirectory;

/*
 *  Register CPT Size Guide
 */
add_action('init', function () {
    register_post_type('sizeguide', array(
        "label" => __('Size Guide', 'daviparis'),
        "description" => __('Add new Size Guide', 'daviparis'),
        'menu_icon' => 'dashicons-list-view',
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'hierarchical' => false,
        'query_var' => true,
        'supports' => array('title', 'editor', 'revisions', 'thumbnail'),
        'show_in_rest' => true,
        'rest_base' => 'size-charts',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
        'labels' => array(
            'name' => __('Size Guide', 'daviparis'),
            'singular_name' => __('Size Guide', 'daviparis'),
            'menu_name' => __('Size Guide', 'daviparis'),
            'add_new' => __('Add Size Guide', 'daviparis'),
            'add_new_item' => __('Add New Size Guide', 'daviparis'),
            'edit' => __('Edit', 'daviparis'),
            'edit_item' => __('Edit Size Guide', 'daviparis'),
            'new_item' => __('New Size Guide', 'daviparis'),
            'view' => __('View Size Guide', 'daviparis'),
            'view_item' => __('View Size Guide', 'daviparis'),
            'search_items' => __('Search Size Guides', 'daviparis'),
            'not_found' => __('No Size Guide Found', 'daviparis'),
            'not_found_in_trash' => __('No Size Guide Found in Trash', 'daviparis'),
            'parent' => __('Parent Size Guide', 'daviparis'),
        )
    ));

}, 10);

add_action('init', function(){
    add_filter( 'woocommerce_taxonomy_objects_product_cat', function(){
        return array( 'product', 'sizeguide' );
    });
    add_filter('woocommerce_taxonomy_args_product_cat', function($args){
        $args ['show_in_rest'] = true;
        return $args;
    });
}, 4);

add_action('template_redirect', function () {
    if (!is_singular('product')) {
        return;
    }
    add_action('waboot/layout/page-before', function(){
        $productTerm = wp_get_post_terms(get_the_ID(), 'product_cat')[0];
        //var_dump($productTerm);
        if(!$productTerm instanceof \WP_Term){
            return;
        }
        $params = [
            'post_type' => 'sizeguide',
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => $productTerm->slug,
                    'operator' => 'IN'
                )
            )
        ];
        $size = get_posts($params);
        if(!empty($size)){
            $v = new HTMLView(getAddonDirectory('sizeguide').'/templates/size-guide-modal.php',false);
            $v->display([
                'size' => $size[0]
            ]);
        }
    },5);
});
