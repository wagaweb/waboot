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
    let $ = jQuery;
    $('.ginput_container_select, .ginput_address_country').addClass('custom-select');

    const $customSelects = $(".custom-select");

    $customSelects.each((index, customSelect) => {
        const $select = $(customSelect).find("select");
        // if select has not a default option, add it
        if ($select.find("option:first-child").val() !== "" ) {
            $select.prepend('<option value="" selected>Seleziona un\'opzione:</option>');
        }
        const $selectedItem = $("<div>").addClass("select-selected");
        $selectedItem.html($select.find("option:selected").html());
        $(customSelect).append($selectedItem);

        const $optionsContainer = $("<div>").addClass("select-items select-hide");
        $select.find("option:not(:first)").each((index, option) => {
            const $optionItem = $("<div>").html($(option).html());
            $optionItem.on("click", (event) => {
                const $target = $(event.currentTarget);
                const $select = $target.closest(".custom-select").find("select");
                const $selectedItem = $target.closest(".custom-select").find(".select-selected");
                const selectedIndex = $target.index() + 1;
                $select.prop("selectedIndex", selectedIndex);
                $selectedItem.html($target.html());
                $target.addClass("same-as-selected").siblings().removeClass("same-as-selected");
                $selectedItem.click();
            });
            $optionsContainer.append($optionItem);
        });
        $(customSelect).append($optionsContainer);

        $selectedItem.on("click", (event) => {
            event.stopPropagation();
            closeAllSelects(event.currentTarget);
            $(event.currentTarget).toggleClass("select-arrow-active");
            $(event.currentTarget).next().toggleClass("select-hide");
        });
    });

    function closeAllSelects(currentSelect) {
        $(".custom-select").not($(currentSelect).closest(".custom-select")).each((index, customSelect) => {
            $(customSelect).find(".select-selected").removeClass("select-arrow-active");
            $(customSelect).find(".select-items").addClass("select-hide");
        });
    }

    $(document).on("click", (event) => {
        closeAllSelects(event.target);
    });

}