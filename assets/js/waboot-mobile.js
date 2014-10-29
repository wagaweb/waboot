//http://getbootstrap.com/getting-started/#support
if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
    var msViewportStyle = document.createElement('style')
    msViewportStyle.appendChild(
        document.createTextNode(
            '@-ms-viewport{width:auto!important}'
        )
    );
    document.querySelector('head').appendChild(msViewportStyle);
}

// Inizialize fastclick to body
jQuery(document).ready(function($) {
    FastClick.attach(document.body);
    $("body").swipe({
        swipeRight: function(event, direction, distance, duration, fingerCount) {
            if ($(".navbar-mobile-collapse").css('right') == '0px') {
                $('button.navbar-toggle').trigger('click');
            }
        },
        swipeLeft: function(event, direction, distance, duration, fingerCount) {
            if ($(".navbar-mobile-collapse").css('right') == '0px') {
                $('button.navbar-toggle').trigger('click');
            }
        }
    });
    //Disable for Metaslider
    $(".metaslider").addClass("noSwipe");
});
