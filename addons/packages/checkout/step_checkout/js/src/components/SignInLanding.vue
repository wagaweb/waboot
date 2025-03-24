<script setup lang="ts">
import {onMounted, ref} from 'vue';
import {WPUser} from "@/services/wp/wpuser";
import {debugLog} from "@/utils/helpers/debug.ts";
import {useCheckoutDataStore} from "@/stores/checkoutData.ts";
import {useI18n} from "vue-i18n";

const emit = defineEmits<{
    (e: 'emailSubmitted', email: string, profileFound: boolean, isGuest: boolean): void
    (e: 'accountRegistered', email: string, id: number): void
}>();

const { t } = useI18n();

const checkoutDataStore = useCheckoutDataStore();

const loading = ref(false);

const email = ref('');

async function checkEmail(continueAsGuest: boolean = false) {
    debugLog('<SignInLanding> checkEmail() -> checking', email.value);
    checkoutDataStore.cleanErrors();
    try {
        checkoutDataStore.isGuest = continueAsGuest;
        loading.value = true;
        // Check if email is valid
        const isValidEmail = /.+@.+\..+/.test(email.value);
        if (!isValidEmail) {
            throw new Error('Email is not valid');
        }
        // Check if email is registered
        const isEmailRegistered = await new WPUser().checkIfEmailIsRegistered(email.value);
        if (isEmailRegistered) {
            // Account found
            debugLog('<SignInLanding> checkEmail() -> emailSubmitted -> wp profile found');
            emit('emailSubmitted', email.value, true, continueAsGuest);
        }else {
            // No account
            debugLog('<SignInLanding> checkEmail() -> emailSubmitted -> wp profile not found');
            emit('emailSubmitted', email.value, false, continueAsGuest);
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
            <div class="woocommerce-billing-fields__field-wrapper">
                <div class="form-row form-row-wide">
                    <input type="email" placeholder="" id="email" v-model="email" :disabled="loading">
                    <label for="email">{{ $t('Insert your email') }} *</label>
                </div>
            </div>
            <div class="woocommerce-checkout-steps__btn-group">
                <input type="submit" :value="t('Proceed')" class="btn btn--primary" :disabled="loading" @click.prevent="checkEmail(false)">
                <input type="submit" :value="t('Proceed as guest')" class="btn btn--outline" :disabled="loading" @click.prevent="checkEmail(true)">
            </div>
        </div>
    </div>
</template>
<style lang="scss">
</style>