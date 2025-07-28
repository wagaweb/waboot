<?php

namespace Waboot\inc\hooks\woocommerce;

use Waboot\inc\enums\Feeds;

add_action('woocommerce_product_options_general_product_data', static function(){
    /**
     * @var \WC_Product
     */
    global $product_object;
    $excludeFromFeeds = get_post_meta($product_object->get_id(), Feeds::EXCLUDE_FROM_FEEDS_META_KEY, true);
    ?>
    <div class="options_group">
        <h2>Feeds</h2>
        <?php
        woocommerce_wp_checkbox(
            [
                'id' => Feeds::EXCLUDE_FROM_FEEDS_META_KEY,
                'value' => $excludeFromFeeds === '1' ? '1' : '0',
                'label' => __('Exclude from feeds', LANG_TEXTDOMAIN),
                'cbvalue' => '1',
            ]
        );
        ?>
    </div>
    <?php
});

add_action('woocommerce_variation_options', static function($loop, $variation_data, \WP_Post $variation){
    $excludeFromFeeds = get_post_meta($variation->ID, Feeds::EXCLUDE_FROM_FEEDS_META_KEY, true);
    ?>
    <div>
        <strong>Feeds</strong>
    <?php
    woocommerce_wp_checkbox(
        [
            'id' => '_variations_'.Feeds::EXCLUDE_FROM_FEEDS_META_KEY.'[' . $loop . ']',
            'class' => 'form-row',
            'label' => __('Exclude from feeds', LANG_TEXTDOMAIN).'&nbsp;',
            'value' => $excludeFromFeeds === '1' ? '1' : '0',
            'cbvalue' => '1',
        ]
    );
    ?>
    </div>
    <?php
},11,3);

add_action('woocommerce_process_product_meta', static function(int $postId){
    if(isset($_POST[Feeds::EXCLUDE_FROM_FEEDS_META_KEY])){
        update_post_meta($postId, Feeds::EXCLUDE_FROM_FEEDS_META_KEY, '1');
    }else{
        delete_post_meta($postId, Feeds::EXCLUDE_FROM_FEEDS_META_KEY);
    }
});

add_action( 'woocommerce_save_product_variation', static function($variation_id, $i){
    if(isset($_POST['_variations_'.Feeds::EXCLUDE_FROM_FEEDS_META_KEY],$_POST['_variations_'.Feeds::EXCLUDE_FROM_FEEDS_META_KEY][$i])){
        update_post_meta($variation_id, Feeds::EXCLUDE_FROM_FEEDS_META_KEY, '1');
    }else{
        delete_post_meta($variation_id, Feeds::EXCLUDE_FROM_FEEDS_META_KEY);
    }
},10,2);