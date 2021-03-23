import Header from './frontend/header';
import { Slidein } from "./frontend/slidein.js";
import Cart from './frontend/cart.js';
import MiniCart from './frontend/minicart.js';
import Checkout from './frontend/checkout.js';
import Attributes from './frontend/attributes.js';
import {enableProductGallery} from './frontend/productGallery.js';
import CatalogFilters from "./frontend/catalogFilters.js";
import Modal from "./frontend/modal.js";

jQuery.fn.slidein = function (options) {
    return this.each(function () {
        if (!jQuery.data(this, "slidein")) {
            jQuery.data(this, "slidein", new Slidein(this, options));
        }
    });
};

jQuery(document).ready(function($) {
    "use strict";

    new Header('.menu-item-has-children');
    new Modal();

    asideBodyClass();
    scrollToAnimate();

    $(window).on("load",function(){
        if (window.matchMedia('(max-width: 991px)').matches) {

        }
    });

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

    let $sitecart = jQuery('[data-minicart]');
    if ($sitecart.length > 0) {
        new MiniCart();
    }

    if($('body').hasClass('woocommerce-cart')) {
        new Cart();
    }
    if($('body').hasClass('woocommerce-checkout')) {
        new Checkout();
    }

    if($('body').hasClass('single-product')) {
        new Attributes();
        enableProductGallery();

        $('form.bundle_form').attr('action','?addedProduct=true');

    }

    // WooCommerce Addon End

});

function asideBodyClass() {
    let $ = jQuery;
    if($('.main__aside').length > 0) {
        $('body').addClass('with-sidebar');
    }
}

function scrollToAnimate(){
    let $ = jQuery;
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
