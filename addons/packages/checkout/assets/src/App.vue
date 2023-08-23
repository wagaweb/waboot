<script setup lang="ts">
import Breadcrumb from './components/Breadcrumb.vue'
import SignInLanding from "@/components/SignInLanding.vue";
import SignInByPassword from "@/components/SignInByPassword.vue";
import {computed, onMounted, ref} from 'vue';
import {WPUser} from "@/services/wp/wpuser";
import UserProfileForm from "@/components/UserProfileForm.vue";
import {useCurrentUserStore} from "@/stores/currentUser";

const currentUserStore = useCurrentUserStore();

const loading = ref(false);

const userRegistered = ref(false);
const userSignedIn = computed(() => {
    return currentUserStore.isLoggedIn;
});

const typedInEmail = ref('');
const mustEnterPassword = ref(false);
const mustEnterProfileData = ref(false);

const WPUserConnector = new WPUser();

onMounted(() => {
    loading.value = true;
    WPUserConnector.checkIfCustomerIsLoggedIn().then((isSignedIn) => {
        if(isSignedIn){
            currentUserStore.setLoggedIn();
        }
        loading.value = false;
    }).catch((error) => {
      console.log('App onMounted() error');
      console.error(error);
    });
});

const mustShowSignInLanding = computed(() => {
    return !loading.value && !userSignedIn.value && !mustEnterPassword.value && !mustEnterProfileData.value;
});

const mustShowSignInByPassword = computed(() => {
    return !loading.value && mustEnterPassword.value;
});

const mustShowUserProfileDataForm = computed(() => {
    return !loading.value && mustEnterProfileData.value;
});

function onEmailVerified(email: string, isRegistered: boolean){
    console.log('Verified email: '+email);
    console.log('Is registered?: '+isRegistered);
    typedInEmail.value = email;
    if(isRegistered){
        mustEnterPassword.value = true;
    }else{
        mustEnterProfileData.value = true;
    }
}

function onProfileDataSubmitted(formData: object){
    console.log('Profile data submitted');
    console.log(formData);
}

</script>

<template>
  <Breadcrumb />
  <SignInLanding v-if="mustShowSignInLanding" @email-verified="onEmailVerified" />
  <SignInByPassword v-if="mustShowSignInByPassword" />
  <UserProfileForm v-if="mustShowUserProfileDataForm" :email="typedInEmail" @profile-data-submitted="onProfileDataSubmitted" />
</template>
