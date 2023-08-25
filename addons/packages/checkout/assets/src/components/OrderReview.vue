<script setup lang="ts">
import {onMounted, ref} from "vue";

    const loading = ref(false);

    onMounted(() => {
        //@ts-ignore
        if(typeof window.jQuery === "undefined"){
            return;
        }
        //@ts-ignore
        const $ = window.jQuery;
        const $orderReviewTable = $('.woocommerce-checkout-review-order-table');
        if($orderReviewTable.length > 0){
            console.log('Cloning order table');
            $orderReviewTable.clone().appendTo('[data-order-review-wrapper]');
        }
        $(document.body).on('updated_checkout', () => {
            console.log('Updating the order review table');
            loading.value = true;
            setTimeout(() => {
                $('[data-order-review-wrapper]').html('');
                $orderReviewTable.clone().appendTo('[data-order-review-wrapper]');
                $('[data-order-review-wrapper]').find('.blockOverlay').attr('style', '');
                loading.value = false;
            }, 1000);
        });
    });
</script>
<template>
    <div id="#order-review__wrapper" class="woocommerce-checkout-steps__order-review" :class="{'loading': loading}">
      <h3>Riepilogo Ordine</h3>
      <div class="woocommerce-checkout-steps__loader"><span class="loader"></span></div>
      <div data-order-review-wrapper></div>
    </div>
</template>