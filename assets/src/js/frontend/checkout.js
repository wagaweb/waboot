import $ from 'jquery';
import {elementAvailable} from "../utils/utils";

export function initCustomCheckoutActions(){
    hideAddressCheckoutForm();
    handleFirstPurchaseButton();
}

function hideAddressCheckoutForm() {
    let $loginStep = $('.step-login');
    if(!elementAvailable($loginStep)) {
        return;
    }
    $('form.woocommerce-checkout').hide();
}

function handleFirstPurchaseButton(){
    let $firstPurchaseButton = $('[data-action="first-purchase"]');
    if(!elementAvailable($firstPurchaseButton)){
        return;
    }
    $firstPurchaseButton.on('click', function(){
        $('form.woocommerce-checkout').show();
        $('.step-login').hide();
    });
}