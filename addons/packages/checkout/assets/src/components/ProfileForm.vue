<script setup lang="ts">
import {computed, onMounted, ref, type ComputedRef} from "vue";
import {useCheckoutDataStore} from "@/stores/checkoutData";
import type {userProfileData} from "../../env";
import {debugLog} from "@/utils/helpers/debug.ts";
import {ErrorMessage, useForm} from 'vee-validate';
import {object, string, date} from 'yup';
import {toTypedSchema} from '@vee-validate/yup';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import {useI18n} from "vue-i18n";

const { t } = useI18n();

const emit = defineEmits<{
    (e: 'profileDataSubmitted', profileData: userProfileData): void
}>();

const checkoutDataStore = useCheckoutDataStore();

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
        profileType: string().default('private').required(),
        birthday: date().label(t('Birthday date')),
        fiscalCode: string().when('profileData.profileType', {
            is: () => !isProfileTypeCompany.value,
            then: (schema) => schema.required(),
            otherwise: (schema) => schema.notRequired(),
        }).label(t('Fiscal code')),
        company: string().when('profileData.profileType', {
            is: () => isProfileTypeCompany.value,
            then: (schema) => schema.required(),
            otherwise: (schema) => schema.notRequired(),
        }).label(t('Business / Company name')),
        vatNumber: string().when('profileData.profileType', {
            is: () => isProfileTypeCompany.value,
            then: (schema) => schema.required(),
            otherwise: (schema) => schema.notRequired(),
        }).label(t('Vat Number')),
        sdiPec: string().when('profileData.profileType', {
            is: () => isProfileTypeCompany.value,
            then: (schema) => schema.required(),
            otherwise: (schema) => schema.notRequired(),
        }).label(t('Unique code SDI / PEC'))
    }),
);

const { values, defineField, errors, meta, handleSubmit } = useForm({
    validationSchema: validationSchema
});

const formSubmittedOnce = ref(false);
const [profileType, profileTypeAttrs] = defineField('profileType');
const [birthday, birthdayAttrs] = defineField('birthday');
const [fiscalCode, fiscalCodeAttrs] = defineField('fiscalCode');
const [company, companyAttrs] = defineField('company');
const [vatNumber, vatNumberAttrs] = defineField('vatNumber');
const [sdiPec, sdiPecAttrs] = defineField('sdiPec');

const formData: ComputedRef<userProfileData> = computed(() => {
    const localProfilyType = profileType.value ?? 'private';
    const localCompany = profileType.value ?? '';
    return {
        email: checkoutDataStore.userEmail,
        profileType: localProfilyType as "" | "company" | "private",
        birthday: birthday.value,
        fiscalCode: fiscalCode.value,
        company: localCompany as string,
        vatNumber: vatNumber.value,
        sdiPec: sdiPec.value
    }
});

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
    for(const [key, value] of Object.entries(checkoutDataStore.profileData)){
        if(value === undefined || value === null){
            continue;
        }
        if(value === ''){
            continue;
        }
        switch (key){
            case 'profileType':
                profileType.value = value as string;
                break;
            case 'birthday':
                birthday.value = value as Date;
                break;
            case 'fiscalCode':
                fiscalCode.value = value as string;
                break;
            case 'company':
                company.value = value as string;
                break;
            case 'vatNumber':
                vatNumber.value = value as string;
                break;
            case 'sdiPec':
                sdiPec.value = value as string;
                break;
        }
    }
}

onMounted(async () => {
    debugLog('<ProfilegForm> onMounted()');
    // Restore data
    if(checkoutDataStore.hasProfileData){
        await restoreFormData();
    }
});

function onSubmit(){
    formSubmittedOnce.value = true;
    const callback = handleSubmit(values => {
        emit('profileDataSubmitted', formData.value);
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

                <div class="form-row form-row-wide" :class="{invalid: 'birthday' in errors }">
                    <VueDatePicker v-model="birthday" v-bind="birthdayAttrs" :enable-time-picker="false" format="dd/MM/yyyy" locale="it" auto-apply placeholder=""></VueDatePicker>
                    <label class="label-birthday" for="birth-date">{{ $t('Birthday') }}</label>
                    <ErrorMessage name="birthday" />
                </div>

                <div v-show="profileType === 'company'" class="form-row form-row-wide" :class="{invalid: 'ragioneSociale' in errors }">
                    <input type="text" placeholder="" id="company" v-model="company" v-bind="companyAttrs">
                    <label for="ragione_sociale">{{ $t('Business / Company name') }} <span>*</span></label>
                    <ErrorMessage name="company" />
                </div>

                <div v-show="profileType === 'company'" class="form-row form-row-wide" :class="{invalid: 'vatNumber' in errors }">
                    <input type="text" placeholder="" id="vat_number" v-model="vatNumber" v-bind="vatNumberAttrs">
                    <label for="vat_number">{{ $t('Vat number') }}</label>
                    <ErrorMessage name="vatNumber" />
                </div>

                <div v-show="profileType === 'company'" class="form-row form-row-wide" :class="{invalid: 'sdiPec' in errors }">
                    <input type="text" placeholder="" id="sdi_pec" v-model="sdiPec" v-bind="sdiPecAttrs">
                    <label for="vat_number">{{ $t('Unique code SDI / PEC') }}</label>
                    <ErrorMessage name="sdiPec" />
                </div>

                <div v-show="profileType === 'private'" class="form-row form-row-wide" :class="{invalid: 'fiscalCode' in errors }">
                    <input type="text" placeholder="" id="fiscal_code" v-model="fiscalCode" v-bind="fiscalCodeAttrs">
                    <label for="fiscal_code">{{ $t('Fiscal code') }}</label>
                    <ErrorMessage name="fiscalCode" />
                </div>

                <input type="submit" :value="t('Proceed')" class="btn btn--primary" :disabled="!meta.touched || restoringData" @click.prevent="onSubmit">
            </div>
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
