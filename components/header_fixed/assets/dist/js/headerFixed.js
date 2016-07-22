jQuery(document).ready(function($){


    var fixedHeader = $(wbHeaderFixed.fixed_class),
        fixedOnStart = wbHeaderFixed.fixed_on_start,
        enterScroll = wbHeaderFixed.scroll_enter,
        afterScroll = wbHeaderFixed.enter_after;

    var styleBefore = {
        'padding-top' : wbHeaderFixed.padding_before+'px',
        'padding-bottom': wbHeaderFixed.padding_before+'px',
        'background-color': wbHeaderFixed.color_before
    };

    var styleAfter = {
        'padding-top' : wbHeaderFixed.padding_after+'px',
        'padding-bottom': wbHeaderFixed.padding_after+'px',
        'background-color': wbHeaderFixed.color_after
    };

    // createClassStyleBefore();
    // createClassStyleAfter();
    // if we want a fixed header from the beginning
    if (fixedOnStart) {
        // add the class fixed_header_component and the padding before
        fixedHeader.addClass('fixed_header_component');
        fixedHeader.css(styleBefore);
    }

    $( window ).scroll(function() {
        var scroll = $(document).scrollTop();
        manageHeader(scroll);

    });

    function manageHeader(scroll) {
        if (scroll > afterScroll) {
            fixedHeader.stop().animate(styleAfter, 250);
        } else {
            fixedHeader.stop().animate(styleBefore, 250);
        }
    }
});


/*
 wbHeaderFixed.fixed_class
 wbHeaderFixed.fixed_on_start
 wbHeaderFixed.color_before
 wbHeaderFixed.padding_before
 wbHeaderFixed.color_after
 wbHeaderFixed.padding_after
 wbHeaderFixed.scroll_enter
 wbHeaderFixed.enter_after
 */
