<script setup lang="ts">
import Breadcrumb from './components/Breadcrumb.vue'
import SignInLanding from "./components/SignInLanding.vue";
import SignInByPassword from "./components/SignInByPassword.vue";
import {computed, onMounted, ref} from 'vue';
import {useCheckoutDataStore} from "./stores/checkoutData";
import {useBreadCrumbStore} from "./stores/breadcrumb";
import Pay from "./components/Pay.vue";
import type {fetchedUserData, userBillingData, userShippingData} from "../env";
import OrderReview from "./components/OrderReview.vue";
import {debugLog} from "@/utils/helpers/debug.ts";
import {wpUserAPI} from "@/services/wp/user.ts";
import ProfileAndBillingForm from "@/components/ProfileAndBillingForm.vue";
import ShippingAddressesForm from "@/components/ShippingAddressesForm.vue";
import UserDataSummary from "@/components/UserDataSummary.vue";
import {useI18n} from "vue-i18n";

const { t } = useI18n();

const checkoutDataStore = useCheckoutDataStore();
const breadCrumbStore = useBreadCrumbStore();

const stepTitle = computed(() => {
    switch(breadCrumbStore.currentStep){
        case 1:
            return t('Contact info');
        case 2:
            return t('Address');
        case 3:
            return t('Payment')
    }
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
            checkoutDataStore.setUserAsLoggedIn();
            checkoutDataStore.setUserId(userData.id);
            checkoutDataStore.setUserEmail(userData.billing_data.email);
            checkoutDataStore.setBillingData(userData.billing_data);
            checkoutDataStore.setShippingData(userData.shipping_data);
            if (!checkoutDataStore.isBillingDataComplete) {
                checkoutDataStore.currentStep = 'profile';
            } else {
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

function onEmailSubmitted(email: string, profileFound: boolean, isGuest: boolean) {
    debugLog('<App> onEmailSubmitted(), profileFound?', profileFound);
    checkoutDataStore.setUserEmail(email);
    checkoutDataStore.wpProfileFound = profileFound;
    if (profileFound) {
        checkoutDataStore.currentStep = 'password';
    } else {
        checkoutDataStore.currentStep = 'profile';
    }
}

function onUserSignedId(userData: fetchedUserData) {
    location.reload(); //just reload for now
}

function onProfileDataSubmitted(billingData: userBillingData) {
    debugLog('<App> onProfileDataSubmitted()', {billing: billingData});
    checkoutDataStore.setBillingData(billingData);
    checkoutDataStore.currentStep = 'address';
}

function onShippingAddressDataSubmitted(shippingData: userShippingData) {
    debugLog('<App> onShippingAddressDataSubmitted()', shippingData);
    checkoutDataStore.setShippingData(shippingData);
    checkoutDataStore.currentStep = 'pay';
}

function onEditAddress() {
    breadCrumbStore.goToNamedStep('addresses');
    checkoutDataStore.currentStep = 'address';
}

function onEditProfile() {
    breadCrumbStore.goToNamedStep('login');
    checkoutDataStore.currentStep = 'profile';
}

</script>

<template>
    <div class="woocommerce-checkout-steps__left" v-if="!loading">
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

        <h4>{{ stepTitle }}</h4>

        <UserDataSummary
            @edit-email="checkoutDataStore.currentStep = 'email'"
            @edit-shipping="checkoutDataStore.currentStep = 'address'"
            @edit-billing="checkoutDataStore.currentStep = 'profile'"
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
        <!-- L'utente ha inserito la mail, ma non è un utente registrato, OPPURE l'utente è già loggato, ma il profilo non è completo: -->
        <ProfileAndBillingForm
            v-else-if="checkoutDataStore.currentStep == 'profile'"
            @profile-data-submitted="onProfileDataSubmitted"
        />
        <ShippingAddressesForm
            v-else-if="checkoutDataStore.currentStep == 'address'"
            @address-data-submitted="onShippingAddressDataSubmitted"
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