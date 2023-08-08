<?php

namespace Waboot\inc\cli;

use Waboot\inc\cli\product_import\ImportProducts;
use Waboot\inc\core\utils\Utilities;

/**
 * Setting up woo_variation_gallery
 */
/*
add_action('wawoo_product_images_importer/setting_variation_gallery', function(int $productId, array $galleryIds){
    $this->log(sprintf('- Set di "woo_variation_gallery_images" per %d = %s',$productId,implode(',',$galleryIds)));
    update_post_meta($productId,'woo_variation_gallery_images', $galleryIds);
},10,2);
*/

/**
 * Get product by ean13 in images importer
 */
/*
add_filter('wawoo_product_images_importer/product_id_fetcher', function(string $identifier){
    global $wpdb;

    $sql = <<<SQL
select pm.post_id from $wpdb->postmeta pm where pm.meta_key = 'ean13' and pm.meta_value = %s
SQL;
    $res = $wpdb->get_var($wpdb->prepare($sql, $identifier));

    return $res === null ? null : (int)$res;
}, 10);
*/

/**
 * Set a custom color taxonomy name for each brand
 */
/*
add_filter('wawoo_products_importer/color_taxonomy_name', function(string $colorTaxonomyName, int $postId, ImportProducts $importProductsCmdInstance){
    $brandTerms = Utilities::getPostTermsHierarchical($postId,$importProductsCmdInstance->getBrandTaxonomyName(),[],true,true);
    if(!\is_array($brandTerms) || empty($brandTerms)){
        return $colorTaxonomyName;
    }
    $firstBrand = array_shift($brandTerms);
    if(!$firstBrand instanceof \WP_Term){
        return $colorTaxonomyName;
    }
    return $colorTaxonomyName .'-'.$firstBrand->slug;
}, 10, 3);
*/

/**
 * Setup _swatch_type and _swatch_type_options metas
 */
/*
add_action('wawoo_product_importer/finalize_products', function(array $parsedProductIds, bool $dryRun, ImportProducts $importProductsCmdInstance){
    foreach ($this->parsedProductIds as $productId) {
        $colorTerms = wp_get_object_terms($productId,$importProductsCmdInstance->getColorTaxonomyName($productId));
        if(!\is_array($colorTerms) || empty($colorTerms)){
            continue;
        }
        if(!$dryRun){
            //Generate woocommerce-variation-swatches-and-photos metas
            //Something like: a:1:{s:32:"8ff8884a227e03637010e84defa23a5b";a:4:{s:4:"type";s:12:"term_options";s:6:"layout";s:7:"default";s:4:"size";s:19:"swatches_image_size";s:10:"attributes";a:18:{s:32:"2b58ea4dbb6766a40f7df280ddb28741";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37507";}s:32:"28917255bd9dfc20b362b90f438585ae";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37508";}s:32:"0570f9d1860518fd015db1822429ed2d";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37509";}s:32:"ec2c75bf2609d29fa9101cf48002eca0";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37510";}s:32:"1b85a4ab757c1c4a028b07ca7a85930f";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37511";}s:32:"243721e861cbb621643fbbc5358e986f";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37512";}s:32:"c567ca78138d84e530e9099f7692a1c1";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37513";}s:32:"c50867656758e10a3a8ea9b6a65f19d8";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37514";}s:32:"fe2a10e51e4ff67dc06f9c00d4344b7a";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37515";}s:32:"37382caf9964dd77df2f21c89b907e4c";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37516";}s:32:"5086db3577c8e02a281a3358810d0f5d";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37517";}s:32:"5019adc65f7340cedc832ef93eb184a7";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37518";}s:32:"edafbc670d4193c361e98fa1a0f17557";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37519";}s:32:"5ebdc72a60227e9185cbc531dbc2a941";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37520";}s:32:"b36dc264178e93c67f11dfcd4f6634c5";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37521";}s:32:"c83b205d5c60aed1f04f9cfa2121aa5e";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37522";}s:32:"93d5af028b2b68d99a8d481a3073f9b9";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37523";}s:32:"3f0d09daebc0f17b1d6d343c765d56ec";a:3:{s:4:"type";s:5:"photo";s:5:"color";s:7:"#ffffff";s:5:"photo";s:5:"37506";}}}}
            $key = md5($importProductsCmdInstance->getColorTaxonomyName($productId));
            $swatchTypeOptions = [];
            $swatchTypeOptions[$key] = [
                'type' => 'term_options',
                'layout' => 'default',
                'size' => 'swatches_image_size',
                'attributes' => []
            ];
            foreach ($colorTerms as $colorTerm){
                $termKey = md5(sanitize_title($colorTerm->slug));
                $swatchTypeOptions[$key]['attributes'][$termKey] = [
                    'type' => 'color',
                    'color' => '#ffffff',
                    'image' => ''
                ];
            }
            update_post_meta($productId,'_swatch_type','pickers');
            update_post_meta($productId,'_swatch_type_options',$swatchTypeOptions);
        }
    }
}, 10, 3);
*/

/*
 * Product EXPORT: Decoding HTML
 */
add_filter('waboot/cli/product_export/file_written/post_processing', static function(string $content){
    $content = html_entity_decode($content, ENT_QUOTES, 'utf-8');
    return $content;
},10,2);

/*
 * Product EXPORT: Replace strange  
 */
add_filter('waboot/cli/product_export/file_written/post_processing', static function(string $content){
    $content = str_replace(' ',' ',$content);
    return $content;
},10,2);