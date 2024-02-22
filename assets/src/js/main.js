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

    asideBodyClass();
    scrollToAnimate();
    initCarousel();
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
    let $slideIn = $('.slidein');
    $slideIn.css({ 'height': 'calc(100% - ' + headerHeight+ 'px)' });
    $slideIn.css('top',headerHeight);
}

function initCarousel() {
    let $ = jQuery;
    $('.block__carousel--1 .wp-block-group__inner-container').addClass('block__carousel owl-carousel').owlCarousel({
        items: 1,
        autoplay:true,
        loop: true,
        autoHeight: true,
        nav: true,
        navText: ['<i class="fas fa-chevron-left"></i>','<i class="fas fa-chevron-right"></i>']
    })
    $('.wp-block-gallery.block__carousel--1').wrapInner('<div class="blocks-gallery-grid"></div>');
    $('.wp-block-gallery.block__carousel--2').wrapInner('<div class="blocks-gallery-grid"></div>');
    $('.wp-block-gallery.block__carousel--3').wrapInner('<div class="blocks-gallery-grid"></div>');
    $('.wp-block-gallery.block__carousel--4').wrapInner('<div class="blocks-gallery-grid"></div>');
    $('.wp-block-gallery.block__carousel--5').wrapInner('<div class="blocks-gallery-grid"></div>');

    $('.block__carousel--1 > .blocks-gallery-grid').addClass('block__carousel owl-carousel').owlCarousel({
        items: 1,
        autoplay:true,
        loop: true,
        autoHeight: true,
        dots:false,
        nav: false,
        0 : {
            nav: false,
        },
        768 : {
            nav: true,
        }
    })
    $('.block__carousel--2 > .blocks-gallery-grid').addClass('block__carousel owl-carousel').owlCarousel({
        autoplay:true,
        loop: true,
        dots:false,
        nav: true,
        navText: ['<i class="fas fa-chevron-left"></i>','<i class="fas fa-chevron-right"></i>'],
        responsive : {
            0 : {
                items: 1,
            },
            768 : {
                items: 2,
                margin: 30
            }
        }
    })
    $('.block__carousel--3 > .blocks-gallery-grid').addClass('block__carousel owl-carousel').owlCarousel({
        autoplay:true,
        loop: true,
        dots:false,
        nav: true,
        navText: ['<i class="fas fa-chevron-left"></i>','<i class="fas fa-chevron-right"></i>'],
        responsive : {
            0 : {
                items: 1,
            },
            768 : {
                items: 3,
                margin: 30
            }
        }
    })
    $('.block__carousel--4 > .blocks-gallery-grid').addClass('block__carousel owl-carousel').owlCarousel({
        autoplay:true,
        loop: true,
        dots:false,
        nav: true,
        navText: ['<i class="fas fa-chevron-left"></i>','<i class="fas fa-chevron-right"></i>'],
        responsive : {
            0 : {
                items: 1,
            },
            768 : {
                items: 4,
                margin: 30
            }
        }
    })
    $('.block__carousel--5 > .blocks-gallery-grid').addClass('block__carousel owl-carousel').owlCarousel({
        autoplay:true,
        loop: true,
        dots:false,
        nav: true,
        navText: ['<i class="fas fa-chevron-left"></i>','<i class="fas fa-chevron-right"></i>'],
        responsive : {
            0 : {
                items: 1,
            },
            768 : {
                items: 5,
                margin: 30
            }
        }
    })
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
    $(selector).each(function () {
        const $price = $(this);
        const regular = Number(
            $price
                .find('del bdi')
                .clone()
                .children()
                .remove()
                .end()
                .text()
                .replace(',', '.')
        );
        const current = Number(
            $price
                .find('ins bdi')
                .clone()
                .children()
                .remove()
                .end()
                .text()
                .replace(',', '.')
        );
        if (regular > 0 && !Number.isNaN(current)) {
            const percentage = 100 - (current * 100) / regular;
            $price.append(
                '<span class="sale-percentage">-' +
                Math.round(percentage) +
                '%</span>'
            );
        }
    });
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