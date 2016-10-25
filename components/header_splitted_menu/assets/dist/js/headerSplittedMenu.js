jQuery(document).ready(function($){

    defineLogo();

    $(window).resize(function() {
        defineLogo()
    });

    /**
     * Define Logo
     */
    function defineLogo() {
        var logocontainer = $('.logonav'),
            logo = $('.logonav img'),
            width = logo.width(),
            height = logo.outerHeight(),
            navLeft = $('.main-navigation .navbar-split-left'),
            navRight = $('.main-navigation .navbar-split-right'),
            additionalMargin = parseInt(wabootHeaderSplitted.margin);

        logocontainer.css('margin-left', (width/2)*-1);

        // add top nav height, if any
        if (height > 50) {
            navLeft.css('padding-top', (height-50)/2);
            navRight.css('padding-top', (height-50)/2);
            navLeft.css('padding-bottom', (height-50)/2);
            navRight.css('padding-bottom', (height-50)/2);
        }else{
            navLeft.css('padding-top', 0);
            navRight.css('padding-top', 0);
            navLeft.css('padding-bottom', 0);
            navRight.css('padding-bottom', 0);
        }

        navLeft.css('padding-right', width/2+additionalMargin);
        navRight.css('padding-left', width/2+additionalMargin);
    }
});