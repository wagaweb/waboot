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
    createClassStyleBefore();
    createClassStyleAfter();
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
        var paddingBefore = wbHeaderFixed.padding_before,
            paddingAfter = wbHeaderFixed.padding_after;
        var styleAfter = {
            'padding-top' : wbHeaderFixed.padding_after+'px',
            'padding-bottom': wbHeaderFixed.padding_after+'px',
            'background-color': wbHeaderFixed.color_after
        };

        if (scroll > afterScroll) {
            console.log('maggiore');
            fixedHeader.animate({
                'padding-top': paddingAfter
            });
        } else {
            console.log('minore');
            fixedHeader.animate({
                'padding-top': paddingBefore
            });
        }


    }

    function createClassStyleBefore() {
        var styleBefore = document.createElement('style');
        style.type = 'text/css';
        style.innerHTML = '.style_before { ' +
            'padding-top : ' + wbHeaderFixed.padding_before + 'px;' +
            'padding-bottom: ' + wbHeaderFixed.padding_before + 'px;' +
            'background-color: ' + wbHeaderFixed.color_before + ';'
        document.getElementsByTagName('head')[0].appendChild(styleBefore);

        $(fixedHeader).addClass('cssClass');
    }
    function createClassStyleAfter() {
        var styleAfter = document.createElement('style');
        style.type = 'text/css';
        style.innerHTML = '.style_after { ' +
            'padding-top : ' + wbHeaderFixed.padding_after + 'px;' +
            'padding-bottom: ' + wbHeaderFixed.padding_after + 'px;' +
            'background-color: ' + wbHeaderFixed.color_after + ';'
        document.getElementsByTagName('head')[0].appendChild(styleBefore);

        $(fixedHeader).addClass('cssClass');
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
