jQuery(document).ready(function($){

    centerLogo();

    $(window).resize(function() {
        centerLogo()
    });
    function centerLogo() {
        var logo = $('#logo'),
            width = logo.find('a img').width(),
            additionalMargin = parseInt(wabootHeaderSplitted.margin) * 2,
            height = logo.outerHeight(),
            paddingNav = (height-50)/2,
            selector = "ul.navbar-nav.nav li:nth-child("+ wabootHeaderSplitted.count + ")",
            topnavWrapperHeight = $('#topnav-wrapper').height();

        logo.css('margin-left', (width/2)*-1);

        // add top nav height, if any
        if (topnavWrapperHeight > 1) {
            logo.css('margin-top', topnavWrapperHeight);
        }
        $( selector ).css('margin-right', (width+additionalMargin));
        var paddingStyle = {
            'padding-top' : paddingNav+'px',
            'padding-bottom': paddingNav+'px'
        };
        $('.main-navigation .navbar-nav').css(paddingStyle);
    }
});