<script lang="ts" setup>
import {useCheckoutDataStore} from "@/stores/checkoutData.ts";
import {computed} from "vue";

const emit = defineEmits<{
  (e: 'editEmail'): void
  (e: 'editBilling'): void
  (e: 'editShipping'): void
}>();

const checkoutDataStore = useCheckoutDataStore();

const isAddressSame = computed(() => {
  const {shippingData, billingData} = checkoutDataStore;
  return shippingData.address1 === billingData.address1 &&
      shippingData.address2 === billingData.address2 &&
      shippingData.postcode === billingData.postcode &&
      shippingData.city === billingData.city &&
      shippingData.state === billingData.state;
});
</script>
<template>
  <div class="woocommerce-checkout-steps__data">
    <div class="woocommerce-checkout-steps__row">
      <h5>{{ $t('Contact info') }}</h5>
      <a
          v-if="checkoutDataStore.currentStep != 'email'"
          @click.prevent="$emit('editEmail')"
          class="woocommerce-checkout-steps__edit"
          href="#"
      >
        {{ $t('Edit') }} <i class="icon icon-chevron-right"></i>
      </a>
    </div>

    <p v-if="checkoutDataStore.hasEmail" class="form-row__email">
      {{ checkoutDataStore.userEmail }}
    </p>
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

<!--    <div class="woocommerce-checkout-steps__shipping-footer" v-if="isAddressSame">
      <p class="panel">{{ $t('The delivery address and billing address are the same.') }}</p>
    </div>-->
  </div>
</template>