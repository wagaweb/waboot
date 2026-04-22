<script setup lang="ts">
import {computed, onMounted, type Ref, ref, watch} from 'vue'
import {useCheckoutDataStore} from "@/stores/checkoutData";
import {wcAPI} from "@/services/wp/woocommerce";
import type {addressData, fetchedCountry, userShippingDataWP} from "../../env";
import {debugLog} from "@/utils/helpers/debug.ts";
import {useI18n} from "vue-i18n";
import AddressForm from "@/components/AddressForm.vue";
import {deepClone} from "@/utils/helpers/objects.ts";
import {wpUserAPI} from "@/services/wp/user.ts";
import {getBackEndData} from "@/services/wp/backendData.ts";
import BillingDataStep from "@/components/BillingDataStep.vue";
import ShippingDataStep from "@/components/ShippingDataStep.vue";

const {t} = useI18n();

const emit = defineEmits<{
    (e: 'AddressDataSubmitted', shippingData: addressData, billingData: addressData, isGuest: boolean): void
}>();

const billingDataFirst = getBackEndData().billing_data_first;


const billingAddressFormRef = ref<InstanceType<typeof AddressForm> | null>(null);

const billingStepRef = ref<InstanceType<typeof BillingDataStep> | null>(null);

const shippingData: Ref<addressData|null> = ref(null);
const billingData: Ref<addressData|null> = ref(null);
const addressesDataValid: Ref<boolean> = ref(false);

const displayShippingStep = ref(false);

const checkoutDataStore = useCheckoutDataStore();

const fetchedCountries: Ref<fetchedCountry[]> = ref([]);
const loadingCountriesAndStates = ref(false);

const loadingAddresses = ref(true);
const availableShippingAddresses = ref<userShippingDataWP[]>([]);
const addingNewAddress = ref(false);
const addressSelected = computed(() => {
    return typeof checkoutDataStore.selectedAddressIndex !== 'undefined';
});

const billingAddressEnabled = ref(false);
const shippingAddressEnabled = ref(false);
const customerAccountCreationEnabled = ref(false);
const showCreateAnAccountCheckbox = computed(() => {
    let toShow = true;
    if(getBackEndData().use_proceed_as_guest){
        toShow = false;
    }else{
        if(checkoutDataStore.isLoggedIn){
            toShow = false;
        }
    }
    return toShow;
});
const isGuest = computed(() => {
    return checkoutDataStore.wpProfileFound === false && customerAccountCreationEnabled.value === false;
});

/*
 * If the user clicked on "Proceed" but no account was found, AND the proceed as guest was enabled, so force the customer creation to true
 * (because the user chose to not being a guest on purpose by non clicking on "proceed as guest")
 */
function toggleCustomerAccountCreationEnabled(){
    if(!checkoutDataStore.continueAsGuest && !checkoutDataStore.wpProfileFound && getBackEndData().use_proceed_as_guest){
        customerAccountCreationEnabled.value = true;
    }
}

/*
 * We need both shipping and billing fields enabled on the original (hidden) form
 */
function enableShipToDifferentAddress() {
    //@ts-ignore
    const $ = window.jQuery;
    let $originalForm = $('#original-form-wrapper');
    if ($originalForm !== "undefined" && $originalForm.length > 0) {
        $('[name=ship_to_different_address]').attr('checked', true);
        $('[name=ship_to_different_address]').trigger('change');
    }
}

/*
 * Fetch available countries
 */
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


/*
 * Callback for @address-validated of <AddressForm>
 * This event is emitted when <AddressForm> "onSubmit()" function pass the validations
 */
function onShippingAddressValidated(formData: addressData){
    debugLog('<ShippingAddressesForm> onShippingAddressValidated()',formData);
    shippingData.value = formData;
    checkAddressValidation();
}

/*
 * Callback for @address-validated of <AddressForm>
 * This event is emitted when <AddressForm> "onSubmit()" function pass the validations
 */
function onBillingAddressValidated(formData: addressData){
    debugLog('<AddressesForm> onBillingAddressValidated()',formData);
    billingData.value = formData;
    if(billingDataFirst.value){
        if(!billingStepRef.value.useBillingForShipping){
            displayShippingStep.value = true;
        }else{
            const billingDataForShipping = deepClone(formData);
            shippingData.value = billingDataForShipping;
        }
    }else{
        checkAddressValidation();
    }
}

/*
 * This functions checks if <AddressesForm> component should fire
 * its @address-data-submitted event to proceed with the checkout.
 */
function checkAddressValidation(){
    debugLog('<ShippingAddressesForm> checkAddressValidation()');
    debugLog('<ShippingAddressesForm> checkAddressValidation() -> shippingData', shippingData.value);
    debugLog('<ShippingAddressesForm> checkAddressValidation() -> billingData', billingData.value);
    if(shippingData.value && billingData.value){
        addressesDataValid.value = true; // This value is being watched by watch()
    }
}

watch(addressesDataValid, () => {
    if(!addressesDataValid){
        return;
    }
    const shippingValues = shippingData.value;
    const billingValues = billingData.value;
    checkoutDataStore.useDifferentAddressForBillingChecked = billingAddressEnabled.value; // Need these for restoring
    checkoutDataStore.createAnAccountChecked = customerAccountCreationEnabled.value;
    emit('AddressDataSubmitted', shippingValues as addressData, billingValues as addressData, isGuest.value);
});

onMounted(async () => {
    debugLog('<AddressesForm> onMounted()');
    await fetchCountries();
    enableShipToDifferentAddress();
    toggleCustomerAccountCreationEnabled();
    if(checkoutDataStore.mustRestoreAddressData){ // This is set by the edit link handler
        if(!checkoutDataStore.selectedAddressIndex){
            // If no address selected before, pre-compile the checkboxes and open the new address window
            billingAddressEnabled.value = checkoutDataStore.useDifferentAddressForBillingChecked;
            customerAccountCreationEnabled.value = checkoutDataStore.createAnAccountChecked;
            addingNewAddress.value = true;
        }
        if(checkoutDataStore.userChoseToEditBilling){
            billingAddressEnabled.value = true;
        }
    }
});
</script>

<template>
    <section class="woocommerce-checkout-steps__content" id="checkout-step-2">
        <template v-if="billingDataFirst">
            <BillingDataStep
                ref="billingStepRef"
                :available-countries="fetchedCountries"
                :initial-form-data="checkoutDataStore.mustRestoreAddressData ? checkoutDataStore.billingData : null"
                :show-create-an-account-checkbox="showCreateAnAccountCheckbox"
                @address-validated="onBillingAddressValidated"
            />
        </template>
        <template v-else>
            <ShippingDataStep
                :available-countries="fetchedCountries"
                :initial-shipping-form-data="checkoutDataStore.mustRestoreAddressData ? checkoutDataStore.shippingData : null"
                :initial-billing-form-data="checkoutDataStore.mustRestoreAddressData ? checkoutDataStore.billing : null"
                :show-create-an-account-checkbox="showCreateAnAccountCheckbox"
                :adding-new-address="addingNewAddress"
                :selected-address-index="checkoutDataStore.selectedAddressIndex"
                :billing-address-enabled = billingAddressEnabled
                @address-validated="onShippingAddressValidated"
            />
        </template>
    </section>
</template>

