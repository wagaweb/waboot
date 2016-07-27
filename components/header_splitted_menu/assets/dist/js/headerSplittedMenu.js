jQuery(document).ready(function($){

    $(window).load(function () {
        centerLogo();
    });

    $(window).resize(function() {
        centerLogo()
    });
    function centerLogo() {
        var logo = $('#logo'),
            width = logo.find('a img').width(),
            additionalMargin = parseInt(wabootHeaderSplitted.margin) * 2,
            height = logo.outerHeight(),
            paddingNav = (height-50)/2,
            nav = $("ul.navbar-nav.nav"),
            navLeft = $('.main-navigation .navbar-nav.splitted:eq(0)'),
            navRight = $('.main-navigation .navbar-nav.splitted:eq(1)'),
            deltaWidth = navLeft.width() - navRight.width(),
            topnavWrapperHeight = $('#topnav-wrapper').height();

        logo.css('margin-left', (width/2)*-1);

        // add top nav height, if any
        if (topnavWrapperHeight > 1) {
            logo.css('margin-top', topnavWrapperHeight);
        }
        //$( selector ).css('margin-right', (width+additionalMargin));
        var paddingStyle = {
            'padding-top' : paddingNav+'px',
            'padding-bottom': paddingNav+'px'
        };
        nav.css(paddingStyle);
        navLeft.css('padding-right', width/2+additionalMargin);
        navRight.css('padding-left', width/2+additionalMargin);

        if (Math.abs(deltaWidth)> 0) {
            if (navLeft.width() > navRight.width()){
                navLeft.css('margin-left', deltaWidth*-1);
            } else {
                navRight.css('margin-right', deltaWidth);
            }

        }
    }
});