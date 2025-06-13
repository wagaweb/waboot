<script setup lang="ts">
import Breadcrumb from './components/Breadcrumb.vue'
import SignInLanding from "./components/SignInLanding.vue";
import SignInByPassword from "./components/SignInByPassword.vue";
import {computed, onMounted, ref} from 'vue';
import {useCheckoutDataStore} from "./stores/checkoutData";
import {useBreadCrumbStore} from "./stores/breadcrumb";
import Pay from "./components/Pay.vue";
import type {addressData, fetchedUserData, userProfileData} from "../env";
import OrderReview from "./components/OrderReview.vue";
import {debugLog} from "@/utils/helpers/debug.ts";
import {wpUserAPI} from "@/services/wp/user.ts";
import AddressesForm from "@/components/AddressesForm.vue";
import UserDataSummary from "@/components/UserDataSummary.vue";
import {useI18n} from "vue-i18n";
import {getBackEndData} from "@/services/wp/backendData.ts";
import ProfileForm from "@/components/ProfileForm.vue";

const { t } = useI18n();

const checkoutDataStore = useCheckoutDataStore();
const breadCrumbStore = useBreadCrumbStore();

const stepTitle = computed(() => {
    switch(breadCrumbStore.currentStep){
        case 1:
            return t('Login');
        case 2:
            return t('Profile');
        case 3:
            return t('Shipping');
        case 4:
            return t('Payment');
    }
});

const mustShowProfileStep = computed(() => {
    // todo: add the condition to check if profile data are already filled completely?
    return getBackEndData().must_show_profile_step;
});

const loading = ref(false);

onMounted(async () => {
    try {
        checkoutDataStore.cleanErrors();
        debugLog('<App> onMounted()');
        loading.value = true;
        const userData = await wpUserAPI.fetchUser();
        debugLog('<App> onMounted() -> wpUserAPI.fetchUser()', userData);
        if (userData.is_logged_in) {
            checkoutDataStore.setUserAsLoggedIn(userData.id);
            checkoutDataStore.setUserEmail(userData.profile_data.email);
            checkoutDataStore.setProfileData(userData.profile_data);
            checkoutDataStore.setBillingData(userData.billing_data);
            checkoutDataStore.setShippingData(userData.shipping_data);
            if(mustShowProfileStep.value){
                checkoutDataStore.currentStep = 'profile';
            }else{
                checkoutDataStore.currentStep = 'address';
            }
        } else {
            checkoutDataStore.currentStep = 'email';
        }
        loading.value = false;
    } catch (error: any) {
        debugLog('<App> onMounted() ERROR', error);
        checkoutDataStore.addError(error.message);
    }
});

function onEmailSubmitted(email: string, profileFound: boolean) {
    debugLog('<App> onEmailSubmitted(), email', email);
    debugLog('<App> onEmailSubmitted(), profileFound?', profileFound);
    checkoutDataStore.setUserEmail(email);
    checkoutDataStore.wpProfileFound = profileFound;
    if (profileFound) {
        debugLog('<App> onEmailSubmitted() -> set next step', 'password');
        checkoutDataStore.currentStep = 'password';
    } else {
        if(mustShowProfileStep.value){
            debugLog('<App> onEmailSubmitted() -> set next step', 'profile');
            checkoutDataStore.currentStep = 'profile';
        }else{
            debugLog('<App> onEmailSubmitted() -> set next step', 'address');
            checkoutDataStore.currentStep = 'address';
        }
    }
}

function onUserSignedId(userData: fetchedUserData) {
    location.reload(); //just reload for now
}

function onProfileDataSubmitted(profileData: userProfileData){
    debugLog('<App> onProfileDataSubmitted()', profileData);
    checkoutDataStore.setProfileData(profileData);
    checkoutDataStore.currentStep = 'address';
}

function onAddressDataSubmitted(shippingData: addressData, billingData: addressData, isGuest: boolean) {
    debugLog('<App> onAddressDataSubmitted() -> shipping', shippingData);
    debugLog('<App> onAddressDataSubmitted() -> billing', billingData);
    checkoutDataStore.setBillingData(billingData);
    checkoutDataStore.setShippingData(shippingData);
    checkoutDataStore.isGuest = isGuest;
    checkoutDataStore.currentStep = 'pay';
}

</script>

<template>
    <div class="woocommerce-checkout-steps__left" :data-step="checkoutDataStore.currentStep" v-if="!loading">
        <div class="woocommerce-checkout-steps__messages" v-if="checkoutDataStore.globalErrors.length > 0">
            <p class="woocommerce-checkout-steps__message woocommerce-checkout-steps__message--error"
               v-for="error in checkoutDataStore.globalErrors">
                {{ error }}
            </p>
        </div>
        <div class="woocommerce-checkout-steps__header">
            <h1>Checkout</h1>
            <Breadcrumb/>
        </div>

<!--        <h4>{{ stepTitle }}</h4>-->

        <UserDataSummary
            @edit-email="checkoutDataStore.currentStep = 'email'"
            @edit-shipping="checkoutDataStore.mustRestoreAddressData = true; checkoutDataStore.currentStep = 'address'"
        />
        <!-- L'utente non è loggato, deve inserire l'email: -->
        <SignInLanding
            v-if="checkoutDataStore.currentStep == 'email'"
            @email-submitted="onEmailSubmitted"
        />
        <!-- L'utente ha inserito la mail, ed è uscito che è un utente già registrato: -->
        <SignInByPassword
            v-else-if="checkoutDataStore.currentStep == 'password'"
            @user-signed-in="onUserSignedId"
        />
        <!-- L'utente deve inserire i campi di profilo addizionali -->
        <ProfileForm
            v-else-if="checkoutDataStore.currentStep == 'profile'"
            @profile-data-submitted="onProfileDataSubmitted"
        />
        <AddressesForm
            v-else-if="checkoutDataStore.currentStep == 'address'"
            @address-data-submitted="onAddressDataSubmitted"
        />
        <Pay
            v-else-if="checkoutDataStore.currentStep == 'pay'"
        />
    </div>
    <div class="woocommerce-checkout-steps__left" v-else>
        Caricamento...
    </div>

    <div class="woocommerce-checkout-steps__right">
        <OrderReview/>
    </div>
</template>

<style lang="scss">
@use "./sass/main";


</style>