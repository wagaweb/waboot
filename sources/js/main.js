jQuery(document).ready(function($) {
    "use strict";
    /*
     * Bootstrapping html elements
     */
    $('input[type=text]').addClass('form-control');
    $('input[type=select]').addClass('form-control');
    $('textarea').addClass('form-control');
    $('select').addClass('form-control');
    $('input#submit').addClass('btn btn-primary');
    $('.gform_button').addClass('btn btn-primary btn-lg').removeClass('gform_button button');
    $('.validation_error').addClass('alert alert-danger').removeClass('validation_error');
    $('.gform_confirmation_wrapper').addClass('alert alert-success').removeClass('gform_confirmation_wrapper');
    // Tables
    $('table').addClass('table');
    /*
     * These will make any element that has data-wbShow\wbHide="<selector>" act has visibily toggle for <selector>
     */
    $('[data-wbShow]').on('click', function() {
        var itemToShow = $($(this).attr("data-trgShow"));
        if (itemToShow.hasClass('modal')) {
            $('.modal').each(function(index) {
                $(this).modal("hide");
            });
            itemToShow.modal("show");
        } else {
            itemToShow.show();
        }
    });
    $('[data-wbHide]').on('click', function() {
        var itemToShow = $($(this).attr("data-trgHide"));
        if (itemToShow.hasClass('modal')) {
            itemToShow.modal("hide");
        } else {
            itemToShow.hide();
        }
    });
    /*
     * INIT CONTACT FORM
     */
    var ContactFormView = require("./views/contactForm.js"),
        ContactFormModel = require("./controllers/contactForm.js"),
        $contactForm = $("[data-contactForm]");
    //Init search windows
    if ($contactForm.length > 0) {
        var contactWindow = new ContactFormView({
            model: new ContactFormModel(),
            el: $contactForm
        });
    }
    /*
     * MOBILE ACTIONS
     */
    if (wbData.isMobile) {
        var fs = require("FastClick"),
            swipe = require("TouchSwipe");
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
        fs.FastClick.attach(document.body);
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
    }
    /*
     * WOOCOMMERCE
     */
    $('.woocommerce a.button').addClass('btn');
    $('.woocommerce a.button').addClass('btn-primary');
    $('.woocommerce a.button').removeClass('button');

});
