const $ = window.jQuery;

const updateCartTotal = () => {
    console.log('<order-review-manager.js> updateCartTotal()');
    const totalElement = $('.woocommerce-checkout-review-order-table .order-total .woocommerce-Price-amount').first();
    if (totalElement.length) {
        $('[data-cart-total]').html(totalElement.text().trim())
    }
};

$(document).ready(function(){
    //const $orderReviewTable = $('.woocommerce-checkout-review-order-table');
    if($('.woocommerce-checkout-review-order-table').length > 0){
        console.log('<order-review-manager.js> Cloning order table');
        $('.woocommerce-checkout-review-order-table').clone().appendTo('[data-order-review-wrapper]');
        updateCartTotal();
    }

    $(document.body).on('updated_checkout', () => {
        console.log('<order-review-manager.js> Updating the order review table');
        $('#order-review__wrapper').addClass('loading');
        setTimeout(() => {
            $('[data-order-review-wrapper]').html('');
            /*
             * .woocommerce-checkout-review-order-table contains the items and totals
             */
            $('.woocommerce-checkout-review-order-table').clone().appendTo('[data-order-review-wrapper]');
            $('[data-order-review-wrapper]').find('.blockOverlay').attr('style', '');
            $('#order-review__wrapper').removeClass('loading');
            updateCartTotal();
        }, 1000);
    });

    $('.woocommerce-checkout-steps__order-review-top').on('click', function () {
        $('.woocommerce-checkout-steps__order-review').toggleClass('open');
    });
});