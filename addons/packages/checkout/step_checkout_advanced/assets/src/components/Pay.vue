<script setup lang="ts">
import {useCheckoutDataStore} from "@/stores/checkoutData";
import {onBeforeUnmount, onMounted} from "vue";
import {debugLog} from "@/utils/helpers/debug.ts";
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const checkoutDataStore = useCheckoutDataStore();

function showOriginalFormPayment() {
    debugLog('<Pay> showOriginalFormPayment()');
    //@ts-ignore
    if (typeof window.jQuery === "undefined") {
        return;
    }
    //@ts-ignore
    const $ = window.jQuery;
    let $originalFormWrapper = $('#original-form-wrapper');
    if ($originalFormWrapper !== "undefined" && $originalFormWrapper.length > 0) {
        $originalFormWrapper.find('#customer_details').hide();
        $originalFormWrapper.find('#order_review_heading').hide();
        $originalFormWrapper.find('#order_review').find('.woocommerce-checkout-review-order-table').hide();
        $originalFormWrapper.find('#order_review').find('#payment').show();
        $('[data-order-review-wrapper]').find('.woocommerce-checkout-review-order-table').show();
        $originalFormWrapper.css('display','grid');
    }
}

function resetOriginalFormVisibility(){
    debugLog('<Pay> resetOriginalFormVisibility()');
    //@ts-ignore
    if (typeof window.jQuery === "undefined") {
        return;
    }
    //@ts-ignore
    const $ = window.jQuery;
    let $originalFormWrapper = $('#original-form-wrapper');
    if ($originalFormWrapper !== "undefined" && $originalFormWrapper.length > 0) {
        $originalFormWrapper.find('#customer_details').show();
        $originalFormWrapper.find('#order_review_heading').show();
        $originalFormWrapper.find('#order_review').find('.woocommerce-checkout-review-order-table').show();
        $originalFormWrapper.find('#order_review').find('#payment').show();
        $('[data-order-review-wrapper]').find('.woocommerce-checkout-review-order-table').show();
        $originalFormWrapper.hide();
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
    showOriginalFormPayment();
});

onBeforeUnmount(() => {
    debugLog('<Pay> onBeforeUnmount()');
    resetOriginalFormVisibility();
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
            .woocommerce-terms-and-conditions-wrapper {
                display: block !important;
                label {
                  font-size: 13px;
                  font-weight: 500;
                }
                a {
                  color: inherit;
                  text-decoration: underline;
                }
            }
            .woocommerce-validated label {
              font-size: 13px;
            }
            [name='woocommerce_checkout_place_order'] {
                display: none !important;
            }
        }
    }
}
</style>