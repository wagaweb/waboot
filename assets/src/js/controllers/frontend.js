import * as Backbone from "backbone";
import * as FastClick from "FastClick"; 
import $ from "jquery";

export default class extends Backbone.Model{
    initialize() {
        "use strict";
        this.on_ready();
    }
    on_ready(){
        "use strict";
        /*
         * Bootstrapping html elements
         */
        this.apply_bootstrap();
        
        /*
         * These will make any element that has data-wbShow\wbHide="<selector>" act has visibily toggle for <selector>
         */
        $('[data-wbShow]').on('click', function() {
            let itemToShow = $($(this).attr("data-trgShow"));
            if (itemToShow.hasClass('modal')) {
                $('.modal').each(index => $(this).modal("hide"));
                itemToShow.modal("show");
            } else {
                itemToShow.show();
            }
        });
        $('[data-wbHide]').on('click', function() {
            let itemToShow = $($(this).attr("data-trgHide"));
            if (itemToShow.hasClass('modal')) {
                itemToShow.modal("hide");
            } else {
                itemToShow.hide();
            }
        });
        
        /*
         * MOBILE ACTIONS
         */
        if (wbData.isMobile){
            this.do_mobile_actions();
        }
        
        /*
         * WOOCOMMERCE
         */
        if(typeof wbData !== "undefined" && wbData.has_woocommerce){
            this.do_wc_actions();
        }
        
        //Enabling tab navigation
        /*
        $("[role=tablist] li a").each(function() {
            var self = this;
            $(this).on("click", function(e) {
                e.preventDefault();
                self.tab("show");
            });
        });
        */
        
        $(".nav-tabs li:first-child").addClass("active");
        $(".tab-content .tab-pane:first-child").addClass("active");
    }

    /**
     * Bootstrapping html elements
     */
    apply_bootstrap(){
        "use strict";
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
    }
    
    /**
     * Performs mobile oriented actions
     */
    do_mobile_actions(){
        "use strict";
        let $metaslider = $(".metaslider");
        //let swipe = require("TouchSwipe");
        
        //http://getbootstrap.com/getting-started/#support
        if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
            let msViewportStyle = document.createElement('style');
            msViewportStyle.appendChild(
                document.createTextNode(
                    '@-ms-viewport{width:auto!important}'
                )
            );
            document.querySelector('head').appendChild(msViewportStyle);
        }
        
        if(typeof FastClick !== "undefined"){
            FastClick.attach(document.body);   
        }
        
        /*
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
         */
        
        //Disable for Metaslider
        if(typeof $metaslider !== "undefined"){
            $metaslider.addClass("noSwipe");
        }
    }
    
    /**
     * Performs some adjustment for WooCommerce
     */
    do_wc_actions(){
        "use strict";
        let $wc_container = $('.woocommerce'),
            $wc_cart_table = $wc_container.find("table.cart");
        
        $wc_container.find('a.button').addClass('btn');
        $wc_container.find('a.add_to_cart_button').addClass('btn-success');
        $wc_container.find('a.button').removeClass('button');
        
        $wc_cart_table.addClass('table-striped');
        $wc_cart_table.find('td.actions input.button').addClass('btn').addClass('btn-default').removeClass('button');
    }
}