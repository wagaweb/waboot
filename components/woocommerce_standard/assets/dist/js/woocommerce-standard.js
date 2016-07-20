jQuery("document").ready(function($){
    var $wc = $(".woocommerce"),
        $wc_cart = $("table.cart");

    $wc.find('a.button').addClass('btn');
    $wc.find('a.add_to_cart_button').removeClass('btn-primary');
    $wc.find('.single_add_to_cart_button').removeClass('btn-primary');
    $wc.find('a.add_to_cart_button').addClass('btn-success');
    $wc.find('.single_add_to_cart_button').addClass('btn-success');
    $wc.find('a.button').removeClass('button');

    $wc_cart.addClass('table-striped');
    $wc_cart.find('td.actions input.button').addClass('btn');
    $wc_cart.find('td.actions input.button').addClass('btn-default');
    $wc.find('table.cart td.actions input.button').removeClass('button');

    $('.wc-proceed-to-checkout a').addClass('btn btn-lg btn-primary');

    $(".nav-tabs li:first-child").addClass("active");

    $(".tab-content .tab-pane:first-child").addClass("active");

    $( document.body ).on( 'updated_checkout', function(){
        $('.woocommerce-checkout .woocommerce-checkout-review-order-table').addClass('table');
        $('.woocommerce-checkout-payment input[type=submit]').addClass('btn btn-lg btn-primary');
    });
});
