<script setup lang="ts">
import Breadcrumb from './components/Breadcrumb.vue'
import SignInLanding from "@/components/SignInLanding.vue";
import SignInByPassword from "@/components/SignInByPassword.vue";
import {computed, onMounted, ref, toRaw} from 'vue';
import {WPUser} from "@/services/wp/wpuser";
import UserProfileForm from "@/components/UserProfileForm.vue";
import {useCurrentUserStore} from "@/stores/currentUser";
import AddressesForm from "@/components/AddressesForm.vue";
import {useBreadCrumbStore} from "@/stores/breadcrumb";
import Pay from "@/components/Pay.vue";

const currentUserStore = useCurrentUserStore();
const breadCrumbStore = useBreadCrumbStore();

const loading = ref(false);

const userRegistered = ref(false);
const userSignedIn = computed(() => {
    return currentUserStore.isLoggedIn;
});

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
            currentUserStore.setLoggedIn();
            //todo: assegnare i dati esistenti
            mustPay.value = true;
        }else{
            mustSignIn.value = true;
        }
        loading.value = false;
    }).catch((error) => {
      console.log('App onMounted() error');
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

function onProfileDataSubmitted(formData: any){
    console.log('Profile data submitted');
    console.log(formData);
    mustEnterProfileData.value = false;
    mustEnterAddressData.value = true;
    currentUserStore.setEmail(typedInEmail.value);
    currentUserStore.setProfileData(formData);
    if(formData.createAccount){
        currentUserStore.setMustRegisterNewUser();
        currentUserStore.setUserPassword(formData.password);
    }
    breadCrumbStore.nextStep();
}

function onAddressDataSubmitted(formData: any){
    console.log('Address data submitted');
    currentUserStore.setShippingData(formData);
    mustEnterAddressData.value = false;
    mustPay.value = true;
    breadCrumbStore.nextStep();
}

</script>

<template>
  <Breadcrumb />
  <SignInLanding v-if="mustShowSignInLanding" @email-verified="onEmailVerified" />
  <SignInByPassword v-if="mustShowSignInByPassword" />
  <UserProfileForm v-if="mustShowUserProfileDataForm" :email="typedInEmail" @profile-data-submitted="onProfileDataSubmitted" />
  <AddressesForm v-if="mustShowAddressesDataForm" @address-data-submitted="onAddressDataSubmitted" />
  <Pay v-if="mustShowPay" />
</template>
