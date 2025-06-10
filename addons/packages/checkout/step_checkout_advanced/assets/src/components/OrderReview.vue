<script setup lang="ts">
import {onMounted, ref} from "vue";

const loading = ref(false);
const cartTotal = ref("CHF 0");

onMounted(() => {
    //@ts-ignore
    if(typeof window.jQuery === "undefined"){
        return;
    }
    //@ts-ignore
    const $ = window.jQuery;

    const updateCartTotal = () => {
        const totalElement = $('.woocommerce-checkout-review-order-table .order-total .woocommerce-Price-amount').first();
        if (totalElement.length) {
            cartTotal.value = totalElement.text().trim();
        }
    };

    //const $orderReviewTable = $('.woocommerce-checkout-review-order-table');
    if($('.woocommerce-checkout-review-order-table').length > 0){
        console.log('<OrderReview> Cloning order table');
        $('.woocommerce-checkout-review-order-table').clone().appendTo('[data-order-review-wrapper]');
        updateCartTotal();
    }
    $(document.body).on('updated_checkout', () => {
        console.log('<OrderReview> Updating the order review table');
        loading.value = true;
        setTimeout(() => {
            $('[data-order-review-wrapper]').html('');
            /*
             * .woocommerce-checkout-review-order-table contains the items and totals
             */
            $('.woocommerce-checkout-review-order-table').clone().appendTo('[data-order-review-wrapper]');
            $('[data-order-review-wrapper]').find('.blockOverlay').attr('style', '');
            loading.value = false;
            updateCartTotal();
        }, 1000);
    });

    $('.woocommerce-checkout-steps__order-review-top').on('click', function () {
        $('.woocommerce-checkout-steps__order-review').toggleClass('open');
    });
});
</script>
<template>
    <div id="#order-review__wrapper" class="woocommerce-checkout-steps__order-review" :class="{'loading': loading}">
        <div class="woocommerce-checkout-steps__order-review-top">
            <h3>{{ $t('Order review') }}</h3>
            <button><i class="icon icon-chevron-down"></i></button>

            <strong>{{ cartTotal }}</strong>
        </div>

        <div class="woocommerce-checkout-steps__loader"><span class="loader"></span></div>

        <div data-order-review-wrapper></div>
    </div>
</template>