jQuery(document).ready(function($){

    centerLogo();

    $(window).resize(function() {
        centerLogo()
    });
    function centerLogo() {
        var width = $('#logo a img').width(),
            height = $('#logo').outerHeight(),
            paddingNav = (height-50)/2,
            selector = "ul li:nth-child("+wabootHeaderSplitted.count+")";

        $( '#logo' ).css('margin-left', (width/2)*-1);
        $( selector ).css('margin-right', width);
        var paddingStyle = {
            'padding-top' : paddingNav+'px',
            'padding-bottom': paddingNav+'px'
        };
        $('.main-navigation .navbar-nav').css(paddingStyle);
    }
});