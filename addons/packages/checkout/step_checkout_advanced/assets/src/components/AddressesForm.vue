<script setup lang="ts">
import {computed, onMounted, type Ref, ref, watch} from 'vue'
import {useCheckoutDataStore} from "@/stores/checkoutData";
import {wcAPI} from "@/services/wp/woocommerce";
import type {addressData, fetchedCountry, userBillingData, userShippingData, userShippingDataWP} from "../../env";
import {debugLog} from "@/utils/helpers/debug.ts";
import {useI18n} from "vue-i18n";
import AddressForm from "@/components/AddressForm.vue";
import {deepClone} from "@/utils/helpers/objects.ts";
import {wpUserAPI} from "@/services/wp/user.ts";

const {t} = useI18n();

const emit = defineEmits<{
    (e: 'AddressDataSubmitted', shippingData: userShippingData, billingData: userBillingData, isGuest: boolean): void
}>();

const shippingAddressFormRef = ref<InstanceType<typeof AddressForm> | null>(null);
const billingAddressFormRef = ref<InstanceType<typeof AddressForm> | null>(null);
const shippingData: Ref<addressData|null> = ref(null);
const billingData: Ref<addressData|null> = ref(null);
const addressesDataValid: Ref<boolean> = ref(false);

watch(addressesDataValid, () => {
    if(addressesDataValid.value){
        const shippingValues = shippingData.value;
        if(shippingValues){
            const billingValues = billingAddressEnabled.value ? billingData.value : deepClone(shippingValues);
            emit('AddressDataSubmitted', shippingValues as userShippingData, billingValues as userBillingData, isGuest.value);
        }
    }
});

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
const customerAccountCreationEnabled = ref(false);
const isGuest = computed(() => {
    return checkoutDataStore.wpProfileFound === false && customerAccountCreationEnabled.value === false;
});

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
 * Fetch available shipping addresses
 */
async function fetchShippingAddresses() {
    try {
        if (!checkoutDataStore.isLoggedIn || !checkoutDataStore.currentUserId) {
            loadingAddresses.value = false;
            addingNewAddress.value = true;
            return;
        }
        loadingAddresses.value = true;
        const addresses = await wpUserAPI.fetchShippingAddresses(checkoutDataStore.currentUserId);
        debugLog('<ShippingAddressesForm> fetchShippingAddresses()');
        loadingAddresses.value = false;
        debugLog('<ShippingAddressesForm> fetchShippingAddresses() -> response', addresses);
        availableShippingAddresses.value = addresses;
        if (availableShippingAddresses.value.length === 0) {
            addingNewAddress.value = true;
        }
    } catch (error) {
        loadingAddresses.value = false;
        debugLog('<ShippingAddressesForm> fetchShippingAddresses() ERROR', error);
    }
}

/*
 * This functions checks if <AddressesForm> component should fire
 * its @address-data-submitted event to proceed with the checkout.
 */
function checkAddressValidation(){
    debugLog('<ShippingAddressesForm> checkAddressValidation()');
    debugLog('<ShippingAddressesForm> checkAddressValidation() -> shippingData', shippingData.value);
    if(billingAddressEnabled.value){
        if(shippingData.value && billingData.value){
            addressesDataValid.value = true; // This value is being watched by watch()
        }
    }else{
        if(shippingData.value){
            addressesDataValid.value = true; // This value is being watched by watch()
        }
    }
}

/*
 * The user chooses a saved shipping address
 */
function selectShippingAddress(index: number) {
    debugLog('<ShippingAddressesForm> selectShippingAddress()',index);
    if (availableShippingAddresses.value.length <= 0) {
        return;
    }
    if (typeof availableShippingAddresses.value[index] === 'undefined') {
        return;
    }
    const selectedAddress = {...availableShippingAddresses.value[index]};
    checkoutDataStore.selectedAddressIndex = index;
    addingNewAddress.value = false;
    if(shippingAddressFormRef.value){
        shippingAddressFormRef.value.populateFormData(selectedAddress as addressData);
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
    debugLog('<ShippingAddressesForm> onBillingAddressValidated()',formData);
    billingData.value = formData;
    checkAddressValidation();
}

/*
 * User chooses to add a new shipping address
 */
function onAddNewShippingAddress() {
    debugLog('<ShippingAddressesForm> onAddNewShippingAddress()');
    addingNewAddress.value = true;
    checkoutDataStore.selectedAddressIndex = undefined;
    if(shippingAddressFormRef.value){
        shippingAddressFormRef.value.resetFormData();
    }
}

/*
 * User chooses to close the address form window
 */
function onNewShippingAddressFormClose() {
    debugLog('<ShippingAddressesForm> onNewShippingAddressFormClose()');
    addingNewAddress.value = false;
}

/*
 * Addresses forms submit (shipping and billing)
 * Let's call <AddressForm> "onSubmit()" to validated. If the values are valid, <AddressForm>
 * will fire @address-validated event.
 */
function onSubmit(){
    debugLog('<ShippingAddressesForm> onSubmit()');
    if(shippingAddressFormRef.value){
        shippingAddressFormRef.value.onSubmit();
        if(billingAddressEnabled.value && billingAddressFormRef.value){
            billingAddressFormRef.value.onSubmit();
        }
    }
}

/*
 * User selects and confirms a shipping address between those available
 */
function onConfirmSelectedAddress() {
    debugLog('<ShippingAddressesForm> confirmSelectedAddress()');
    if(checkoutDataStore.selectedAddressIndex === undefined || checkoutDataStore.selectedAddressIndex === null){
        return; // Bail out if the index of the selected address is not set
    }
    const selectedAddress = {...availableShippingAddresses.value[checkoutDataStore.selectedAddressIndex]};
    debugLog('<ShippingAddressesForm> confirmSelectedAddress() -> selectedAddress', selectedAddress);
    shippingData.value = selectedAddress;
    checkAddressValidation();
}

onMounted(() => {
    debugLog('<ShippingAddressesForm> onMounted()');
    fetchShippingAddresses();
    fetchCountries();
    enableShipToDifferentAddress();
});
</script>

<template>
    <section class="woocommerce-checkout-steps__content" id="checkout-step-2">
        <h5 v-if="availableShippingAddresses.length > 1">{{ $t('Addresses') }}</h5>
        <h5 v-else-if="availableShippingAddresses.length === 1">{{ $t('Address') }}</h5>

        <div v-if="loadingAddresses">
            {{ $t('Loading shipping addresses...') }}
        </div>

        <!-- Address selector -->
        <div class="shipping-addresses" v-if="!loadingAddresses && availableShippingAddresses.length">
            <div v-if="availableShippingAddresses.length" v-for="(address,index) in availableShippingAddresses"
                 :key="index"
                 class="shipping-addresses__item"
                 :class="{'selected': checkoutDataStore.selectedAddressIndex === index}"
                 @click="selectShippingAddress(index)"
            >
                <i class="icon icon-home"></i>
                <div>
                    <strong>{{ address.name }}</strong>
                    <!--<span>{{ address.first_name }}</span>
                    <span>{{ address.last_name }}</span>-->
                    <span>{{ address.address1 }}, {{ address.city }}</span>
                    <!--<span>{{ address.address2 }}</span>-->
                    <!--<span>{{ address.postcode }}</span>-->
                    <!--<span>{{ address.state }}</span>
                    <span>{{ address.country }}</span>-->
                </div>
            </div>
            <div class="shipping-addresses__item shipping-addresses__item--btn" v-if="!addingNewAddress">
                <button @click="onAddNewShippingAddress" :disabled="loadingAddresses">
                    <i class="icon icon-plus"></i> {{ $t('Add address') }}
                </button>
            </div>
        </div>

        <div class="checkout woocommerce-checkout" v-show="addingNewAddress">
            <div class="woocommerce-checkout-steps__row">
                <h5>{{ $t('New shipping address') }}</h5>
                <button v-if="availableShippingAddresses.length" @click="onNewShippingAddressFormClose" class="btn btn--link"><i class="icon icon-close"></i>
                    {{ $t('Close') }}
                </button>
            </div>
            <div class="woocommerce-billing-fields__field-wrapper">
                <AddressForm :available-countries="fetchedCountries" @address-validated="onShippingAddressValidated" ref="shippingAddressFormRef" />
            </div>
            <template v-if="!checkoutDataStore.isLoggedIn || (checkoutDataStore.isLoggedIn && !checkoutDataStore.hasBillingData)">
                <input type="checkbox" v-model="billingAddressEnabled"> {{ $t('Use a different address for billing') }}
            </template>
            <template v-if="billingAddressEnabled">
                <div class="woocommerce-checkout-steps__row">
                    <h5>{{ $t('Billing address') }}</h5>
                </div>
                <div class="woocommerce-billing-fields__field-wrapper" >
                    <AddressForm :available-countries="fetchedCountries" @address-validated="onBillingAddressValidated" ref="billingAddressFormRef" />
                </div>
            </template>
            <template v-if="!checkoutDataStore.isLoggedIn">
                <input type="checkbox" v-model="customerAccountCreationEnabled"> {{ $t('Create an account to save your data') }}
            </template>
        </div>
        <template v-if="addingNewAddress">
            <input type="submit" :value="t('Save address')" class="btn btn--primary" @click.prevent="onSubmit" v-if="!billingAddressEnabled">
            <input type="submit" :value="t('Save addresses')" class="btn btn--primary" @click.prevent="onSubmit" v-else>
        </template>
        <input v-else type="submit" :value="t('Use selected address')" class="btn btn--primary" :disabled="!addressSelected" @click.prevent="onConfirmSelectedAddress">
    </section>
</template>

