<script setup lang="ts">
import {computed, ref} from "vue";
import type {fetchedUserData} from "../../env";
import {useCheckoutDataStore} from "@/stores/checkoutData.ts";
import {wpUserAPI} from "@/services/wp/user.ts";
import {debugLog} from "@/utils/helpers/debug.ts";
import {useI18n} from "vue-i18n";

const emit = defineEmits<{
    (e: 'userSignedIn', userData: fetchedUserData): void
}>();

const { t } = useI18n();

const checkoutDataStore = useCheckoutDataStore();

const password = ref('');
const showPassword = ref(false);
const signInErrorMessage = ref('');
const signInErrorOccurred = computed(() => signInErrorMessage.value !== '');

const loading = ref(false);

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

async function onSubmit() {
    debugLog('<SignInByPassword> onSubmit()');
    try {
        signInErrorMessage.value = '';
        checkoutDataStore.cleanErrors();
        loading.value = true;
        const response = await wpUserAPI.signIn(checkoutDataStore.userEmail as string, password.value);
        loading.value = false;
        emit('userSignedIn', response);
    } catch (error: any) {
        if('message' in error){
            signInErrorMessage.value = error.message;
        }
        debugLog('<SignInByPassword> onSubmit() ERROR', error);
        loading.value = false;
    }
}

</script>
<template>
    <div>
        <div class="panel">
            <h4>{{ $t('Welcome back!') }}</h4>
            <h5>{{ $t('Access to your account now') }}</h5>

            <div class="checkout woocommerce-checkout" :class="{'loading': loading}">
                <div class="woocommerce-billing-fields__field-wrapper">
                    <div class="form-row form-row-wide">
                        <div class="password-wrapper">
                            <input :type="showPassword ? 'text' : 'password'" placeholder="" id="password" v-model="password">
                            <label for="password">{{ $t('Type your password') }}</label>
                            <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'" @click="togglePassword"></i>
                        </div>
                        <span role="alert" v-if="signInErrorOccurred">{{ signInErrorMessage }}</span>
                        <a class="forgot-password btn btn--underline btn--muted" href="#">{{ $t('Forgot password?') }}</a>
                    </div>
                </div>
                <input type="submit" :value="t('Log in')" class="btn btn--primary" :disabled="password == '' || loading"
                       @click.prevent="onSubmit">

                <!-- https://vue-i18n.intlify.dev/guide/advanced/component.html#slots-syntax-usage -->
                <!-- @vue-ignore -->

            </div>
        </div>
    </div>
</template>
<style lang="scss">
a.forgot-password{
    display: block !important;
    text-align: left !important;
}
</style>