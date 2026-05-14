import $ from 'jquery';
import {initHeader} from './frontend/header';
import { Slidein } from "./frontend/slidein.js";
import Cart from './frontend/cart.js';
import MiniCart from './frontend/minicart.js';
import {initCustomCheckoutActions} from './frontend/checkout.js';
import {alterAttributesView} from './frontend/attributes.js';
import {enableProductGallery} from './frontend/productGallery.js';
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
        slideinHeight();
    }
});

$(document).ready(function() {
    initHeader('.menu-item-has-children');
    initCustomerCareModal();

    asideBodyClass();
    scrollToAnimate();
    initCarousel();

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

/*
  Il codice seguente va usato con un markup apposito per attivare un effetto slider
  sugli item su mobile
*/
document.addEventListener('DOMContentLoaded', function () {
  const mq = window.matchMedia('(max-width: 782px)');
  if (!mq.matches) return;

  const wrappers = document.querySelectorAll('._horizontal-scroller-wrapper');

  wrappers.forEach(function (wrapper) {
    const scroller = wrapper.querySelector('._horizontal-scroller');
    const prev = wrapper.querySelector('._horizontal-scroller-prev');
    const next = wrapper.querySelector('._horizontal-scroller-next');

    if (!scroller || !prev || !next) return;

    const getScrollAmount = function () {
      const firstItem = scroller.querySelector(':scope > *');
      if (!firstItem) return scroller.clientWidth;
      return firstItem.getBoundingClientRect().width + 16;
    };

    prev.addEventListener('click', function () {
      scroller.scrollBy({
        left: -getScrollAmount(),
        behavior: 'smooth',
      });
    });

    next.addEventListener('click', function () {
      scroller.scrollBy({
        left: getScrollAmount(),
        behavior: 'smooth',
      });
    });
  });
});

function initCarousels() {
  let $ = jQuery;
  let $carousel = $('._horizontal-scroller');

  function checkCarousel() {
    let windowWidth = $(window).width();

    if (windowWidth < 1024) {
      if (!$carousel.hasClass('slick-initialized')) {
        $carousel.slick({
          infinite: false,
          slidesToShow: 3,
          slidesToScroll: 1,
          arrows: true,
          dots: true,
          prevArrow: '<button type="button" class="slick-prev" aria-label="Previous slide"><i class="far fa-arrow-left" aria-hidden="true"></i></button>',
          nextArrow: '<button type="button" class="slick-next" aria-label="Next slide"><i class="far fa-arrow-right" aria-hidden="true"></i></button>',
          responsive: [
            {
              breakpoint: 768,
              settings: {
                slidesToShow: 2.2,
                slidesToScroll: 1,
                centerMode: false
              }
            },
            {
              breakpoint: 480,
              settings: {
                slidesToShow: 1.2,
                slidesToScroll: 1,
              }
            }
          ]
        });
      }
    } else {
      if ($carousel.hasClass('slick-initialized')) {
        $carousel.slick('unslick');
      }
    }
  }

  // Esegui al caricamento
  checkCarousel();

  // Esegui al resize con debounce
  $(window).on('resize', function () {
    clearTimeout(window.carouselTimer);
    window.carouselTimer = setTimeout(checkCarousel, 200);
  });
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
