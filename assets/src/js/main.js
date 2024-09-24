import $ from 'jquery';
import {initHeader} from './frontend/header';
import { Slidein } from "./frontend/slidein.js";
import Cart from './frontend/cart.js';
import MiniCart from './frontend/minicart.js';
import {initCustomCheckoutActions} from './frontend/checkout.js';
import {alterAttributesView} from './frontend/attributes.js';
import {enableProductGallery} from './frontend/productGallery.js';
import { enableProductQuantity } from "./frontend/quantity.js";
import {initEuVat} from "./frontend/checkout/invoicing";
import {initCustomerCareModal} from "./frontend/modal.js";
import {isCartPage, isCheckOutPage, isSingleProductPage} from "./utils/wp";
import {Modal} from "./frontend/modals";

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
        enableProductQuantity();
    }
    if (window.matchMedia('(max-width: 991px)').matches) {
        //slideinHeight();
    }
});

$(document).ready(function() {
    initHeader('.menu-item-has-children');
    initCustomerCareModal();

    new Modal();

    asideBodyClass();
    scrollToAnimate();
    initCarousel();
    initAccordion();
    showHidePasswords();

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
    venoboxCarouselGutenbergGallery();

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
        printSalePercentage('p.price');
        $('.single_variation_wrap').on('show_variation', (e, v) => {
            const price = $('.woocommerce-variation-price .price');
            if (price.length > 0) {
                $('p.price').replaceWith(
                    `<p class="price">${price.html()}</p>`
                );
                price.remove();
                printSalePercentage('p.price');
            }
        });

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

function slideinHeight(){
    let $ = jQuery;
    let headerHeight = $('header').outerHeight();
    $('.slidein').css({ 'height': 'calc(100% - ' + headerHeight+ 'px)' });
    $('.slidein').css('top',headerHeight);
}

function initCarousel() {
}

function venoboxCarouselGutenbergGallery() {
    let $ = jQuery;
    var pageGallery = 1;
    $(".wp-block-gallery").each(function() {
        $(this).find('figure a').attr('data-gall', 'venobox-gallery-'+pageGallery);
        pageGallery++;
    });
}

function printSalePercentage(selector) {
}

function showHidePasswords() {
    const $passwords = $("input[type='password']");

    $passwords.each(function () {
        const $passwordInput = $(this);
        const $showPasswordInput = $passwordInput.next('.password__toggle');

        if (!$showPasswordInput.length) {
            const $icon = $('<i class="far fa-eye"></i>');
            const $showPasswordContainer = $('<div class="password__toggle"></div>').append($icon);

            $passwordInput.add($showPasswordContainer).wrapAll('<span class="password__wrapper"></span>');

            $showPasswordContainer.on('click', function () {
                const showPassword = $passwordInput.attr("type") === "password";
                $passwordInput.attr("type", showPassword ? "text" : "password");
                $icon.toggleClass('fa-eye fa-eye-slash');
            });
        }
    });
}

function initAccordion() {
    let $ = jQuery;

    $('.accordion__header').on('click', function() {
        const $accordionItem = $(this).closest('.accordion__item');
        const $accordionContent = $accordionItem.find('.accordion__body');

        if ($accordionItem.hasClass('active')) {
            $accordionItem.removeClass('active');
            $accordionContent.slideUp();
        } else {
            $accordionItem.addClass('active');
            $accordionContent.slideDown();
            $('.accordion__item')
                .not($accordionItem)
                .removeClass('active')
                .find('.accordion__body')
                .slideUp();
        }
    });

    $('.product__more-info').on('click', function() {
        let $accordionFirst = $('.accordion__item:first-child');
        $accordionFirst.addClass('active');
        $accordionFirst.find('.accordion__body').slideDown();
    });
}