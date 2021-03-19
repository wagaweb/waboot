export default class{

    constructor(){
        let $ = jQuery;
        this.hideAddressCheckoutForm();
        this.handleFirstPurchaseButton();
    }

    hideAddressCheckoutForm() {
        let $ = jQuery;
        let loginStep = $('.step-login');
        if(loginStep.length > 0) {
            $('form.woocommerce-checkout').hide();
        }
    }

    handleFirstPurchaseButton(){
        let $ = jQuery;
        $('[data-action="first-purchase"]').on('click', function(){
            $('form.woocommerce-checkout').show();
            $('.step-login').hide();
        });
    }

}