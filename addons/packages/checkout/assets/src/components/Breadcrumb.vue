<script setup lang="ts">
import {reactive, ref, toRaw} from "vue";
import {useBreadCrumbStore} from "@/stores/breadcrumb";

const breadCrumbStore = useBreadCrumbStore();
</script>
<template>
  <ul class="woocommerce-checkout-steps__list">
    <li :class="{'woocommerce-checkout-steps__item': true, 'woocommerce-checkout-steps__item--done': breadCrumbStore.currentStep <= 3}">
      <div class="woocommerce-checkout-steps__link">
        <span class="woocommerce-checkout-steps__number">1</span>
        <span class="woocommerce-checkout-steps__label">Login</span>
      </div>
    </li>
    <li :class="{'woocommerce-checkout-steps__item': true, 'woocommerce-checkout-steps__item--done': breadCrumbStore.currentStep > 1}">
      <div class="woocommerce-checkout-steps__link">
        <span class="woocommerce-checkout-steps__number">2</span>
        <span class="woocommerce-checkout-steps__label">Indirizzi</span>
      </div>
    </li>
    <div :class="{'woocommerce-checkout-steps__item': true, 'woocommerce-checkout-steps__item--done': breadCrumbStore.currentStep === 3}">
      <a class="woocommerce-checkout-steps__link">
        <span class="woocommerce-checkout-steps__number">3</span>
        <span class="woocommerce-checkout-steps__label">Pagamento</span>
      </a>
    </div>
  </ul>
</template>