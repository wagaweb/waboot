//http://getbootstrap.com/getting-started/#support
if(navigator.userAgent.match(/IEMobile\/10\.0/)) {
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
    //if(isMobile()){
        //Enable swiping...
        $("body").swipe( {
            //Generic swipe handler for all directions
            swipeRight:function(event, direction, distance, duration, fingerCount) {
                $('button.navbar-toggle').trigger('click');
            },
            swipeLeft:function(event, direction, distance, duration, fingerCount) {
                $('button.navbar-toggle').trigger('click');
            }
            //Default is 75px, set to 0 for demo so any distance triggers swipe
            // threshold:0
        });
        //Disable for Metaslider
        $(".metaslider").addClass("noSwipe");
    //}
});