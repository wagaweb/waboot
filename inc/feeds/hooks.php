<?php

namespace Waboot\inc\feeds;

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

add_action('admin_menu', static  function(){
    add_submenu_page(
        'tools.php',
        'WaWoo Feeds',
        'WaWoo Feeds',
        'manage_options', 'wawoo-feeds',
        'Waboot\inc\feeds\renderFeedToolsAdminSubPage'
    );
});

add_action( 'admin_init', function () {
    //$productTaxonomies = get_object_taxonomies('product', 'names');
    $productTaxonomies = ['product_cat', 'product_tag']; // Customize here
    foreach( $productTaxonomies as $taxonomy ) {
        add_action( "create_{$taxonomy}", function ($term_id) {
            if( isset( $_POST[ '_gshopping_product_category' ] ) && $_POST[ '_gshopping_product_category' ] !== '' ) {
                update_term_meta( $term_id, '_gshopping_product_category', sanitize_text_field( $_POST[ '_gshopping_product_category' ] ) );
            }
            if( isset( $_POST[ '_gshopping_shipping_label' ] ) && $_POST[ '_gshopping_shipping_label' ] !== '' ) {
                update_term_meta( $term_id, '_gshopping_shipping_label', sanitize_text_field( $_POST[ '_gshopping_shipping_label' ] ) );
            }
        } );
        add_action( "edited_{$taxonomy}", function ($term_id) {
            if( isset( $_POST[ '_gshopping_product_category' ] ) && $_POST[ '_gshopping_product_category' ] !== '' ) {
                update_term_meta( $term_id, '_gshopping_product_category', sanitize_text_field( $_POST[ '_gshopping_product_category' ] ) );
            } else {
                delete_term_meta( $term_id, '_gshopping_product_category' );
            }
            if( isset( $_POST[ '_gshopping_shipping_label' ] ) && $_POST[ '_gshopping_shipping_label' ] !== '' ) {
                update_term_meta( $term_id, '_gshopping_shipping_label', sanitize_text_field( $_POST[ '_gshopping_shipping_label' ] ) );
            } else {
                delete_term_meta( $term_id, '_gshopping_shipping_label' );
            }
        } );
        add_action( "{$taxonomy}_add_form_fields", function () {
            ?>
            <div class="form-field">
                <label for="_gshopping_product_category">
                    <?php _ex( 'Google Product Category', 'WaWoo Feeds', LANG_TEXTDOMAIN ) ?>
                </label>
                <input type="text" name="_gshopping_product_category" id="_gshopping_product_category" value="">
                <p class="description">
                    <?php _ex( 'Specify a custom category mapping for this term', 'WaWoo Feeds', LANG_TEXTDOMAIN ) ?>
                    <br/>
                    <a href="https://support.google.com/merchants/answer/6324436?hl=it&ref_topic=6324338"
                       target="_blank">https://support.google.com/merchants/answer/6324436?hl=it&ref_topic=6324338</a><br/>
                    <a href="https://www.google.com/basepages/producttype/taxonomy.en-US.txt" target="_blank">https://www.google.com/basepages/producttype/taxonomy.en-US.txt</a>
                </p>
            </div>
            <div class="form-field">
                <label for="_gshopping_shipping_label">
                    <?php _ex( 'Google Shipping Label', LANG_TEXTDOMAIN ) ?>
                </label>
                <input type="text" name="_gshopping_shipping_label" id="_gshopping_shipping_label" value="">
                <p class="description">
                    <?php _ex( 'Specify a custom shipping label for this term', 'WaWoo Feeds', LANG_TEXTDOMAIN ) ?><br/>
                    <a href="https://support.google.com/merchants/answer/6324504" target="_blank">https://support.google.com/merchants/answer/6324504</a>
                </p>
            </div>
            <?php
        }, 20 );
        add_action( "{$taxonomy}_edit_form_fields", function ($term) {
            $currentProductCategory = get_term_meta( $term->term_id, '_gshopping_product_category', true );
            $currentShippingLabel = get_term_meta( $term->term_id, '_gshopping_shipping_label', true );
            ?>
            <tr class="form-field">
                <th scope="row">
                    <label for="custom_meta">
                        <?php _ex( 'Google Product Category', 'WaWoo Feeds', LANG_TEXTDOMAIN ) ?>
                    </label>
                </th>
                <td>
                    <input type="text" name="_gshopping_product_category" id="_gshopping_product_category"
                           value="<?php echo esc_attr( $currentProductCategory ); ?>">
                    <p class="description">
                        <?php _ex( 'Specify a custom category mapping for this term', 'WaWoo Feeds', LANG_TEXTDOMAIN ) ?>
                        <br/>
                        <a href="https://support.google.com/merchants/answer/6324436" target="_blank">https://support.google.com/merchants/answer/6324436</a><br/>
                        <a href="https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt" target="_blank">https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt</a>
                    </p>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row">
                    <label for="custom_meta">
                        <?php _ex( 'Google Shipping Label', 'WaWoo Feeds', LANG_TEXTDOMAIN ) ?>
                    </label>
                </th>
                <td>
                    <input type="text" name="_gshopping_shipping_label" id="_gshopping_shipping_label"
                           value="<?php echo esc_attr( $currentShippingLabel ); ?>">
                    <p class="description">
                        <?php _ex( 'Specify a custom shipping label for this term', 'WaWoo Feeds', LANG_TEXTDOMAIN ) ?>
                        <br/>
                        <a href="https://support.google.com/merchants/answer/6324504" target="_blank">https://support.google.com/merchants/answer/6324504</a>
                    </p>
                </td>
            </tr>
            <?php
        }, 20 );
    }
} );