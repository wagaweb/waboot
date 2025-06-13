import {ref, reactive, computed, watch} from 'vue'
import type {Ref} from 'vue'
import { defineStore } from 'pinia'
import type {userBillingData, userProfileData, userShippingData} from "../../env";
import {getBackendFormatFromDate, getDayFromDate} from "@/utils/helpers/dates.ts";

export type StepType = 'email' | 'password' | 'profile' | 'address' | 'pay';

// @see: https://pinia.vuejs.org/core-concepts/#Setup-Stores
export const useCheckoutDataStore = defineStore('currentUser', () => {
    const globalErrors: Ref<string[]> = ref([]);
    const currentStep: Ref<StepType> = ref('email');
    const currentUserId = ref<number>();
    const wpProfileFound = ref(false);
    const isGuest = ref(true);
    const profileData: userProfileData = reactive({
        email: '',
        profileType: '',
        birthday: undefined,
        fiscalCode: '',
        company: '',
        vatNumber: '',
        sdiPec: ''
    });
    const billingData: userBillingData = reactive({
        firstName: '',
        lastName: '',
        phone: '',
        country: '',
        address1: '',
        address2: '',
        postcode: '',
        city: '',
        state: '',
    });
    const shippingData: userShippingData = reactive({
        name: '',
        firstName: '',
        lastName: '',
        phone: '',
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
        return profileData.email;
    });

    const hasEmail = computed(() => {
       return profileData.email !== '';
    });

    const isProfileDataComplete = computed(() => {
        return profileData.email !== '' &&
            profileData.profileType !== '' &&
            profileData.birthday !== undefined;
    });

    const isBillingDataComplete = computed(() => {
        return profileData.email !== '' &&
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

    const hasProfileData = computed(() => {
        for(const [key, value] of Object.entries(profileData)){
            if(key === 'email'){
                continue;
            }
            if(value !== '' && value !== undefined && value !== null){
                return true;
            }
        }
        return false;
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
        return getDayFromDate(profileData.birthday);
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

    watch(profileData, (oldV, newV) => {
        //@ts-ignore
        const $ = window.jQuery;
        let $originalForm = $('#original-form-wrapper');
        if($originalForm !== "undefined" && $originalForm.length > 0){
            $('[name=billing_email]').val(profileData.email);
            const $billingCustomerType = $originalForm.find('[name=billing_customer_type]');
            const $billingBirthday = $originalForm.find('[name=billing_birthday]');
            const $billingFiscalCode = $originalForm.find('[name=billing_fiscal_code]');
            const $billingCompany = $originalForm.find('[name=billing_company]');
            const $billingVatNumber = $originalForm.find('[name=billing_vat_number]');
            const $billingSdiPec = $originalForm.find('[name=billing_sdi_pec]');
            if($billingFiscalCode.length > 0){
                $billingFiscalCode.val(profileData.fiscalCode);
            }
            if($billingCompany.length > 0){
                $billingCompany.val(profileData.company);
            }
            if($billingVatNumber.length > 0){
                $billingVatNumber.val(profileData.vatNumber);
            }
            if($billingSdiPec.length > 0){
                $billingSdiPec.val(profileData.sdiPec);
            }
            if($billingCustomerType.length > 0){
                if($billingCustomerType.length > 1){
                    // Its radio
                    $originalForm.find(`#billing_customer_type_${profileData.profileType}`).prop("checked",true);
                    $originalForm.find(`#billing_customer_type_${profileData.profileType}`).prop("value", profileData.profileType);
                    $originalForm.find(`[value='${profileData.profileType}']`).prop("checked",true);
                }else{
                    $billingCustomerType.val(profileData.profileType);
                }
            }
            if($billingBirthday.length > 0){
                let dateFormatted = getBackendFormatFromDate(profileData.birthday);
                $billingBirthday.val(dateFormatted);
            }
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
            $('[name=billing_address_1]').val(billingData.address1);
            $('[name=billing_address_2]').val(billingData.address2);
            $('[name=billing_city]').val(billingData.city);
            $('[name=billing_postcode]').val(billingData.postcode);
            const $billingCountry = $originalForm.find('[name=billing_country]');
            const $billingState = $originalForm.find('[name=billing_state]');

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
            const $shippingPhone = $originalForm.find('[name=shipping_phone]');
            if($shippingPhone.length > 0){
                $shippingPhone.val(shippingData.phone);
            }
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

    function setProfileData(newProfileData: userProfileData){
        if(newProfileData.email !== undefined){
            profileData.email = newProfileData.email;
        }
        if(newProfileData.profileType !== undefined){
            profileData.profileType = newProfileData.profileType;
        }
        if(newProfileData.birthday !== undefined){
            profileData.birthday = newProfileData.birthday;
        }
        if(newProfileData.fiscalCode !== undefined){
            profileData.fiscalCode = newProfileData.fiscalCode;
        }
        if(newProfileData.company !== undefined){
            profileData.company = newProfileData.company;
        }
        if(newProfileData.vatNumber !== undefined){
            profileData.vatNumber = newProfileData.vatNumber;
        }
        if(newProfileData.sdiPec !== undefined){
            profileData.sdiPec = newProfileData.sdiPec;
        }
    }

    function setBillingData(newBillingData: userBillingData){
        if(newBillingData.firstName !== undefined){
            billingData.firstName = newBillingData.firstName;
        }
        if(newBillingData.lastName !== undefined){
            billingData.lastName = newBillingData.lastName;
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
    }

    function clearBillingData(){
        billingData.country = '';
        billingData.address1 = '';
        billingData.address2 = '';
        billingData.city = '';
        billingData.postcode = '';
        billingData.state = '';
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
        profileData.email = email;
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
        profileData,
        billingData,
        isProfileDataComplete,
        isBillingDataComplete,
        hasBillingData,
        hasProfileData,
        shippingData,
        isShippingDataComplete,
        selectedAddressIndex,
        mustRestoreAddressData,
        setUserAsLoggedIn,
        setUserId,
        setUserEmail,
        setProfileData,
        setBillingData,
        setShippingData,
    }
})