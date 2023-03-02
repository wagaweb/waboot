import $ from 'jquery';
import {elementAvailable} from "../../../../assets/src/js/utils/utils";

export function initCustomCheckoutActions(){
    //hideAddressCheckoutForm();
    //handleFirstPurchaseButton();
    checkoutLoginModal();
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

function checkoutLoginModal() {
    $('body.page-checkout').addClass('checkout-single-step');
    $(".main").css("padding-top", 0);
    $('.checkout-login__toggle').on('click', function () {
        $('.checkout-login__modal').addClass('open');
        $('body.page-checkout').addClass('overflow-hidden');
    });

    $('.checkout-login__close').on('click', function () {
        $('.checkout-login__modal').removeClass('open');
        $('body.page-checkout').removeClass('overflow-hidden');
    });
    $('.checkout-login__modal').on('click',function () {
        if($(event.target).hasClass('checkout-login__modal')) {
            $('.checkout-login__modal').removeClass('open');
            $('body.page-checkout').removeClass('overflow-hidden');
        }
    });
}