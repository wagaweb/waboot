<script setup lang="ts">
import type {Ref} from 'vue'
import {computed, onMounted, reactive, ref} from "vue";
import {useCheckoutDataStore} from "@/stores/checkoutData";
import {wcAPI} from "@/services/wp/woocommerce";
import type {fetchedCountry, userBillingData, userShippingData, userShippingDataWP} from "../../env";
import {debugLog} from "@/utils/helpers/debug.ts";
import {wpUserAPI} from "@/services/wp/user.ts";
import {object, string} from 'yup';
import { toTypedSchema } from '@vee-validate/yup';
import {ErrorMessage, useForm} from "vee-validate";
import {useI18n} from "vue-i18n";
import VueSelect from "vue3-select-component";
import {deepClone} from "@/utils/helpers/objects.ts";

const { t } = useI18n();

const emit = defineEmits<{
    (e: 'AddressDataSubmitted', shippingData: userShippingData, billingData: userBillingData): void
}>();

const checkoutDataStore = useCheckoutDataStore();

const fetchedCountries: Ref<fetchedCountry[]> = ref([]);
const fetchedStates: Ref<fetchedCountry[]> = ref([]);
const loadingCountriesAndStates = ref(false);

const loadingAddresses = ref(true);
const availableShippingAddresses = ref<userShippingDataWP[]>([]);
const addingNewAddress = ref(false);
const addressSelected = computed(() => {
    return typeof checkoutDataStore.selectedAddressIndex !== 'undefined';
});

const validationSchema = toTypedSchema(object({
    name: string().required().label(t('Address name')),
    firstName: string().required().label(t('First name')),
    lastName: string().required().label(t('Last name')),
    country: string().required().label(t('Country')),
    address1: string().required().label(t('Address')),
    address2: string().label(t('Address information')),
    postcode: string().required().label(t('ZIP code')),
    city: string().required().label(t('City')),
    state: string().when([], {
        is: () => fetchedStates.value.length > 0,
        then: (schema) => schema.required(),
        otherwise: (schema) => schema.notRequired(),
    }).label(t('State')),
    notes: string().label(t('Order notes'))
}));

const { values, defineField, errors, meta, handleSubmit } = useForm({
    validationSchema: validationSchema
});

const [name, nameAttrs] = defineField('name');
const [firstName, firstNameAttrs] = defineField('firstName');
const [lastName, lastNameAttrs] = defineField('lastName');
const [country, countryAttrs] = defineField('country');
const [address1, address1Attrs] = defineField('address1');
const [address2, address2Attrs] = defineField('address2');
const [postcode, postcodeAttrs] = defineField('postcode');
const [city, cityAttrs] = defineField('city');
const [state, stateAttrs] = defineField('state');
const [notes, notesAttrs] = defineField('notes');

const formData = computed(() => {
    return {
        name: name.value,
        firstName: firstName.value,
        lastName: lastName.value,
        country: country.value,
        address1: address1.value,
        address2: address2.value,
        postcode: postcode.value,
        city: city.value,
        state: state.value,
        notes: notes.value
    }
});

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

async function fetchStates() {
    try {
        if(!country.value){
            return;
        }
        loadingCountriesAndStates.value = true;
        debugLog('<ShippingAddressesForm> fetchStates()');
        const states = await wcAPI.fetchStates(country.value);
        debugLog('<ShippingAddressesForm> fetchStates() -> response', states);
        fetchedStates.value = states;
        loadingCountriesAndStates.value = false;
    } catch (error) {
        loadingCountriesAndStates.value = false;
        debugLog('<ShippingAddressesForm> fetchCountries() ERROR', error);
    }
}

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
        if(availableShippingAddresses.value.length === 0){
            addingNewAddress.value = true;
        }
    } catch (error) {
        loadingAddresses.value = false;
        debugLog('<ShippingAddressesForm> fetchShippingAddresses() ERROR', error);
    }
}

function resetFormData() {
    name.value = '';
    firstName.value = '';
    lastName.value = '';
    country.value = '';
    address1.value = '';
    address2.value = '';
    postcode.value = '';
    city.value = '';
    state.value = '';
    notes.value = '';
}

function onAddNewShippingAddress() {
    addingNewAddress.value = true;
    checkoutDataStore.selectedAddressIndex = undefined;
    resetFormData();
}

function onRemovingNewShippingAddress() {
    addingNewAddress.value = false;
}

function selectShippingAddress(index: number) {
    if (availableShippingAddresses.value.length <= 0) {
        return;
    }
    if (typeof availableShippingAddresses.value[index] === 'undefined') {
        return;
    }
    const selectedAddress = {...availableShippingAddresses.value[index]};
    checkoutDataStore.selectedAddressIndex = index;
    addingNewAddress.value = false;
    name.value = selectedAddress.name;
    firstName.value = selectedAddress.first_name;
    lastName.value = selectedAddress.last_name;
    country.value = selectedAddress.country;
    address1.value = selectedAddress.address1;
    address2.value = selectedAddress.address2;
    postcode.value = selectedAddress.postcode;
    city.value = selectedAddress.city;
    state.value = selectedAddress.state;
    notes.value = selectedAddress.notes;
}

function enableShipToDifferentAddress() {
    //@ts-ignore
    const $ = window.jQuery;
    let $originalForm = $('#original-form-wrapper');
    if ($originalForm !== "undefined" && $originalForm.length > 0) {
        $('[name=ship_to_different_address]').attr('checked', true);
        $('[name=ship_to_different_address]').trigger('change');
    }
}

const onSubmit = handleSubmit(values => {
    const shippingData = formData.value;
    const billingData = deepClone(shippingData);
    emit('AddressDataSubmitted', shippingData as userShippingData, billingData as userBillingData);
});

function confirmSelectedAddress(){
    const shippingData = formData.value;
    const billingData = deepClone(shippingData);
    emit('AddressDataSubmitted', formData.value as userShippingData, billingData as userBillingData);
}

async function onCountryChange(){
    postcode.value = '';
    await fetchStates();
}

async function restoreFormData(){
    for(const [key, value] of Object.entries(checkoutDataStore.shippingData)){
        if(value === undefined || value === null){
            continue;
        }
        if(value === ''){
            continue;
        }
        switch (key){
            case 'name':
                name.value = value as string;
                break;
            case 'firstName':
                firstName.value = value as string;
                break;
            case 'lastName':
                lastName.value = value as string;
                break;
            case 'country':
                country.value = value as string;
                await onCountryChange();
                break;
            case 'address1':
                address1.value = value as string;
                break;
            case 'address2':
                address2.value = value as string;
                break;
            case 'postcode':
                postcode.value = value as string;
                break;
            case 'city':
                city.value = value as string;
                break;
            case 'state':
                state.value = value as string;
                break;
        }
    }
}

onMounted(() => {
    debugLog('<ShippingAddressesForm> onMounted()');
    fetchShippingAddresses();
    fetchCountries();
    enableShipToDifferentAddress();
    for (const [key, value] of Object.entries(formData)) {
        //@ts-ignore
        if (checkoutDataStore.shippingData.hasOwnProperty(key) && checkoutDataStore.shippingData[key] !== '') {
            //@ts-ignore
            formData[key] = checkoutDataStore.shippingData[key];
        }
    }
    if (checkoutDataStore.selectedAddressIndex !== undefined) {
        debugLog('<ShippingAddressesForm> onMounted() -> select previously selected address');
        selectShippingAddress(checkoutDataStore.selectedAddressIndex);
    }else{
        if(checkoutDataStore.mustRestoreAddressData){
            debugLog('<ShippingAddressesForm> onMounted() -> restore data');
            restoreFormData();
            checkoutDataStore.mustRestoreAddressData = false;
        }
    }
});
</script>

<template>
    <section class="woocommerce-checkout-steps__content" id="checkout-step-2">
        <h4>{{ $t('Shipping addresses') }}</h4>

        <div class="shipping-addresses" v-if="!loadingAddresses">

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
        <div v-else>
            {{ $t('Loading shipping addresses...') }}
        </div>

        <div class="checkout woocommerce-checkout" v-show="addingNewAddress">
            <div class="woocommerce-checkout-steps__row">
                <h5>{{ $t('New shipping address') }}</h5>
                <button @click="onRemovingNewShippingAddress" class="btn btn--link"><i class="icon icon-close"></i> {{ $t('Close') }}
                </button>
            </div>
            <div class="woocommerce-billing-fields__field-wrapper">
                <form>
                    <div class="form-row form-row-wide" :class="{invalid: 'name' in errors }">
                      <input type="text" placeholder="" id="name" v-model="name" v-bind="nameAttrs">
                      <label for="name">{{ $t('Address name') }}<span>*</span></label>
                      <ErrorMessage name="name" v-show="meta.touched" />
                    </div>

                    <div class="form-row form-row-wide" :class="{invalid: 'first_name' in errors }">
                      <input type="text" placeholder="" id="first_name" v-model="firstName" v-bind="firstNameAttrs">
                      <label for="first_name">{{ $t('First name') }} <span>*</span></label>
                      <ErrorMessage name="firstName" v-show="meta.touched" />
                    </div>

                    <div class="form-row form-row-wide" :class="{invalid: 'last_name' in errors }">
                      <input type="text" placeholder="" id="last_name" v-model="lastName" v-bind="lastNameAttrs">
                      <label for="address">{{ $t('Last name') }} <span>*</span></label>
                      <ErrorMessage name="lastName" v-show="meta.touched" />
                    </div>

                    <div class="form-row form-row-wide" :class="{invalid: 'country' in errors }">
                      <VueSelect v-model="country" v-bind="countryAttrs" @option-selected="onCountryChange"
                        :options="fetchedCountries.map((country) => { return {label: country.label, value: country.slug} })"
                        :placeholder="t('Country')"
                      />
                      <label class="vue-select-label" for="country">{{ $t('Country') }} <span>*</span></label>
                      <ErrorMessage name="country" v-show="meta.touched" />
                    </div>

                    <div class="form-row form-row-wide" :class="{invalid: 'address1' in errors }">
                      <input type="text" placeholder="" v-model="address1" v-bind="address1Attrs">
                      <label for="address">{{ $t('Address') }} <span>*</span></label>
                      <ErrorMessage name="address1" v-show="meta.touched" />
                    </div>

                    <div class="form-row form-row-wide" :class="{invalid: 'address2' in errors }">
                      <input type="text" placeholder="" v-model="address2" v-bind="address2Attrs">
                      <label for="address">{{ $t('Address information') }}</label>
                      <ErrorMessage name="address2" v-show="meta.touched" />
                    </div>

                    <div class="form-row form-row-wide" :class="{invalid: 'postcode' in errors }">
                        <input type="text" placeholder="" id="zip-code" v-model="postcode" v-bind="postcodeAttrs">
                        <label for="zip-code">{{ $t('ZIP code') }} <span>*</span></label>
                        <ErrorMessage name="postcode" />
                    </div>

                    <div class="form-row form-row-wide" :class="{invalid: 'city' in errors }">
                        <input type="text" placeholder="" id="city" v-model="city" v-bind="cityAttrs">
                        <label for="city">{{ $t('City') }} <span>*</span></label>
                        <ErrorMessage name="city" />
                    </div>

                    <div id="state_selector" class="form-row form-row-wide" v-show="fetchedStates.length > 0" :class="{invalid: 'state' in errors }">
                        <div class="form__item">
                            <VueSelect v-model="state" v-bind="stateAttrs"
                               :options="fetchedStates.map((state) => { return {label:state.label, value:state.slug} })"
                               :placeholder="t('Select a state')"
                            />
                            <label class="vue-select-label" for="province">{{ $t('State') }} <span>*</span></label>
                        </div>
                        <ErrorMessage name="state" v-show="meta.touched" />
                    </div>

                    <div class="form-row form-row-wide">
                      <textarea id="notes" placeholder="" v-model="notes" v-bind="notesAttrs"></textarea>
                      <label for="notes">{{ $t('Shipping notes') }}</label>
                    </div>
                </form>
            </div>
        </div>
        <input v-if="addingNewAddress" type="submit" :value="t('Proceed')" class="btn btn--primary" @click.prevent="onSubmit">
        <input v-else type="submit" :value="t('Use selected address')" class="btn btn--primary" :disabled="!addressSelected" @click.prevent="confirmSelectedAddress">
    </section>
</template>
<style lang="scss" scoped>
:deep(.vue-select) {
    --vs-border-radius: 24px;
    --vs-padding: 0 16px 0 16px;
    --vs-border: 1px solid #a1a1a1;
    --vs-text-color: #444;
    height: 52px;
}

.vue-select-label{
    font-size: .75em;
    padding: 0 3px;
    top: -5px !important;
    background: #fff;
}

.available-address__wrapper {
    border: 1px solid grey;

    &.selected {
        border: 4px solid grey;
    }
}
</style>
