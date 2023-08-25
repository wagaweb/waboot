<script setup lang="ts">
import Breadcrumb from './components/Breadcrumb.vue'
import SignInLanding from "@/components/SignInLanding.vue";
import SignInByPassword from "@/components/SignInByPassword.vue";
import {computed, onMounted, ref} from 'vue';
import {WPUser} from "@/services/wp/wpuser";
import UserProfileForm from "@/components/UserProfileForm.vue";
import {useCheckoutDataStore} from "@/stores/checkoutData";
import AddressesForm from "@/components/AddressesForm.vue";
import {useBreadCrumbStore} from "@/stores/breadcrumb";
import Pay from "@/components/Pay.vue";
import type {fetchedUserData} from "../env";
import OrderReview from "@/components/OrderReview.vue";

const checkoutDataStore = useCheckoutDataStore();
const breadCrumbStore = useBreadCrumbStore();

const loading = ref(false);

const typedInEmail = ref('');
const mustSignIn = ref(false);
const mustEnterPassword = ref(false);
const mustEnterProfileData = ref(false);
const mustEnterAddressData = ref(false);
const mustPay = ref(false);

const WPUserConnector = new WPUser();

onMounted(() => {
    loading.value = true;
    WPUserConnector.fetchUser().then((userData) => {
        console.log(userData);
        if(userData.is_logged_in){
            checkoutDataStore.setUserAsLoggedIn();
            checkoutDataStore.setUserEmail(userData.profile_data.email);
            typedInEmail.value = userData.profile_data.email;
            checkoutDataStore.setProfileData(userData.profile_data);
            checkoutDataStore.setShippingData(userData.shipping_data);
            if(!checkoutDataStore.isProfileComplete){
                mustEnterProfileData.value = true;
            }else if(!checkoutDataStore.isShippingDataComplete){
                mustEnterAddressData.value = true;
            }else{
                mustPay.value = true;
            }
        }else{
            mustSignIn.value = true;
        }
        loading.value = false;
    }).catch((error) => {
        console.log('App.vue onMounted() error');
        console.error(error);
    });
});

const mustShowSignInLanding = computed(() => {
    return !loading.value && mustSignIn.value;
});

const mustShowSignInByPassword = computed(() => {
    return !loading.value && mustEnterPassword.value;
});

const mustShowUserProfileDataForm = computed(() => {
    return !loading.value && mustEnterProfileData.value;
});

const mustShowAddressesDataForm = computed(() => {
    return !loading.value && mustEnterAddressData.value;
});

const mustShowPay = computed(() => {
    return !loading.value && mustPay.value;
});

function onEmailVerified(email: string, isRegistered: boolean){
    console.log('Verified email: '+email);
    console.log('Is registered?: '+isRegistered);
    typedInEmail.value = email;
    mustSignIn.value = false;
    if(isRegistered){
        mustEnterPassword.value = true;
    }else{
        mustEnterProfileData.value = true;
    }
}

function onUserSignedId(userData: fetchedUserData){
    location.reload(); //just reload for now
}

function onProfileDataSubmitted(formData: any){
    console.log('Profile data submitted');
    console.log(formData);
    checkoutDataStore.setUserEmail(typedInEmail.value);
    checkoutDataStore.setProfileData(formData);
    if(formData.createAccount){
        checkoutDataStore.setMustRegisterNewUser();
        checkoutDataStore.setUserPassword(formData.password);
    }
    mustEnterProfileData.value = false;
    if(!checkoutDataStore.isShippingDataComplete){
        mustEnterAddressData.value = true;
        breadCrumbStore.nextStep();
    }else{
        mustPay.value = true;
        breadCrumbStore.nextStep();
        breadCrumbStore.nextStep();
    }
}

function onAddressDataSubmitted(formData: any){
    console.log('Address data submitted');
    checkoutDataStore.setShippingData(formData);
    mustEnterAddressData.value = false;
    mustPay.value = true;
    breadCrumbStore.nextStep();
}

function onEditAddress(){
    breadCrumbStore.goToNamedStep('addresses');
    mustPay.value = false;
    mustEnterProfileData.value = false;
    mustEnterAddressData.value = true;
}

function onEditProfile(){
    breadCrumbStore.goToNamedStep('login');
    mustPay.value = false;
    mustEnterAddressData.value = false;
    mustEnterProfileData.value = true;
}

function onCountryChanged(formData: any){
    //@ts-ignore
    if(typeof window.jQuery === "undefined"){
        return;
    }
    //@ts-ignore
    const $ = window.jQuery;
    let $originalForm = $('#original-form-wrapper');
    if($originalForm !== "undefined" && $originalForm.length > 0){
        console.log('Changing country in the original form');
        const $billingCountry = $originalForm.find('[name=billing_country]');
        if($billingCountry.length > 0){
            $billingCountry.val(formData.country);
            $billingCountry.trigger('change');
        }
    }
}

</script>

<template>
  <Breadcrumb />
  <OrderReview />
  <SignInLanding v-if="mustShowSignInLanding" @email-verified="onEmailVerified" />
  <SignInByPassword v-if="mustShowSignInByPassword" :email="typedInEmail" @user-signed-in="onUserSignedId" />
  <UserProfileForm v-if="mustShowUserProfileDataForm" :email="typedInEmail" @profile-data-submitted="onProfileDataSubmitted" />
  <AddressesForm v-if="mustShowAddressesDataForm" @address-data-submitted="onAddressDataSubmitted" @country-changed="onCountryChanged" />
  <Pay v-if="mustShowPay" @edit-address="onEditAddress" />
</template>

<style lang="scss">
    @import "./sass/main";
</style>