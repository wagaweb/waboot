<script setup lang="ts">
import {useCheckoutDataStore} from "@/stores/checkoutData";
import {onBeforeUnmount, onMounted} from "vue";
import {debugLog} from "@/utils/helpers/debug.ts";
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const checkoutDataStore = useCheckoutDataStore();

function moveOriginalFormIntoApp() {
    debugLog('<Pay> moveOriginalFormIntoApp()');
    //@ts-ignore
    if (typeof window.jQuery === "undefined") {
        return;
    }
    //@ts-ignore
    const $ = window.jQuery;
    let $originalFormWrapper = $('#original-form-wrapper');
    let $paymentWrapper = $('#payment-wrapper');
    if ($originalFormWrapper !== "undefined" && $originalFormWrapper.length > 0) {
        const $originalForm = $('#original-form-wrapper').find('form');
        if ($originalForm.length > 0) {
            debugLog('<Pay> moveOriginalFormIntoApp() -> moving');
            $originalForm.appendTo('#payment-wrapper');
        }
    }
}

function resetOriginalFormPosition() {
    debugLog('<Pay> resetOriginalFormPosition()');
    //@ts-ignore
    if (typeof window.jQuery === "undefined") {
        return;
    }
    //@ts-ignore
    const $ = window.jQuery;
    let $originalForm = $('#original-form-wrapper');
    let $paymentWrapper = $('#payment-wrapper');
    if ($originalForm !== "undefined" && $originalForm.length > 0) {
        debugLog('<Pay> resetOriginalFormPosition() -> restoring');
        $paymentWrapper.find('form').appendTo('#original-form-wrapper');
    }
}

function placeOrder() {
    //@ts-ignore
    if (typeof window.jQuery === "undefined") {
        return;
    }
    //@ts-ignore
    const $ = window.jQuery;
    let $paymentWrapper = $('#payment-wrapper');
    $paymentWrapper.find('form').submit();
}

onMounted(() => {
    debugLog('<Pay> onMounted()');
    moveOriginalFormIntoApp();
});

onBeforeUnmount(() => {
    debugLog('<Pay> onBeforeUnmount()');
    resetOriginalFormPosition();
})

</script>
<template>
    <div class="woocommerce-checkout-steps__payment">
        <h5>{{ $t('Payment') }}</h5>
        <div id="payment-wrapper"></div>
        <div class="checkout woocommerce-checkout" @click.prevent="placeOrder">
            <input type="submit" :value="t('Place order')" class="btn btn--primary">
        </div>
    </div>
</template>
<style lang="scss">
    #payment-wrapper{
        .woocommerce-form-coupon__wrapper{
            display: block !important;
        }
    }
    form {
        #customer_details,
        #order_review_heading,
        .woocommerce-checkout-review-order-table {
            display: none;
        }

        #payment {
            .wc_payment_methods {
                display: block !important;
            }
            .woocommerce-terms-and-conditions-wrapper{
                display: block !important;
            }
            [name="woocommerce_checkout_place_order"]{
                display: none !important;
            }
        }
    }
</style>