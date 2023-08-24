<script setup lang="ts">
import {useCheckoutDataStore} from "@/stores/checkoutData";
import {onMounted} from "vue";

const emit = defineEmits<{
    (e: 'editAddress'): void
}>();

const checkoutDataStore = useCheckoutDataStore();

onMounted(() => {
    //@ts-ignore
    if(typeof window.jQuery === "undefined"){
        return;
    }
    //@ts-ignore
    const $ = window.jQuery;
    let $originalForm = $('#original-form-wrapper');
    let $paymentWrapper = $('#payment-wrapper');
    if($originalForm !== "undefined" && $originalForm.length > 0){
        console.log('Wrapping the original form');
        $originalForm.find('form').appendTo('#payment-wrapper');
        $paymentWrapper.find('form').find('#customer_details').hide();
        $paymentWrapper.find('form').find('#place_order').hide();
    }
});

function placeOrder(){
    //@ts-ignore
    if(typeof window.jQuery === "undefined"){
        return;
    }
    //@ts-ignore
    const $ = window.jQuery;
    let $paymentWrapper = $('#payment-wrapper');
    //Compile the WooCommerce form with current stored data
    const profileData = checkoutDataStore.profileData;
    const shippingData = checkoutDataStore.shippingData;
    $('[name=billing_first_name]').val(profileData.firstName);
    $('[name=billing_last_name]').val(profileData.lastName);
    $('[name=billing_phone]').val(profileData.phone);
    $('[name=billing_email]').val(profileData.email);
    $('[name=billing_city]').val(shippingData.city);
    $('[name=billing_state]').val(shippingData.state);
    $('[name=billing_postcode]').val(shippingData.postcode);
    $('[name=billing_address_1]').val(shippingData.address);
    $('[name=order_comments]').val(shippingData.notes);
    if(checkoutDataStore.mustRegisterNewUser){
        $('[name=createaccount]').prop('checked', true);
        $('[name=account_password]').val(checkoutDataStore.newAccountData.password);
    }

    $paymentWrapper.find('form').submit();
}

function onEditAddressClick(){
    //@ts-ignore
    if(typeof window.jQuery === "undefined"){
        return;
    }
    //@ts-ignore
    const $ = window.jQuery;
    let $originalForm = $('#original-form-wrapper');
    let $paymentWrapper = $('#payment-wrapper');
    if($originalForm !== "undefined" && $originalForm.length > 0){
        console.log('Restoring the original form');
        $paymentWrapper.find('form').appendTo('#original-form-wrapper');
        // $originalForm.find('form').find('#customer_details').show();
        // $originalForm.find('form').find('#place_order').show();
    }
    emit('editAddress');
}

</script>
<template>
    <div>
        <div class="woocommerce-checkout-steps__data">
            <h5>Indirizzo email</h5>
            <ul class="">
                <li>{{ checkoutDataStore.profileData.email }}</li>
            </ul>
        </div>
        <div class="woocommerce-checkout-steps__data">
            <h5>Indirizzo di spedizione</h5>
            <ul class="">
                <li>{{ checkoutDataStore.shippingData.address }}</li>
                <li>{{ checkoutDataStore.shippingData.city }}</li>
                <li>{{ checkoutDataStore.shippingData.state }}</li>
                <li>{{ checkoutDataStore.profileData.phone }}</li>
            </ul>
            <a class="woocommerce-checkout-steps__edit" href="#" @click.prevent="onEditAddressClick">Modifica <i class="fal fa-pencil"></i></a>
        </div>
        <h5>Modalità di pagamento</h5>
        <div id="payment-wrapper"></div>
        <form action="" class="checkout woocommerce-checkout" @submit.prevent="placeOrder">
            <input type="submit" value="Effettua ordine" class="btn btn--primary">
        </form>
    </div>
</template>