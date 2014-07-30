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

jQuery(document).ready(function() {
    // Style form controls
    jQuery(type = "text").addClass('form-control');
    jQuery(type = "select").addClass('form-control');
    jQuery(type = "textarea").addClass('form-control');
    jQuery('input#submit').addClass('btn btn-primary');
    jQuery('.gform_button').addClass('btn btn-primary btn-lg');
    // Tables
    jQuery('table').addClass('table');

});

// Inizialize fastclick to body
jQuery(function() {
    FastClick.attach(document.body);
});


// Inizialize touchSwipe
jQuery(function($) {
    //Enable swiping...
    $("body").swipe({
        //Generic swipe handler for all directions
        swipeRight: function(event, direction, distance, duration, fingerCount) {
            $('button.navbar-toggle').trigger('click');
        },
        swipeLeft: function(event, direction, distance, duration, fingerCount) {
            $('button.navbar-toggle').trigger('click');
        },
        //Default is 75px, set to 0 for demo so any distance triggers swipe
        // threshold:0
    });
    //Disable for Metaslider
    $(".metaslider").addClass("noSwipe");
});
