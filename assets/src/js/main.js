import Header from './frontend/header';
import { Slidein } from "./frontend/slidein.js";

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

    asideBodyClass();
    scrollToAnimate();

    $(window).on("load",function(){
        if (window.matchMedia('(max-width: 991px)').matches) {

        }
    });

    $("[data-slidein-nav]").slidein({
        toggler: ".slidein-nav__toggle",
    });

    $("a").each(function(){
        var my_href = $(this).attr("href");
        if(/\.(?:jpg|jpeg|gif|png)/i.test(my_href)){
            $(this).addClass('venobox');
        }
    });
    $('.venobox').venobox();
    initCarousels();

});

function asideBodyClass() {
    let $ = jQuery;
    if($('.main__aside').length > 0) {
        $('body').addClass('with-sidebar');
    }
}

function scrollToAnimate(){
    let $ = jQuery;
    let $header = $('.site-header').height();
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

/*
  Il codice seguente va usato con un markup apposito per attivare un effetto slider
  sugli item su mobile
*/
document.addEventListener('DOMContentLoaded', function () {
  const mq = window.matchMedia('(max-width: 782px)');
  if (!mq.matches) return;
  console.log('fired');

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