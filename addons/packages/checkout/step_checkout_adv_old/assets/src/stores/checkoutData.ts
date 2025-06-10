import {ref, reactive, computed, watch} from 'vue'
import type {Ref} from 'vue'
import { defineStore } from 'pinia'
import type {userBillingData, userShippingData} from "../../env";
import {getBackendFormatFromDate, getDayFromDate} from "@/utils/helpers/dates.ts";

export type StepType = 'email' | 'password' | 'address' | 'pay';

// @see: https://pinia.vuejs.org/core-concepts/#Setup-Stores
export const useCheckoutDataStore = defineStore('currentUser', () => {
    const globalErrors: Ref<string[]> = ref([]);
    const currentStep: Ref<StepType> = ref('email');
    const currentUserId = ref<number>();
    const wpProfileFound = ref(false);
    const isGuest = ref(true);
    const billingData: userBillingData = reactive({
        email: '',
        profileType: '',
        firstName: '',
        lastName: '',
        country: '',
        address1: '',
        address2: '',
        postcode: '',
        city: '',
        state: '',
        fiscalCode: '',
        company: '',
        vatNumber: '',
        birthday: undefined,
        phone: '',
        sdiPec: ''
    });
    const shippingData: userShippingData = reactive({
        name: '',
        firstName: '',
        lastName: '',
        country: '',
        address1: '',
        address2: '',
        postcode: '',
        city: '',
        state: '',
        notes: '',
    });
    const selectedAddressIndex = ref<number|undefined>();
    const mustRestoreAddressData = ref<boolean>(false);

    const mustRegisterNewUser = computed(() => {
        return !isGuest.value;
    });

    const isUserLoggedIn = computed(() => {
       return currentUserId.value !== undefined && currentUserId.value != 0;
    });

    const userEmail = computed(() => {
        return billingData.email;
    });

    const hasEmail = computed(() => {
       return billingData.email !== '';
    });

    const isProfileDataComplete = computed(() => {
        return billingData.email !== '' &&
            billingData.firstName !== '' &&
            billingData.lastName !== '';
    });

    const isBillingDataComplete = computed(() => {
        return billingData.email !== '' &&
            billingData.firstName !== '' &&
            billingData.lastName !== '' &&
            billingData.address1 !== '' &&
            billingData.postcode !== '' &&
            billingData.city !== '' &&
            billingData.country !== '';
    });

    const hasBillingData = computed(() => {
        return billingData.address1 !== '' ||
            billingData.postcode !== '' ||
            billingData.city !== '' ||
            billingData.country !== '';
    });

    const isShippingDataComplete = computed(() => {
        return shippingData.firstName !== '' &&
            shippingData.lastName !== '' &&
            shippingData.address1 !== '' &&
            shippingData.postcode !== '' &&
            shippingData.city !== '' &&
            shippingData.country !== '';
    });

    const birthdayString = computed(() => {
        return getDayFromDate(billingData.birthday);
    });

    watch(isGuest, (newV, oldV ) => {
        //@ts-ignore
        const $ = window.jQuery;
        let $originalForm = $('#original-form-wrapper');
        if($originalForm !== "undefined" && $originalForm.length > 0){
            if(newV){
                $('[name=createaccount]').attr('checked', false);
            }else{
                $('[name=createaccount]').attr('checked', true);
            }
            $('[name=createaccount]').trigger('change');
        }
    });

    watch(billingData, (oldV, newV) => {
        //@ts-ignore
        const $ = window.jQuery;
        let $originalForm = $('#original-form-wrapper');
        if($originalForm !== "undefined" && $originalForm.length > 0){
            $('[name=billing_first_name]').val(billingData.firstName);
            $('[name=billing_last_name]').val(billingData.lastName);
            $('[name=billing_phone]').val(billingData.phone);
            $('[name=billing_email]').val(billingData.email);
            $('[name=billing_address_1]').val(billingData.address1);
            $('[name=billing_address_2]').val(billingData.address2);
            $('[name=billing_city]').val(billingData.city);
            $('[name=billing_postcode]').val(billingData.postcode);
            const $billingCountry = $originalForm.find('[name=billing_country]');
            const $billingState = $originalForm.find('[name=billing_state]');
            const $billingCustomerType = $originalForm.find('[name=billing_customer_type]');
            const $billingBirthday = $originalForm.find('[name=billing_birthday]');
            const $billingFiscalCode = $originalForm.find('[name=billing_fiscal_code]');
            const $billingCompany = $originalForm.find('[name=billing_company]');
            const $billingVatNumber = $originalForm.find('[name=billing_vat_number]');
            const $billingSdiPec = $originalForm.find('[name=billing_sdi_pec]');
            if($billingCountry.length > 0){
                $billingCountry.val(billingData.country);
                $billingCountry.trigger('change');
            }
            if($billingState.length > 0){
                $("#billing_state").val(billingData.state);
                $("#billing_state").trigger('change');
                // setTimeout(() => {
                //     $billingState.val(billingData.state);
                //     $billingState.trigger('change');
                //     $("#billing_state").val(billingData.state);
                //     $("#billing_state").trigger('change');
                // },2000);
            }
            if($billingFiscalCode.length > 0){
                $billingFiscalCode.val(billingData.fiscalCode);
            }
            if($billingCompany.length > 0){
                $billingCompany.val(billingData.company);
            }
            if($billingVatNumber.length > 0){
                $billingVatNumber.val(billingData.vatNumber);
            }
            if($billingSdiPec.length > 0){
                $billingSdiPec.val(billingData.sdiPec);
            }
            if($billingCustomerType.length > 0){
                if($billingCustomerType.length > 1){
                    // Its radio
                    $originalForm.find(`#billing_customer_type_${billingData.profileType}`).prop("checked",true);
                    $originalForm.find(`#billing_customer_type_${billingData.profileType}`).prop("value", billingData.profileType);
                    $originalForm.find(`[value='${billingData.profileType}']`).prop("checked",true);
                }else{
                    $billingCustomerType.val(billingData.profileType);
                }
            }
            if($billingBirthday.length > 0){
                let dateFormatted = getBackendFormatFromDate(billingData.birthday);
                $billingBirthday.val(dateFormatted);
            }
        }
    });

    watch(shippingData, (oldV, newV) => {
        //@ts-ignore
        const $ = window.jQuery;
        let $originalForm = $('#original-form-wrapper');
        if($originalForm !== "undefined" && $originalForm.length > 0){
            const $shippingCountry = $originalForm.find('[name=shipping_country]');
            if($shippingCountry.length > 0){
                $shippingCountry.val(shippingData.country);
                $shippingCountry.trigger('change');
            }
            const $shippingState = $originalForm.find('[name=shipping_state]');
            if($shippingState.length > 0){
                $("#shipping_state").val(shippingData.state);
                $("#shipping_state").trigger('change');
                setTimeout(() => {
                    $shippingState.val(shippingData.state);
                    $shippingState.trigger('change');
                    $("#shipping_state").val(shippingData.state);
                    $("#shipping_state").trigger('change');
                },2000);
            }
            $('[name=shipping_id]').val(shippingData.name);
            $('[name=shipping_first_name]').val(shippingData.firstName);
            $('[name=shipping_last_name]').val(shippingData.lastName);
            $('[name=shipping_address_1]').val(shippingData.address1);
            $('[name=shipping_address_2]').val(shippingData.address2);
            $('[name=shipping_city]').val(shippingData.city);
            $('[name=shipping_postcode]').val(shippingData.postcode);
        }
    });

    function cleanErrors(){
        globalErrors.value = [];
    }

    function addError(error: string){
        globalErrors.value.push(error);
    }

    function setUserAsLoggedIn(){
        isGuest.value = false;
    }

    function setBillingData(newBillingData: userBillingData){
        if(newBillingData.profileType !== undefined){
            billingData.profileType = newBillingData.profileType;
        }else{
            billingData.profileType = 'private'; // Default
        }
        if(newBillingData.firstName !== undefined){
            billingData.firstName = newBillingData.firstName;
        }
        if(newBillingData.lastName !== undefined){
            billingData.lastName = newBillingData.lastName;
        }
        if(newBillingData.birthday !== undefined){
            billingData.birthday = newBillingData.birthday;
        }
        if(newBillingData.phone !== undefined){
            billingData.phone = newBillingData.phone;
        }
        if(newBillingData.country !== undefined){
            billingData.country = newBillingData.country;
        }
        if(newBillingData.address1 !== undefined){
            billingData.address1 = newBillingData.address1;
        }
        if(newBillingData.address2 !== undefined){
            billingData.address2 = newBillingData.address2;
        }
        if(newBillingData.city !== undefined){
            billingData.city = newBillingData.city;
        }
        if(newBillingData.postcode !== undefined){
            billingData.postcode = newBillingData.postcode;
        }
        if(newBillingData.state !== undefined){
            billingData.state = newBillingData.state;
        }
        if(newBillingData.fiscalCode !== undefined){
            billingData.fiscalCode = newBillingData.fiscalCode;
        }
        if(newBillingData.company !== undefined){
            billingData.company = newBillingData.company;
        }
        if(newBillingData.vatNumber !== undefined){
            billingData.vatNumber = newBillingData.vatNumber;
        }
        if(newBillingData.sdiPec !== undefined){
            billingData.sdiPec = newBillingData.sdiPec;
        }
    }

    function clearBillingData(){
        billingData.country = '';
        billingData.address1 = '';
        billingData.address2 = '';
        billingData.city = '';
        billingData.postcode = '';
        billingData.state = '';
        billingData.fiscalCode = '';
        billingData.company = '';
        billingData.vatNumber = '';
        billingData.sdiPec = '';
    }

    function setShippingData(newShippingData: userShippingData){
        if(newShippingData.name !== undefined){
            shippingData.name = newShippingData.name;
        }
        if(newShippingData.firstName !== undefined){
            shippingData.firstName = newShippingData.firstName;
        }
        if(newShippingData.lastName !== undefined){
            shippingData.lastName = newShippingData.lastName;
        }
        if(newShippingData.country !== undefined){
            shippingData.country = newShippingData.country;
        }
        if(newShippingData.country !== undefined){
            shippingData.country = newShippingData.country;
        }
        if(newShippingData.address1 !== undefined){
            shippingData.address1 = newShippingData.address1;
        }
        if(newShippingData.address2 !== undefined){
            shippingData.address2 = newShippingData.address2;
        }
        if(newShippingData.postcode !== undefined){
            shippingData.postcode = newShippingData.postcode;
        }
        if(newShippingData.city !== undefined){
            shippingData.city = newShippingData.city;
        }
        if(newShippingData.state !== undefined){
            shippingData.state = newShippingData.state;
        }
        if(newShippingData.notes !== undefined){
            shippingData.notes = newShippingData.notes;
        }
    }

    function setUserId(id: number){
        currentUserId.value = id;
    }

    function setUserEmail(email: string){
        billingData.email = email;
    }

    return {
        currentStep,
        globalErrors,
        cleanErrors,
        addError,
        isGuest,
        isLoggedIn: isUserLoggedIn,
        currentUserId,
        wpProfileFound,
        mustRegisterNewUser,
        birthdayString,
        userEmail,
        hasEmail,
        billingData,
        isProfileDataComplete,
        isBillingDataComplete,
        hasBillingData,
        shippingData,
        isShippingDataComplete,
        selectedAddressIndex,
        mustRestoreAddressData,
        setUserAsLoggedIn,
        setUserId,
        setUserEmail,
        setBillingData,
        setShippingData,
    }
})