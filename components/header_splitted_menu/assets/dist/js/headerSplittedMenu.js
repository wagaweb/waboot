function Waboot_HeaderSplittedMenu(params){
    var $ = jQuery;
    var defaults_params = {
        positionTop: jQuery(window).height() / 2,
        widthStart: 400,
        widthEnd: 200,
        heightEnd: 0,
        splitStart: 0,
        splitEnd: 100
    };

    this.params = $.extend(defaults_params,params);

    var self = this;

    this.mosefx = function(){
        var $ = jQuery;

        var logo = $('.logonav');
        var logoImg = $('.logonav img');
        var navLeft = $('.main-navigation .navbar-split-left');
        var navRight = $('.main-navigation .navbar-split-right');

        var positionLeftStart = (self.params.widthStart / 2) * -1;
        var positionLeftEnd = (self.params.widthEnd / 2) * -1;

        navLeft.css('padding-top', self.params.heightEnd + 'px');
        navLeft.css('padding-bottom', self.params.heightEnd + 'px');
        navRight.css('padding-top', self.params.heightEnd + 'px');
        navRight.css('padding-bottom', self.params.heightEnd + 'px');

        logo.css('top', self.params.positionTop);
        logoImg.css('width', self.params.widthStart);
        logo.css('margin-left', positionLeftStart);

        $(window).scroll(function () {
            if ($(window).scrollTop() < self.params.positionTop) {
                var scrollTop = $(window).scrollTop();
                logo.css('top', self.params.positionTop - scrollTop + 'px');
                logoImg.css('width', self.params.widthStart - scrollTop * ( (self.params.widthStart - self.params.widthEnd) / self.params.positionTop ) + 'px');
                logo.css('margin-left', positionLeftStart - scrollTop * ( (positionLeftStart - positionLeftEnd) / self.params.positionTop ) + 'px');
                navLeft.css('padding-right', self.params.splitStart - scrollTop * ( (self.params.splitStart - self.params.splitEnd) / self.params.positionTop ) + 'px');
                navRight.css('padding-left', self.params.splitStart - scrollTop * ( (self.params.splitStart - self.params.splitEnd) / self.params.positionTop ) + 'px');
            } else {
                logo.css('top', '0px');
                logoImg.css('width', self.params.widthEnd + 'px');
                logo.css('margin-left', positionLeftEnd + 'px');
                navLeft.css('padding-right', self.params.splitEnd + 'px');
                navRight.css('padding-left', self.params.splitEnd + 'px');
            }
        });
    };

    this.split = function(){
        var $ = jQuery;
        var logocontainer = $('.logonav'),
            logo = $('.logonav img'),
            width = logo.width(),
            height = logo.outerHeight(),
            navLeft = $('.main-navigation .navbar-split-left'),
            navRight = $('.main-navigation .navbar-split-right'),
            additionalMargin = parseInt(wabootHeaderSplitted.margin);

        logocontainer.css('margin-left', (width/2)*-1);

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
}

jQuery(document).ready(function($){
    if(wabootHeaderSplitted.split_enabled){
        var wbhsm = new Waboot_HeaderSplittedMenu();
        wbhsm.split();
        $(window).resize(function() {
            wbhsm.split();
        });
    }
});