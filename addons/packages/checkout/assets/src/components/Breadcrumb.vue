<script setup lang="ts">
import {reactive, ref, toRaw} from "vue";
import {useBreadCrumbStore} from "@/stores/breadcrumb";

const breadCrumbStore = useBreadCrumbStore();
</script>
<template>
  <ul class="woocommerce-checkout-steps__list">
    <li :class="{'woocommerce-checkout-steps__item': true, 'woocommerce-checkout-steps__item--done': breadCrumbStore.currentStep <= 3}">
      <a href="#checkout-step-1" class="woocommerce-checkout-steps__link">
        <span class="woocommerce-checkout-steps__number">1</span>
        <span class="woocommerce-checkout-steps__label">Login</span>
      </a>
    </li>
    <li :class="{'woocommerce-checkout-steps__item': true, 'woocommerce-checkout-steps__item--done': breadCrumbStore.currentStep > 1}">
      <a href="#checkout-step-2" class="woocommerce-checkout-steps__link">
        <span class="woocommerce-checkout-steps__number">2</span>
        <span class="woocommerce-checkout-steps__label">Indirizzi</span>
      </a>
    </li>
    <li :class="{'woocommerce-checkout-steps__item': true, 'woocommerce-checkout-steps__item--done': breadCrumbStore.currentStep === 3}">
      <a href="#checkout-step-3" class="woocommerce-checkout-steps__link">
        <span class="woocommerce-checkout-steps__number">3</span>
        <span class="woocommerce-checkout-steps__label">Pagamento</span>
      </a>
    </li>
  </ul>
</template>