import $ from 'jquery';

/**
 *
 * @return {boolean}
 */
export function isSingleProductPage(){
    return $('body').hasClass('single-product');
}

/**
 *
 * @return {boolean}
 */
export function isCheckOutPage(){
    return $('body').hasClass('woocommerce-checkout');
}

/**
 *
 * @return {boolean}
 */
export function isCartPage(){
    return $('body').hasClass('woocommerce-cart');
}