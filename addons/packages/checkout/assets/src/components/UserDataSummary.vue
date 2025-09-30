<script lang="ts" setup>
import {useCheckoutDataStore} from "@/stores/checkoutData";
import {computed, type ComputedRef} from "vue";

const emit = defineEmits<{
    (e: 'editEmail'): void
    (e: 'editBilling'): void
    (e: 'editShipping'): void
}>();

const checkoutDataStore = useCheckoutDataStore();

const mustShowBillingInfo: ComputedRef<boolean> = computed(() => {
    if(!checkoutDataStore.hasBillingData){
        return false;
    }
    if(checkoutDataStore.userChoseToEditBilling){
        return false;
    }
    return checkoutDataStore.currentStep == 'pay';
});

const mustShowShippingInfo: ComputedRef<boolean> = computed(() => {
    return checkoutDataStore.currentStep == 'pay' && checkoutDataStore.isShippingDataComplete;
});

const canEditEmail: ComputedRef<boolean> = computed(() => {
    return checkoutDataStore.currentStep != 'email' && !checkoutDataStore.isLoggedIn;
});

const canEditBillingInfo: ComputedRef<boolean> = computed(() => {
    return checkoutDataStore.currentStep === 'pay' && !checkoutDataStore.isLoggedIn;
});


</script>
<template>
    <div class="woocommerce-checkout-steps__data">
        <div class="woocommerce-checkout-steps__row">
            <h4>{{ $t('Contact info') }}</h4>
            <a v-if="canEditEmail" @click.prevent="$emit('editEmail')" class="woocommerce-checkout-steps__edit" href="#">{{ $t('Edit') }} <i class="icon icon-chevron-right"></i></a>
        </div>
        <p v-if="checkoutDataStore.hasEmail" class="form-row__email">
            {{ checkoutDataStore.userEmail }}
        </p>
        <template v-if="mustShowBillingInfo">
            <div class="woocommerce-checkout-steps__row">
                <h5>{{ $t('Billing info') }}</h5>
                <a v-if="canEditBillingInfo" @click.prevent="$emit('editBilling')" class="woocommerce-checkout-steps__edit" href="#">{{ $t('Edit') }} <i class="icon icon-chevron-right"></i></a>
            </div>
            <ul>
                <li>{{ checkoutDataStore.billingData.firstName }} {{ checkoutDataStore.billingData.lastName }}</li>
                <li>{{ checkoutDataStore.billingData.address1 }}</li>
                <li>{{ checkoutDataStore.billingData.address2 }}</li>
                <li>{{ checkoutDataStore.billingData.postcode }} {{ checkoutDataStore.shippingData.city }} {{ checkoutDataStore.billingData.state }}</li>
            </ul>
        </template>
    </div>

    <div class="woocommerce-checkout-steps__shipping" v-if="mustShowShippingInfo">
        <div class="woocommerce-checkout-steps__profile-col">
            <div class="woocommerce-checkout-steps__row">
                <h5>{{ $t('Address') }}</h5>
                <a class="woocommerce-checkout-steps__edit" href="#" @click.prevent="$emit('editShipping')">{{ $t('Edit') }} <i class="icon icon-chevron-right"></i></a>
            </div>
            <ul>
                <li>{{ checkoutDataStore.shippingData.firstName }} {{ checkoutDataStore.shippingData.lastName }}</li>
                <li>{{ checkoutDataStore.shippingData.address1 }}</li>
                <li>{{ checkoutDataStore.shippingData.address2 }}</li>
                <li>{{ checkoutDataStore.shippingData.postcode }} {{ checkoutDataStore.shippingData.city }} {{ checkoutDataStore.shippingData.state }}</li>
            </ul>
        </div>
    </div>
</template>
