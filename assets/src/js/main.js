import $ from 'jquery';
import {initHeader} from './frontend/header';
import { Slidein } from "./frontend/slidein.js";
import Cart from './frontend/cart.js';
import MiniCart from './frontend/minicart.js';
import {initCustomCheckoutActions} from './frontend/checkout.js';
import {alterAttributesView} from './frontend/attributes.js';
import {enableProductGallery} from './frontend/productGallery.js';
import CatalogFilters from "./frontend/catalogFilters.js";
import {initEuVat} from "./frontend/checkout/invoicing";
import {initCustomerCareModal} from "./frontend/modal.js";
import {isCartPage, isCheckOutPage, isSingleProductPage} from "./utils/wp";

$.fn.slidein = function (options) {
    return this.each(function () {
        if (!$.data(this, "slidein")) {
            $.data(this, "slidein", new Slidein(this, options));
        }
    });
};

$(window).on('load',function(){
    if(isSingleProductPage()) {
        enableProductGallery();
    }
    if (window.matchMedia('(max-width: 991px)').matches) {

    }
});

$(document).ready(function() {
    initHeader('.menu-item-has-children');
    initCustomerCareModal();

    asideBodyClass();
    scrollToAnimate();

    $("[data-slidein-nav]").slidein({
        toggler: ".slidein-nav__toggle",
    });

    $("[data-slidein-search]").slidein({
        toggler: ".slidein-search__toggle",
    });

    $("a").each(function(){
        var my_href = $(this).attr("href");
        if(/\.(?:jpg|jpeg|gif|png)/i.test(my_href)){
            $(this).addClass('venobox');
        }
    });
    $('.venobox').venobox();

    //new CatalogFilters();

    // WooCommerce Addon Start

    let $sitecart = $('[data-minicart]');
    if ($sitecart.length > 0) {
        new MiniCart();
    }

    if(isCartPage()) {
        new Cart();
    }

    if(isCheckOutPage()) {
        initCustomCheckoutActions();
        initEuVat();
    }

    if(isSingleProductPage()) {
        alterAttributesView();
        $('form.bundle_form').attr('action','?addedProduct=true');
    }

    // WooCommerce Addon End

});

function asideBodyClass() {
    if($('.main__aside').length > 0) {
        $('body').addClass('with-sidebar');
    }
}

function scrollToAnimate(){
    let $header = $('.header').height();
    $('a[href^="#"]').on('click', function(event) {
        let target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - $header
            }, 1000);
        }
    });
}
