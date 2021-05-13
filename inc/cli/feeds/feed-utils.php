<?php

namespace Waboot\inc\cli\feeds;

/**
 * @param \WC_Product $product
 * @return string
 */
function getGShoppingDescription(\WC_Product $product): string{
    return trim(preg_replace('/\s+/', ' ', $product->get_description()));
}

/**
 * @param \WC_Product $product
 * @return string
 */
function getProductFeaturedImageSrc(\WC_Product $product): string {
    $src = \wp_get_attachment_image_src($product->get_image_id(), 'full', false);
    if(\is_array($src) && count($src) > 0){
        return $src[0];
    }
    return '';
}

/**
 * @param $product
 * @return array
 */
function getProductImagesSrc($product): array {
    if(\is_int($product)){
        $product = \wc_get_product($product);
    }
    if(!$product instanceof \WC_Product){
        throw new \RuntimeException($product.' in an invalid product');
    }
    $images = [
        0 => getProductFeaturedImageSrc($product),
    ];
    $galleryImagesIds = $product->get_gallery_image_ids();
    foreach($galleryImagesIds as $imageId){
        $src = \wp_get_attachment_image_src($imageId, 'full');
        if(\is_array($src) && count($src) > 0){
            $images[] = $src[0];
        }
    }
    return $images;
}
