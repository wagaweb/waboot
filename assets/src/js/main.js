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
    youTubeNoCookieChangeUrl();
    customSelect();

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

function youTubeNoCookieChangeUrl() {
    let $ = jQuery;
    self.addEventListener('fetch', event => {
        if (event.request.mode === 'navigate' && event.preloadResponse) {
            event.respondWith((async () => {
                const response = await event.preloadResponse;
                if (response) {
                    return response;
                }
                // If there is no response, fetch the request normally.
                return fetch(event.request);
            })());
            event.waitUntil((async () => {
                const response = await event.preloadResponse;
                // Here you can add any custom logic to handle the preloaded response
                console.log('Preloaded navigation response:', response);
            })());
        }
    });

    $("iframe[src^='https://www.youtube.com/']").each(function() {
        var oldSrc = $(this).attr("src");
        var newSrc = oldSrc.replace("https://www.youtube.com/", "https://www.youtube-nocookie.com/");
        $(this).attr("src", newSrc);
    });
}

function customSelect() {
    const $ = jQuery;

    $('select').each(function() {
        const $this = $(this);
        const numberOfOptions = $this.children('option').length;

        $this.addClass('select--hidden');
        $this.wrap('<div class="select"></div>').after('<div class="select--styled"></div>');

        const $styledSelect = $this.next('div.select--styled');
        $styledSelect.text($this.children('option').eq(0).text());

        const $list = $('<ul/>', {
            class: 'select__options'
        }).insertAfter($styledSelect);

        for (let i = 0; i < numberOfOptions; i++) {
            const $option = $this.children('option').eq(i);
            $('<li/>', {
                text: $option.text(),
                rel: $option.val()
            }).appendTo($list);
        }

        const $listItems = $list.children('li');

        $styledSelect.click(function(e) {
            e.stopPropagation();
            const $active = $('div.select--styled.active');
            $active.not(this).removeClass('active').next('ul.select__options').hide();
            $(this).toggleClass('active').next('ul.select__options').toggle();
        });

        $listItems.click(function(e) {
            e.stopPropagation();
            $styledSelect.text($(this).text()).removeClass('active');
            $this.val($(this).attr('rel'));
            $list.hide();
        });

        $(document).click(function() {
            $styledSelect.removeClass('active');
            $list.hide();
        });
    });
}