<script setup lang="ts">
import type {Ref} from 'vue'
import {computed, onMounted, ref} from "vue";
import {useCheckoutDataStore} from "@/stores/checkoutData";
import {wcAPI} from "@/services/wp/woocommerce";
import type {addressData, fetchedCountry} from "../../env";
import {debugLog} from "@/utils/helpers/debug";
import {object, string} from 'yup';
import {toTypedSchema} from '@vee-validate/yup';
import {ErrorMessage, useForm} from "vee-validate";
import {useI18n} from "vue-i18n";
import VueSelect from "vue3-select-component";
import {getBackEndData} from "@/services/wp/backendData";

const {t} = useI18n();

// https://vuejs.org/guide/components/props.html#prop-validation
// https://vuejs.org/guide/typescript/composition-api#props-default-values
const props = withDefaults(defineProps<{
        showAddressName?: boolean;
        showProfileType?: boolean;
        initialFormData?: addressData;
        type: "billing" | "shipping";
        availableCountries?: Array<fetchedCountry>;
        canSubmit?: boolean;
    }>(),
    {
        initialFormData: (rawProps) => {
            return {
                name: '',
                firstName: '',
                lastName: '',
                country: '',
                address1: '',
                address2: '',
                postcode: '',
                city: '',
                state: ''
            }
        },
        availableCountries: () => [],
        canSubmit: false,
        showAddressName: true,
        showProfileType: false
    }
);

const emit = defineEmits<{
    (e: 'AddressValidated', addressData: addressData): void
}>();

const checkoutDataStore = useCheckoutDataStore();

const fetchedStates: Ref<fetchedCountry[]> = ref([]);
const loadingStates = ref(false);

const shippingAddressNameIsMandatory = getBackEndData().default_shipping_address_name_is_mandatory;
const mustShowShippingAddressName = getBackEndData().must_show_default_shipping_address_name;

const validationSchema = computed(() => {
    const schemaFields = {
        ...(props.type === 'shipping' && {
            name: shippingAddressNameIsMandatory
                ? string().required(t(`Address name is a required field`)).label(t('Address name'))
                : string().label(t('Address name'))
        }),
        firstName: string().required(t(`First name is a required field`)).label(t('First name')),
        lastName: string().required(t(`Last name is a required field`)).label(t('Last name')),
        phone: string().label(t('Phone')),
        country: string().required(t(`Country is a required field`)).label(t('Country')),
        address1: string().required(t(`Address is a required field`)).label(t('Address')),
        address2: string().label(t('Address information')),
        postcode: string().required(t(`Post code is a required field`)).label(t('ZIP code')),
        city: string().required(t(`City is a required field`)).label(t('City')),
        ...(fetchedStates.value.length > 0
                ? { state: string().required(t(`State is a required field`)).label(t('State')) }
                : { state: string().label(t('State')) }
        ),
        notes: string().label(t('Order notes')),
        ...(props.type === 'billing' && {
            ...(props.showProfileType && {
                profileType: string().required(t(`Profile type is a required field`)).label(t('Profile type')),
            }),
            companyName: string()
                .label(t('Company name'))
                .when('profileType', {
                    // Se profileType è "company" (e showProfileType è attivo)
                    is: (val: string) => props.showProfileType && val === 'company',
                    then: (schema) => schema.required(t('Company name is a required field')),
                    otherwise: (schema) => schema.notRequired(),
                }),
            vatNumber: string()
                .label(t('VAT Number'))
                .when('profileType', {
                    is: (val: string) => props.showProfileType && val === 'company',
                    then: (schema) => schema.required(t('VAT Number is a required field')),
                    otherwise: (schema) => schema.notRequired(),
                }),
            fiscalCode: string()
                .label(t('Fiscal code'))
                .when('profileType', {
                    is: (val: string) => props.showProfileType && val === 'private',
                    then: (schema) => schema.required(t('Fiscal code is a required field')),
                    otherwise: (schema) => schema.notRequired(),
                }),
        })
    }

    return toTypedSchema(object(schemaFields));
});

// https://vee-validate.logaretm.com/v4/api/use-form/
const {values, defineField, errors, meta, handleSubmit} = useForm({
    validationSchema: validationSchema
});

const [name, nameAttrs] = defineField('name', {
    validateOnModelUpdate: false,
});
const [firstName, firstNameAttrs] = defineField('firstName', {
    validateOnModelUpdate: false,
});
const [lastName, lastNameAttrs] = defineField('lastName', {
    validateOnModelUpdate: false,
});
const [phone, phoneAttrs] = defineField('phone', {
    validateOnModelUpdate: false,
});
const [country, countryAttrs] = defineField('country', {
    validateOnModelUpdate: false,
});
const [address1, address1Attrs] = defineField('address1', {
    validateOnModelUpdate: false,
});
const [address2, address2Attrs] = defineField('address2', {
    validateOnModelUpdate: false,
});
const [postcode, postcodeAttrs] = defineField('postcode', {
    validateOnModelUpdate: false,
});
const [city, cityAttrs] = defineField('city', {
    validateOnModelUpdate: false,
});
const [state, stateAttrs] = defineField('state', {
    validateOnModelUpdate: false,
});
const [notes, notesAttrs] = defineField('notes', {
    validateOnModelUpdate: false,
});
const [profileType, profileTypeAttrs] = defineField('profileType', {
    validateOnModelUpdate: false,
});
const [companyName, companyNameAttrs] = defineField('companyName', {
    validateOnModelUpdate: false,
});
const [vatNumber, vatNumberAttrs] = defineField('vatNumber', {
    validateOnModelUpdate: false,
});
const [fiscalCode, fiscalCodeAttrs] = defineField('fiscalCode', {
    validateOnModelUpdate: false,
});

const formData = computed(() => {
    return {
        name: name.value,
        //name: `${address1.value},${city.value}`,
        firstName: firstName.value,
        lastName: lastName.value,
        phone: phone.value,
        country: country.value,
        address1: address1.value,
        address2: address2.value,
        postcode: postcode.value,
        city: city.value,
        state: state.value,
        notes: notes.value,
        profileType: profileType.value,
        companyName: companyName.value,
        vatNumber: vatNumber.value,
        fiscalCode: fiscalCode.value,
    }
});

async function onCountryChange() {
    postcode.value = '';
    await fetchStates();
}

function getFormData(): addressData {
    return {
        name: name.value ?? '',
        firstName: firstName.value ?? '',
        lastName: lastName.value ?? '',
        phone: phone.value ?? '',
        country: country.value ?? '',
        address1: address1.value ?? '',
        address2: address2.value ?? '',
        postcode: postcode.value ?? '',
        city: city.value ?? '',
        state: state.value ?? '',
        notes: notes.value ?? '',
        profileType: profileType.value ?? '',
        companyName: companyName.value ?? '',
        vatNumber: vatNumber.value ?? '',
        fiscalCode: fiscalCode.value ?? '',
    }
}

function getEmptyFormData(): addressData {
    return {
        name: '',
        firstName: '',
        lastName: '',
        country: '',
        address1: '',
        address2: '',
        postcode: '',
        city: '',
        state: '',
        notes: ''
    }
}

function setFormData(key: string, value: string){
    switch(key){
        case 'name':
            name.value = value;
            break;
        case 'firstName':
            firstName.value = value;
            break;
        case 'lastName':
            lastName.value = value;
            break;
        case 'phone':
            phone.value = value;
            break;
        case 'country':
            country.value = value;
            break;
        case 'address1':
            address1.value = value;
            break;
        case 'address2':
            address2.value = value;
            break;
        case 'postcode':
            postcode.value = value;
            break;
        case 'city':
            city.value = value;
            break;
        case 'state':
            state.value = value;
            break;
        case 'notes':
            notes.value = value;
            break;
        case 'profileType':
            profileType.value = value;
            break;
        case 'companyName':
            companyName.value = value;
            break;
        case 'vatNumber':
            vatNumber.value = value;
            break;
        case 'fiscalCode':
            fiscalCode.value = value;
            break;
    }
}

function resetFormData() {
    name.value = '';
    firstName.value = '';
    lastName.value = '';
    phone.value = '';
    country.value = '';
    address1.value = '';
    address2.value = '';
    postcode.value = '';
    city.value = '';
    state.value = '';
    notes.value = '';
    profileType.value = '';
    companyName.value = '';
    vatNumber.value = '';
    fiscalCode.value = '';
}

async function populateFormData(newAddressData: addressData){
    for (const [key, value] of Object.entries(newAddressData)) {
        if (value === undefined || value === null) {
            continue;
        }
        if (value === '') {
            continue;
        }
        switch (key) {
            case 'name':
                name.value = value as string;
                break;
            case 'firstName':
                firstName.value = value as string;
                break;
            case 'lastName':
                lastName.value = value as string;
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
            case 'notes':
                notes.value = value as string;
                break;
            case 'profileType':
                profileType.value = value as string;
                break;
            case 'companyName':
                companyName.value = value as string;
                break;
            case 'vatNumber':
                vatNumber.value = value as string;
                break;
            case 'fiscalCode':
                fiscalCode.value = value as string;
                break;
        }
    }
}

async function fetchStates() {
    try {
        if (!country.value) {
            return;
        }
        loadingStates.value = true;
        debugLog('<AddresseForm> fetchStates()');
        const states = await wcAPI.fetchStates(country.value);
        debugLog('<AddresseForm> fetchStates() -> response', states);
        fetchedStates.value = states;
        loadingStates.value = false;
    } catch (error) {
        loadingStates.value = false;
        debugLog('<AddresseForm> fetchCountries() ERROR', error);
    }
}

const onSubmit = handleSubmit(values => {
    emit('AddressValidated', formData.value as addressData);
});

defineExpose({
    onSubmit,
    getFormData,
    setFormData,
    resetFormData,
    populateFormData,
    getEmptyFormData
});

onMounted(() => {
    debugLog('<AddresseForm> onMounted()');
    resetFormData();
    if(props.initialFormData){
        populateFormData(props.initialFormData);
    }
});

</script>
<template>
    <form>
        <template v-if="type === 'billing'">
            <div class="form-row form-row-wide">
                <label>{{ $t('Account type') }}*</label>
                <ErrorMessage name="profileType" />

                <div class="woocommerce-billing-fields__field-wrapper woocommerce-billing-fields__field-wrapper--choice">
                    <label><input type="radio" v-model="profileType" v-bind="profileTypeAttrs" value="company">{{ $t('Company profile') }}</label>

                    <label><input type="radio" v-model="profileType" v-bind="profileTypeAttrs" value="private">{{ $t('Private profile') }}</label>
                </div>
            </div>
        </template>

        <div class="form-row form-row-wide" :class="{invalid: 'name' in errors }" v-show="type === 'shipping' && showAddressName && mustShowShippingAddressName">
            <input type="text" placeholder="" id="name" v-model="name" v-bind="nameAttrs">
            <label for="name">{{ $t('Address name') }}<span v-if="shippingAddressNameIsMandatory">*</span></label>
            <ErrorMessage name="name" v-show="meta.touched"/>
        </div>

        <div class="form-row form-row-wide" :class="{invalid: 'first_name' in errors }">
            <input type="text" placeholder="" id="first_name" v-model="firstName" v-bind="firstNameAttrs">
            <label for="first_name">{{ $t('First name') }} <span>*</span></label>
            <ErrorMessage name="firstName" v-show="meta.touched"/>
        </div>

        <div class="form-row form-row-wide" :class="{invalid: 'last_name' in errors }">
            <input type="text" placeholder="" id="last_name" v-model="lastName" v-bind="lastNameAttrs">
            <label for="address">{{ $t('Last name') }} <span>*</span></label>
            <ErrorMessage name="lastName" v-show="meta.touched"/>
        </div>

        <div class="form-row form-row-wide" :class="{invalid: 'phone' in errors }">
            <input type="text" placeholder="" id="phone" v-model="phone" v-bind="phoneAttrs">
            <label for="address">{{ $t('Phone') }}</label>
            <ErrorMessage name="phone" v-show="meta.touched"/>
        </div>

        <div class="form-row form-row-wide" :class="{invalid: 'country' in errors }">
            <VueSelect v-model="country" v-bind="countryAttrs" @option-selected="onCountryChange"
                       :options="availableCountries.map((country) => { return {label: country.label, value: country.slug} })"
                       :placeholder="t('Country')"
            />
            <label class="vue-select-label" for="country">{{ $t('Country') }} <span>*</span></label>
            <ErrorMessage name="country" v-show="meta.touched"/>
        </div>

        <div class="form-row form-row-wide" :class="{invalid: 'address1' in errors }">
            <input type="text" placeholder="" v-model="address1" v-bind="address1Attrs">
            <label for="address">{{ $t('Address') }} <span>*</span></label>
            <ErrorMessage name="address1" v-show="meta.touched"/>
        </div>

        <div class="form-row form-row-wide" :class="{invalid: 'address2' in errors }">
            <input type="text" placeholder="" v-model="address2" v-bind="address2Attrs">
            <label for="address">{{ $t('Address information') }}</label>
            <ErrorMessage name="address2" v-show="meta.touched"/>
        </div>

        <div class="form-row form-row-wide" :class="{invalid: 'postcode' in errors }">
            <input type="text" placeholder="" id="zip-code" v-model="postcode" v-bind="postcodeAttrs">
            <label for="zip-code">{{ $t('ZIP code') }} <span>*</span></label>
            <ErrorMessage name="postcode"/>
        </div>

        <div class="form-row form-row-wide" :class="{invalid: 'city' in errors }">
            <input type="text" placeholder="" id="city" v-model="city" v-bind="cityAttrs">
            <label for="city">{{ $t('City') }} <span>*</span></label>
            <ErrorMessage name="city"/>
        </div>

        <div id="state_selector" class="form-row form-row-wide" v-show="fetchedStates.length > 0"
             :class="{invalid: 'state' in errors }">
            <div class="form__item">
                <VueSelect v-model="state" v-bind="stateAttrs"
                           :options="fetchedStates.map((state) => { return {label:state.label, value:state.slug} })"
                           :placeholder="t('Select a state')"
                />
                <label class="vue-select-label" for="province">{{ $t('State') }} <span>*</span></label>
            </div>
            <ErrorMessage name="state" v-show="meta.touched"/>
        </div>

        <div class="form-row form-row-wide" v-show="type === 'shipping'">
            <textarea id="notes" placeholder="" v-model="notes" v-bind="notesAttrs"></textarea>
            <label for="notes">{{ $t('Shipping notes') }}</label>
        </div>
    </form>
</template>
<style lang="scss" scoped>
:deep(.vue-select) {
    --vs-border-radius: 24px;
    --vs-padding: 0 16px 0 16px;
    --vs-border: 1px solid #a1a1a1;
    --vs-text-color: #444;
    height: 52px;
}

.vue-select-label {
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