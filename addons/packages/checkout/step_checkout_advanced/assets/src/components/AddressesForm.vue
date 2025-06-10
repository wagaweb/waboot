<script setup lang="ts">
import {onMounted, type Ref, ref, watch} from 'vue'
import {useCheckoutDataStore} from "@/stores/checkoutData";
import {wcAPI} from "@/services/wp/woocommerce";
import type {addressData, fetchedCountry, userBillingData, userShippingData} from "../../env";
import {debugLog} from "@/utils/helpers/debug.ts";
import {useI18n} from "vue-i18n";
import AddressForm from "@/components/AddressForm.vue";
import {deepClone} from "@/utils/helpers/objects.ts";

const {t} = useI18n();

const emit = defineEmits<{
  (e: 'AddressDataSubmitted', shippingData: userShippingData, billingData: userBillingData): void
}>();

const shippingAddressFormRef = ref<InstanceType<typeof AddressForm> | null>(null);
const shippingData: Ref<addressData|null> = ref(null);
const addressesDataValid: Ref<boolean> = ref(false);

watch(addressesDataValid, () => {
    if(addressesDataValid.value){
        const shippingValues = shippingData.value;
        if(shippingValues){
            const billingValues = deepClone(shippingValues);
            emit('AddressDataSubmitted', shippingValues as userShippingData, billingValues as userBillingData);
        }
    }
});

const checkoutDataStore = useCheckoutDataStore();

const fetchedCountries: Ref<fetchedCountry[]> = ref([]);
const loadingCountriesAndStates = ref(false);

async function fetchCountries() {
  try {
    loadingCountriesAndStates.value = true;
    debugLog('<ShippingAddressesForm> fetchCountries()');
    const countries = await wcAPI.fetchCountries();
    debugLog('<ShippingAddressesForm> fetchCountries() -> response', countries);
    fetchedCountries.value = countries;
    loadingCountriesAndStates.value = false;
  } catch (error) {
    loadingCountriesAndStates.value = false;
    debugLog('<ShippingAddressesForm> fetchCountries() ERROR', error);
  }
}

function checkAddressValidation(){
    if(shippingData.value){
        addressesDataValid.value = true;
    }
}

function onShippingAddressValidated(formData: addressData){
    debugLog('<ShippingAddressesForm> onShippingAddressValidated()');
    shippingData.value = formData;
    checkAddressValidation();
}

function onSubmit(){
    if(shippingAddressFormRef.value){
        shippingAddressFormRef.value.onSubmit();
    }
}

onMounted(() => {
  debugLog('<ShippingAddressesForm> onMounted()');
  fetchCountries();
});
</script>

<template>
  <section class="woocommerce-checkout-steps__content" id="checkout-step-2">
    <div class="checkout woocommerce-checkout">
        <AddressForm :available-countries="fetchedCountries" @address-validated="onShippingAddressValidated" ref="shippingAddressFormRef" />
        <input type="submit" :value="t('Save address')" class="btn btn--primary" @click.prevent="onSubmit">
    </div>
  </section>
</template>

