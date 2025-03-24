<script setup lang="ts">
import {computed, type ComputedRef, onMounted, ref, type Ref} from "vue";
import {useCheckoutDataStore} from "@/stores/checkoutData";
import type {fetchedCountry, userBillingData} from "../../env";
import {wcAPI} from "@/services/wp/woocommerce.ts";
import {debugLog} from "@/utils/helpers/debug.ts";
import {ErrorMessage, useForm} from 'vee-validate';
import {date, object, string} from 'yup';
import {toTypedSchema} from '@vee-validate/yup';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import {useI18n} from "vue-i18n";
import VueSelect from "vue3-select-component";

const { t } = useI18n();

const emit = defineEmits<{
    (e: 'profileDataSubmitted', billingData: userBillingData): void
}>();

const checkoutDataStore = useCheckoutDataStore();

const fetchedCountries: Ref<fetchedCountry[]> = ref([]);
const fetchedStates: Ref<fetchedCountry[]> = ref([]);
const loadingCountriesAndStates = ref(false);
const restoringData = ref(false);

const isProfileTypeCompany = computed(() => {
    if(typeof profileType === 'undefined'){
        return false;
    }
    if(typeof profileType.value === 'undefined'){
        return false;
    }
    return profileType.value === 'company';
});

const validationSchema = toTypedSchema(
    object({
        billingData: object({
            profileType: string().required(),
            firstName: string().required().label(t('First name')),
            lastName: string().required().label(t('Last name')),
            birthday: date().label(t('Birthday date')),
            phone: string().label(t('Phone')),
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
            //fiscalCode: string().label(t('Fiscal code')),
            fiscalCode: string().when('profileData.profileType', {
                is: () => !isProfileTypeCompany.value,
                then: (schema) => schema.required(),
                otherwise: (schema) => schema.notRequired(),
            }),
            company: string().when('profileData.profileType', {
                is: () => isProfileTypeCompany.value,
                then: (schema) => schema.required(),
                otherwise: (schema) => schema.notRequired(),
            }).label(t('Business / Company name')),
            //vatNumber: string().label(t('VAT Number')),
            vatNumber: string().when('profileData.profileType', {
                is: () => isProfileTypeCompany.value,
                then: (schema) => schema.required(),
                otherwise: (schema) => schema.notRequired(),
            })
        })
    }),
);

const { values, defineField, errors, meta, handleSubmit } = useForm({
    validationSchema: validationSchema
});

const formSubmittedOnce = ref(false);
const [profileType, profileTypeAttrs] = defineField('billingData.profileType');
const [firstName, firstNameAttrs] = defineField('billingData.firstName');
const [lastName, lastNameAttrs] = defineField('billingData.lastName');
const [birthday, birthdayAttrs] = defineField('billingData.birthday');
const [phone, phoneAttrs] = defineField('billingData.phone');
const [country, countryAttrs] = defineField('billingData.country');
const [address1, address1Attrs] = defineField('billingData.address1');
const [address2, address2Attrs] = defineField('billingData.address2');
const [postcode, postcodeAttrs] = defineField('billingData.postcode');
const [city, cityAttrs] = defineField('billingData.city');
const [state, stateAttrs] = defineField('billingData.state');
const [fiscalCode, fiscalCodeAttrs] = defineField('billingData.fiscalCode');
const [company, companyAttrs] = defineField('billingData.company');
const [vatNumber, vatNumberAttrs] = defineField('billingData.vatNumber');

const selected = ref("");

const billingData: ComputedRef<userBillingData>  = computed(() => {
    return values?.billingData as userBillingData;
});

async function fetchCountries(){
    try{
        loadingCountriesAndStates.value = true;
        debugLog('<ProfileAndBillingForm> fetchCountries()');
        const countries = await wcAPI.fetchCountries();
        debugLog('<ProfileAndBillingForm> fetchCountries() -> response', countries);
        fetchedCountries.value = countries;
        loadingCountriesAndStates.value = false;
    }catch (error){
        loadingCountriesAndStates.value = false;
        debugLog('<ProfileAndBillingForm> fetchCountries() ERROR', error);
    }
}

async function fetchStates(){
    try{
        if(!country.value){
            return;
        }
        loadingCountriesAndStates.value = true;
        debugLog('<ProfileAndBillingForm> fetchStates()');
        const states = await wcAPI.fetchStates(country.value);
        debugLog('<ProfileAndBillingForm> fetchStates() -> response', states);
        fetchedStates.value = states;
        loadingCountriesAndStates.value = false;
    }catch (error){
        loadingCountriesAndStates.value = false;
        debugLog('<ProfileAndBillingForm> fetchStates() ERROR', error);
    }
}

async function onCountryChange(){
    postcode.value = '';
    await fetchStates();
}

async function restoreFormData(){
    try{
        restoringData.value = true;
        await restoreBillingDataToForm();
        restoringData.value = false;
    }catch (error){
        restoringData.value = false;
    }
}

async function restoreBillingDataToForm(){
    for(const [key, value] of Object.entries(checkoutDataStore.billingData)){
        if(value === undefined || value === null){
            continue;
        }
        if(value === ''){
            continue;
        }
        switch (key){
            case 'type':
                profileType.value = value as string;
                break
            case 'firstName':
                firstName.value = value as string;
                break;
            case 'lastName':
                lastName.value = value as string;
                break;
            case 'birthday':
                birthday.value = value as Date;
                break;
            case 'phone':
                phone.value = value as string;
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
            case 'fiscalCode':
                state.value = value as string;
                break;
            case 'company':
                company.value = value as string;
                break;
            case 'vatNumber':
                vatNumber.value = value as string;
                break;
        }
    }
}

onMounted(async () => {
    debugLog('<ProfileAndBillingForm> onMounted()');
    await fetchCountries();
    // Restore data
    if(checkoutDataStore.hasBillingData){
        await restoreFormData();
    }
});

function onSubmit(){
    formSubmittedOnce.value = true;
    const callback = handleSubmit(values => {
        emit('profileDataSubmitted', billingData.value);
    });
    callback();
}
</script>

<template>
    <div>
        <div class="checkout woocommerce-checkout">
            <h5>{{ $t('Account type') }}</h5>

            <div class="woocommerce-billing-fields__field-wrapper woocommerce-billing-fields__field-wrapper--choice">
                <label><input type="radio" v-model="profileType" v-bind="profileTypeAttrs" value="company">{{ $t('Company profile') }}</label>

                <label><input type="radio" v-model="profileType" v-bind="profileTypeAttrs" value="private">{{ $t('Private profile') }}</label>
            </div>
        </div>

        <div class="checkout woocommerce-checkout">
            <h5>{{ $t('Account information') }}</h5>

            <div class="woocommerce-billing-fields__field-wrapper">
                <div class="form-row form-row-wide" :class="{invalid: 'profileData.firstName' in errors }">
                    <input type="text" placeholder="" id="first-name" v-model="firstName" v-bind="firstNameAttrs">
                    <label for="first-name">{{ $t('First name') }} <span>*</span></label>
                    <ErrorMessage name="profileData.firstName" />
                </div>

                <div class="form-row form-row-wide" :class="{invalid: 'profileData.lastName' in errors }">
                    <input type="text" placeholder="" id="last-name" v-model="lastName" v-bind="lastNameAttrs">
                    <label for="last-name">{{ $t('Last name') }} <span>*</span></label>
                    <ErrorMessage name="profileData.lastName" />
                </div>

                <div class="form-row form-row-wide" :class="{invalid: 'profileData.birthday' in errors }">
                    <VueDatePicker v-model="birthday" v-bind="birthdayAttrs" :enable-time-picker="false" format="dd/MM/yyyy" locale="it" auto-apply placeholder=""></VueDatePicker>
                    <label class="label-birthday" for="birth-date">{{ $t('Birthday') }}</label>
                    <ErrorMessage name="profileData.birthday" />
                </div>

                <div class="form-row form-row-wide" :class="{invalid: 'profileData.phone' in errors }">
                    <input type="tel" placeholder="" id="phone" v-model="phone" v-bind="phoneAttrs">
                    <label for="phone">{{ $t('Phone') }}</label>
                    <ErrorMessage name="profileData.phone" />
                </div>
            </div>
        </div>

        <div class="checkout woocommerce-checkout">
            <h5>{{ $t('Billing data') }}</h5>

            <div class="woocommerce-billing-fields__field-wrapper">
                <div v-show="profileType === 'company'" class="form-row form-row-wide" :class="{invalid: 'billingData.company' in errors }">
                    <input type="text" placeholder="" id="company" v-model="company" v-bind="companyAttrs">
                    <label for="company">{{ $t('Business / Company name') }} <span>*</span></label>
                    <ErrorMessage name="billingData.company" />
                </div>

                <div v-show="profileType === 'company'" class="form-row form-row-wide" :class="{invalid: 'billingData.vatNumber' in errors }">
                    <input type="text" placeholder="" id="vat_number" v-model="vatNumber" v-bind="vatNumberAttrs">
                    <label for="vat_number">{{ $t('Vat number') }}</label>
                    <ErrorMessage name="billingData.vatNumber" />
                </div>

                <div class="form-row form-row-wide" :class="{invalid: 'billingData.address1' in errors }">
                    <input type="text" placeholder="" id="address" v-model="address1" v-bind="address1Attrs">
                    <label for="address">{{ $t('Address') }} <span>*</span></label>
                    <ErrorMessage name="billingData.address1" />
                </div>

                <div class="form-row form-row-wide" :class="{invalid: 'billingData.address2' in errors }">
                    <input type="text" placeholder="" id="address" v-model="address2" v-bind="address2Attrs">
                    <label for="address">{{ $t('Address information') }}</label>
                    <ErrorMessage name="billingData.address2" />
                </div>

                <div class="form-row form-row-wide" :class="{invalid: 'billingData.country' in errors }">
                    <VueSelect v-model="country" v-bind="countryAttrs" @option-selected="onCountryChange" :disabled="loadingCountriesAndStates || restoringData"
                        :options="fetchedCountries.map((country) => { return {label: country.label, value: country.slug} })"
                        :placeholder="t('Country')"
                    />
                    <label class="vue-select-label" for="country">{{ $t('Country') }} <span>*</span></label>
                    <ErrorMessage name="billingData.country" />
                </div>

                <div class="form-row form-row-wide" :class="{invalid: 'billingData.postcode' in errors }">
                    <input type="text" placeholder="" id="zip-code" v-model="postcode" v-bind="postcodeAttrs">
                    <label for="zip-code">{{ $t('ZIP code') }} <span>*</span></label>
                    <ErrorMessage name="billingData.postcode" />
                </div>

                <div class="form-row form-row-wide" :class="{invalid: 'billingData.city' in errors }">
                    <input type="text" placeholder="" id="city" v-model="city" v-bind="cityAttrs">
                    <label for="city">{{ $t('City') }} <span>*</span></label>
                    <ErrorMessage name="billingData.city" />
                </div>

                <div id="state_selector" class="form-row form-row-wide" v-show="fetchedStates.length > 0" :class="{invalid: 'billingData.state' in errors }">
                    <VueSelect v-model="state" v-bind="stateAttrs"
                        :options="fetchedStates.map((state) => { return {label:state.label, value:state.slug} })"
                        :placeholder="t('Select a state')"
                    />
                    <label class="vue-select-label" for="province">{{ $t('State') }} <span>*</span></label>
                    <ErrorMessage name="billingData.state" />
                </div>

                <div v-show="profileType === 'private'" class="form-row form-row-wide" :class="{invalid: 'billingData.fiscalCode' in errors }">
                    <input type="text" placeholder="" id="fiscal_code" v-model="fiscalCode" v-bind="fiscalCodeAttrs">
                    <label for="fiscal_code">{{ $t('Fiscal code') }}</label>
                    <ErrorMessage name="billingData.fiscalCode" />
                </div>

            </div>
            <input type="submit" :value="t('Proceed')" class="btn btn--primary" :disabled="!meta.touched || restoringData" @click.prevent="onSubmit">
        </div>
    </div>
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
</style>
