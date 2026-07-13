<script setup lang="ts">
import {useBreadCrumbStore} from "@/stores/breadcrumb";
import {computed} from "vue";

const breadCrumbStore = useBreadCrumbStore();

const currentActiveStep = computed(() => {
    switch(breadCrumbStore.currentStep){
        case 1:
        case 2:
            return 'login';
        case 3:
            return 'shipping'
        case 4:
            return 'payment'
    }
});

</script>
<template>
  <ul class="woocommerce-checkout-steps__list">
    <li class="woocommerce-checkout-steps__item"
        :class="{'woocommerce-checkout-steps__item--current': currentActiveStep === 'login', 'woocommerce-checkout-steps__item--done': currentActiveStep !== 'login' }">
      <div class="woocommerce-checkout-steps__link">
        <span class="woocommerce-checkout-steps__label">{{ $t('Login') }}</span>
      </div>
    </li>
    <li class="woocommerce-checkout-steps__item"
        :class="{'woocommerce-checkout-steps__item--current': currentActiveStep === 'shipping', 'woocommerce-checkout-steps__item--done': currentActiveStep !== 'shipping'}">
      <div class="woocommerce-checkout-steps__link">
        <span class="woocommerce-checkout-steps__label">{{ $t('Shipping') }}</span>
      </div>
    </li>
    <li class="woocommerce-checkout-steps__item"
        :class="{'woocommerce-checkout-steps__item--current': currentActiveStep === 'payment', 'woocommerce-checkout-steps__item--done': currentActiveStep !== 'payment'}">
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