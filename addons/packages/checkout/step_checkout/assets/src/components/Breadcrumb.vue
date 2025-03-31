<script setup lang="ts">
import {useBreadCrumbStore} from "@/stores/breadcrumb";

const breadCrumbStore = useBreadCrumbStore();
</script>
<template>
  <ul class="woocommerce-checkout-steps__list">
    <li class="woocommerce-checkout-steps__item"
        :class="{'woocommerce-checkout-steps__item--current': breadCrumbStore.currentStep === 1, 'woocommerce-checkout-steps__item--done': breadCrumbStore.currentStep <= 3}">
      <div class="woocommerce-checkout-steps__link">
        <span class="woocommerce-checkout-steps__label">{{ $t('Login') }}</span>
      </div>
    </li>
    <li class="woocommerce-checkout-steps__item"
        :class="{'woocommerce-checkout-steps__item--current': breadCrumbStore.currentStep === 2, 'woocommerce-checkout-steps__item--done': breadCrumbStore.currentStep > 1}">
      <div class="woocommerce-checkout-steps__link">
        <span class="woocommerce-checkout-steps__label">{{ $t('Shipping') }}</span>
      </div>
    </li>
    <li class="woocommerce-checkout-steps__item"
        :class="{'woocommerce-checkout-steps__item--current': breadCrumbStore.currentStep === 3, 'woocommerce-checkout-steps__item--done': breadCrumbStore.currentStep === 3}">
      <div class="woocommerce-checkout-steps__link">
        <span class="woocommerce-checkout-steps__label">{{ $t('Payment') }}</span>
      </div>
    </li>
  </ul>
</template>
<style lang="scss">
.woocommerce-checkout-steps__item--current {
  div > span {
    font-weight: bold;
  }

  a > span {
    font-weight: bold;
  }
}
</style>