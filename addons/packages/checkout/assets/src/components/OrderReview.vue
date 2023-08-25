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
                loading.value = false;
            }, 1000);
        });
    });
</script>
<template>
    <div id="#order-review__wrapper" data-order-review-wrapper :class="{'loading': loading}">

    </div>
</template>

<style scoped lang="css">
    [data-order-review-wrapper].loading{
        background-color: grey;
    }
</style>