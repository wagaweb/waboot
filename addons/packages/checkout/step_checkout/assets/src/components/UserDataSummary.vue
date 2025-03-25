<script lang="ts" setup>
import {useCheckoutDataStore} from "@/stores/checkoutData.ts";

const emit = defineEmits<{
    (e: 'editEmail'): void
    (e: 'editBilling'): void
    (e: 'editShipping'): void
}>();

const checkoutDataStore = useCheckoutDataStore();
</script>
<template>
    <div class="woocommerce-checkout-steps__data">
        {{ checkoutDataStore.currentStep }}
        <p v-if="checkoutDataStore.hasEmail" class="form-row__email">
            {{ checkoutDataStore.userEmail }}
            <a
                v-if="!checkoutDataStore.isLoggedIn && checkoutDataStore.currentStep === 'profile'"
                @click.prevent="$emit('editEmail')"
                class="woocommerce-checkout-steps__edit"
                href="#"
            >
                {{ $t('Edit email') }} <i class="fal fa-pencil"></i>
            </a>
        </p>
        <div class="woocommerce-checkout-steps__profile-row" v-if="checkoutDataStore.isBillingDataComplete && checkoutDataStore.currentStep !== 'email'">
            <div class="woocommerce-checkout-steps__profile-col">
                <h5>{{ $t('Account') }}</h5>

                <ul>
                    <li>{{ $t('Account type') }}: {{ checkoutDataStore.billingData.profileType }}</li>
                </ul>

                <ul>
                    <li>{{ checkoutDataStore.billingData.firstName }} {{ checkoutDataStore.billingData.lastName }}</li>
                    <li>{{ checkoutDataStore.birthdayString }}</li>
                    <li>{{ checkoutDataStore.billingData.phone }}</li>
                </ul>
            </div>
            <div class="billing-addresses">
                <div class="woocommerce-checkout-steps__profile-col">
                    <h5>{{ $t('Billing data') }}</h5>
                    <ul>
                        <li>{{ checkoutDataStore.billingData.address1 }}</li>
                        <li>{{ checkoutDataStore.billingData.postcode }} {{ checkoutDataStore.billingData.city }}
                            {{ checkoutDataStore.billingData.state }} {{ checkoutDataStore.billingData.country }}
                        </li>
                    </ul>
                </div>
            </div>
            <a
                v-if="!checkoutDataStore.isLoggedIn && checkoutDataStore.currentStep !== 'profile'"
                @click.prevent="$emit('editBilling')"
                class="woocommerce-checkout-steps__edit"
                href="#"
            >
                {{ $t('Edit billing') }} <i class="fal fa-pencil"></i>
            </a>
        </div>
        <div v-if="checkoutDataStore.currentStep == 'pay' && checkoutDataStore.isShippingDataComplete">
          <h4>{{ $t('Shipping') }}</h4>
          <div class="woocommerce-checkout-steps__profile-col">
            <h5>{{ $t('Shipping Data') }}</h5>
            <ul>
              <li>{{ checkoutDataStore.shippingData.firstName }} </li>
              <li>{{ checkoutDataStore.shippingData.address1 }}</li>
              <li>{{ checkoutDataStore.shippingData.address2 }}</li>
              <li>{{ checkoutDataStore.shippingData.postcode }} {{ checkoutDataStore.shippingData.city }} {{ checkoutDataStore.shippingData.state }} </li>
            </ul>
            <a class="woocommerce-checkout-steps__edit" href="#" @click.prevent="$emit('editShipping')">{{ $t('Edit shipping') }} <i
                class="fal fa-pencil"></i></a>
          </div>
        </div>
    </div>
</template>