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


    /**
     * first case
     * we have a fixed header form the beginning
     */
    if (fixedOnStart) {
        // add the class fixed_header_component
        fixedHeader.addClass('fixed_header_component');
        // and if the user is on top of the page apply style before,
        enterFixed();
        // now we can change the css checking if the position of the window is before or after the breakpoint specified by the user
        $( window ).scroll(function() {
            enterFixed();
        });
    /**
     * Second case,
     * we have header entering on scroll up
     */
    } else if (enterScroll) {
        // da dove partiamo
        var initialScroll = $(document).scrollTop();
        $( window ).scroll(function() {
            enterScrollUp();
        });
    /**
     * Last case,
     * the header enter after the breakpoint specified by the user
     */
    } else {
        // at the document ready apply the correct class
        enterafter();

        // then update the classes on window scroll
        $( window ).scroll(function() {
            enterafter();
        });
    }
    /**
     *
     *
     *
     *
     */
    function enterScrollUp() {
        var currentScroll = $(this).scrollTop(), // lo scroll attuale
            headerHeight = fixedHeader.height(),
            sensitiveness = 15, // voglio filtrare per i movimenti decisi
            delta = currentScroll - initialScroll; // la differenza fra lo scroll attuale e il punto di partenza
        if (Math.abs(delta) > sensitiveness) {
            if (delta > 0) { // se il delta è maggiore di 0 stiamo andando giù
                // fixedHeader.removeClass('fixed_header_component');
                fixedHeader.css('margin-top', headerHeight * -1);
            } else if (delta < 0) { // altrimenti stiamo andando su
                fixedHeader.addClass('fixed_header_component').css('margin-top', 0);
            }
        }
        // anyhow just reset margin and class if we are at the top
        if (currentScroll<=headerHeight) {
            fixedHeader.css('margin-top', 0);
        }
        initialScroll = currentScroll; // a ogni scroll dobbiamo aggiornare la posizione iniziale attualizzandola con la posizione corrente

    }

    function enterFixed() {
        var scroll = $(document).scrollTop();
        if (scroll > afterScroll) {
            fixedHeader.css(styleAfter);
        } else {
            fixedHeader.css(styleBefore);
        }
    }

    function enterafter() {
        var scroll = $(document).scrollTop();
        if (scroll < afterScroll && scroll <= fixedHeader.height()) {
            fixedHeader.removeClass('fixed_header_component');
            fixedHeader.css('margin-top', 0);
        } else if (scroll < afterScroll && scroll > fixedHeader.height()) {
            fixedHeader.removeClass('fixed_header_component');
            fixedHeader.css('margin-top', fixedHeader.height()*-1);
        } else {
            fixedHeader.addClass('fixed_header_component').css('margin-top', 0);
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
