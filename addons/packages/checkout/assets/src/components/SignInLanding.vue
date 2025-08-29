<script setup lang="ts">
import {computed, onMounted, ref} from 'vue';
import {WPUser} from "@/services/wp/wpuser";
import {debugLog} from "@/utils/helpers/debug.ts";
import {useCheckoutDataStore} from "@/stores/checkoutData.ts";
import {useI18n} from "vue-i18n";
import {getBackEndData} from "@/services/wp/backendData.ts";

const emit = defineEmits<{
    (e: 'emailSubmitted', email: string, profileFound: boolean): void
    (e: 'accountRegistered', email: string, id: number): void
}>();

const { t } = useI18n();

const checkoutDataStore = useCheckoutDataStore();

const loading = ref(false);

const email = ref('');

const useProceedAsGuest = computed(() => {
    return getBackEndData().use_proceed_as_guest;
});
const continueAsGuest = ref(false);

async function checkEmail() {
    debugLog('<SignInLanding> checkEmail() -> checking', email.value);
    checkoutDataStore.cleanErrors();
    try {
        loading.value = true;
        // Check if email is valid
        const isValidEmail = /.+@.+\..+/.test(email.value);
        if (!isValidEmail) {
            throw new Error(t('Email is not valid'));
        }
        // Check if email is registered
        const isEmailRegistered = await new WPUser().checkIfEmailIsRegistered(email.value);
        checkoutDataStore.continueAsGuest = continueAsGuest.value;
        if(!continueAsGuest.value){
            // The user typed the email and clicked on "Proceed", so...
            if (isEmailRegistered) {
                // Account found
                debugLog('<SignInLanding> checkEmail() -> emailSubmitted -> wp profile found');
                emit('emailSubmitted', email.value, true);
            }else {
                // No account
                debugLog('<SignInLanding> checkEmail() -> emailSubmitted -> wp profile not found');
                emit('emailSubmitted', email.value, false);
            }
        }else{
            // The user typed the email and clicked on "Proceed as guest", so...
            if (isEmailRegistered) {
                throw new Error(t('Email already in use'));
            }
            emit('emailSubmitted', email.value, false);
        }
        loading.value = false;
    } catch (error: any) {
        loading.value = false;
        checkoutDataStore.addError(error.message);
    }
}

onMounted(() => {
    if(checkoutDataStore.hasEmail){
        email.value = checkoutDataStore.userEmail;
    }
    checkoutDataStore.setUserEmail('');
})
</script>

<template>
    <div class="woocommerce-checkout-steps__block">
        <div class="checkout woocommerce-checkout">
            <form id="login">
                <div class="woocommerce-billing-fields__field-wrapper">
                    <div class="form-row form-row-wide">
                        <input type="email" placeholder="" id="email" v-model="email" :disabled="loading">
                        <label for="email">{{ $t('Insert your email') }} *</label>
                    </div>
                </div>
                <div class="woocommerce-checkout-steps__btn-group">
                    <input type="submit" :value="t('Procedi')" class="btn btn--primary" :disabled="loading" @click.prevent="checkEmail()">
                    <input v-if="useProceedAsGuest" type="submit" :value="t('Proceed as guest')" class="btn btn--secondary" :disabled="loading" @click.prevent="continueAsGuest = true; checkEmail()">
                </div>
            </form>
        </div>
    </div>
</template>
<style lang="scss">
</style>