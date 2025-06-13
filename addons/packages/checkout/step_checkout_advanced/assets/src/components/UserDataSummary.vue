<script lang="ts" setup>
import {useCheckoutDataStore} from "@/stores/checkoutData.ts";
import {computed} from "vue";

const emit = defineEmits<{
    (e: 'editEmail'): void
    (e: 'editBilling'): void
    (e: 'editShipping'): void
}>();

const checkoutDataStore = useCheckoutDataStore();

</script>
<template>
    <div class="woocommerce-checkout-steps__data">
        <div class="woocommerce-checkout-steps__row">
            <h5>{{ $t('Contact info') }}</h5>
            <a v-if="checkoutDataStore.currentStep != 'email' && !checkoutDataStore.isLoggedIn" @click.prevent="$emit('editEmail')" class="woocommerce-checkout-steps__edit" href="#">{{ $t('Edit') }} <i class="icon icon-chevron-right"></i></a>
        </div>
        <p v-if="checkoutDataStore.hasEmail" class="form-row__email">
            {{ checkoutDataStore.userEmail }}
        </p>
        <template v-if="checkoutDataStore.hasBillingData">
            <div class="woocommerce-checkout-steps__row">
                <h5>{{ $t('Billing info') }}</h5>
                <a v-if="checkoutDataStore.currentStep === 'pay' && !checkoutDataStore.isLoggedIn" @click.prevent="$emit('editBilling')" class="woocommerce-checkout-steps__edit" href="#">{{ $t('Edit') }} <i class="icon icon-chevron-right"></i></a>
            </div>
            <ul>
                <li>{{ checkoutDataStore.billingData.firstName }}</li>
                <li>{{ checkoutDataStore.billingData.address1 }}</li>
                <li>{{ checkoutDataStore.billingData.address2 }}</li>
                <li>{{ checkoutDataStore.billingData.postcode }} {{ checkoutDataStore.shippingData.city }}
                    {{ checkoutDataStore.billingData.state }}
                </li>
            </ul>
        </template>
    </div>

    <div class="woocommerce-checkout-steps__shipping" v-if="checkoutDataStore.currentStep == 'pay' && checkoutDataStore.isShippingDataComplete">
        <div class="woocommerce-checkout-steps__profile-col">
            <div class="woocommerce-checkout-steps__row">
                <h5>{{ $t('Address') }}</h5>
                <a class="woocommerce-checkout-steps__edit btn btn--link" href="#"
                   @click.prevent="$emit('editShipping')">{{ $t('Edit') }} <i
                    class="icon icon-chevron-right"></i></a>
            </div>
            <ul>
                <li>{{ checkoutDataStore.shippingData.firstName }}</li>
                <li>{{ checkoutDataStore.shippingData.address1 }}</li>
                <li>{{ checkoutDataStore.shippingData.address2 }}</li>
                <li>{{ checkoutDataStore.shippingData.postcode }} {{ checkoutDataStore.shippingData.city }}
                    {{ checkoutDataStore.shippingData.state }}
                </li>
            </ul>
        </div>
    </div>
</template>