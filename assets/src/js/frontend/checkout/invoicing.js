import $ from 'jquery';
import {elementAvailable} from "../../utils/utils";

export function initEuVat(){
    let $checkoutForm = $('form.checkout');
    if(!elementAvailable($checkoutForm)){
        return;
    }
    //On request invoice change:
    let $requireInvoiceCheckbox = getRequestInvoiceCheckbox();
    $requireInvoiceCheckbox.on('change', function(){
        if($(this).is(':checked')){
            $(this).attr("value","1");
        }else{
            $(this).attr("value","");
        }
        toggleFields();
    });
    //On form fields change:
    $checkoutForm.on("blur change", ".input-text, select, input:checkbox", this, validateFields);
    //On Customer type change:
    $checkoutForm.on("change", ".select[name='billing_wb_woo_invoicing_customer_type']", this, toggleFields);
    //On VIES check change:
    $checkoutForm.on("change", ".input-checkbox[name='billing_wb_woo_invoicing_vies_valid']", this, function(event){
        //Do VAT validation
        let $vat = $(".input-text[name='billing_wb_woo_invoicing_vat']"),
            $vat_parent = $vat.closest( '.form-row' ),
            $vies_check = $(this);
        doFieldValidation($vat,$vat_parent,{
            action: "validate_vat",
            vat: $vat.val(),
            vies_check: $vies_check.is(":checked") ? 1 : 0
        });
        //Trigger checkout update
        $(document.body).trigger( 'update_checkout');
    });
    //On Billing country change:
    $checkoutForm.on("change", "#billing_country", this, toggleFields);
    if($requireInvoiceCheckbox.is(':checked')){
        //If, for some reason, the request invoice checkbox is already checked, trigger a change event
        $requireInvoiceCheckbox.trigger('change');
    }
    if(mustDisplayInvoicingFields()){
        $(".select[name='billing_wb_woo_invoicing_customer_type']").trigger('change');
    }
    if(getForcedCustomerType() !== false){
        $(".select[name='billing_wb_woo_invoicing_customer_type']").val(getForcedCustomerType()).trigger('change');
    }
}

/**
 *
 * @return {false|Object}
 */
function getBackEndData(){
    if(typeof backendData === 'undefined'){
        return false;
    }
    if(typeof backendData.invoicing === 'undefined'){
        return false;
    }
    return backendData.invoicing;
}

/**
 *
 * @return {boolean}
 */
function mustDisplayInvoicingFields(){
    let backEndData = getBackEndData();
    if(!backEndData){
        return false;
    }
    return backEndData.must_display_invoicing_fields === 'yes';
}

function showCustomerTypeSelection(){
    let $customerTypeSelectWrapper = $('#billing_wb_euvat_customer_type_field');
    if(!elementAvailable($customerTypeSelectWrapper)){
        return;
    }
    $customerTypeSelectWrapper.show();
}

/**
 * Validate field callback.
 *
 * This mirror the format of validate_fields() in WooCommerce checkout.js
 */
function validateFields(){
    "use strict";
    let $el = $( this ),
        $parent = $el.closest( '.form-row' );

    if( $parent.is( '.woocommerce-invalid' ) ){
        if ( $parent.is( '.validate-fiscal-code' ) ) {
            doFieldValidation($el,$parent,{
                action: "validate_fiscal_code",
                fiscal_code: $el.val()
            });
        }

        if ( $parent.is( '.validate-vat' ) ) {
            let $vies_check = $("#billing_wb_woo_invoicing_vies_valid");
            doFieldValidation($el,$parent,{
                action: "validate_vat",
                vat: $el.val(),
                vies_check: $vies_check.is(":checked") ? 1 : 0
            });
        }
    }
}

/**
 * Perform custom field validation
 *
 * @param $el
 * @param $parent
 * @param data
 */
function doFieldValidation($el,$parent,data){
    let $order_review = $('.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table'),
        validated = true;

    $order_review.block({
        message: null,
        overlayCSS: {
            background: '#fff',
            opacity: 0.6
        }
    });

    $parent.block({
        message: null,
        overlayCSS: {
            background: '#fff',
            opacity: 0.6
        }
    });

    $.ajax(backendData.ajax_url,{
        data: data,
        dataType: "json",
        method: "POST"
    }).done(function(data, textStatus, jqXHR){
        if(typeof data === "object"){
            if(!data.valid){
                validated = false;
            }
        }
        if(validated){
            $parent.removeClass( 'woocommerce-invalid validate-required woocommerce-invalid-required-field' ).addClass( 'woocommerce-validated' );
        }else{
            $parent.removeClass( 'woocommerce-validated' ).addClass( 'validate-required woocommerce-invalid woocommerce-invalid-required-field' );
        }
        $order_review.unblock();
        $parent.unblock();
    }).fail(function(jqXHR, textStatus, errorThrown){
        $parent.unblock();
    });
}

/**
 * Toggle visibility to fiscal code and vat number.
 *
 * @param event
 */
function toggleFields(event){
    "use strict";
    let $customer_type = $("#billing_wb_woo_invoicing_customer_type_field"),
        current_customer_type = $(".select[name='billing_wb_woo_invoicing_customer_type'] option:selected").val(),
        current_country = $("#billing_country").val(),
        $request_invoice_check = $(".input-checkbox[name='billing_wb_woo_invoicing_request_invoice']");

    if(elementAvailable($request_invoice_check) && !$request_invoice_check.is(":checked") ){
        hideAll();
        return;
    }

    if(current_customer_type === undefined){
        return;
    }

    if($customer_type.is(".wbeut-hidden")){
        showCustomerType();
    }

    if(current_country === "IT"){
        showFiscalCode();
    }else{
        hideFiscalCode();
    }

    let $fiscal_code = $("#billing_wb_woo_invoicing_fiscal_code_field");
    let $vat = $("#billing_wb_woo_invoicing_vat_field");

    switch(current_customer_type){
        case 'individual':
            hideVat();
            hideViesCheck();
            hideCompany();
            hideUniqueCode();
            hidePec();
            $fiscal_code.find('span.optional').hide();
            $vat.find('span.optional').show();
            break;
        case 'company':
            showVat();
            let backEndData = getBackEndData();
            if(backEndData !== false){
                if(current_country != backEndData.shop_billing_country && $.inArray(current_country,backEndData.eu_vat_countries) != -1){
                    showViesCheck();
                }
            }
            showCompany();
            showUniqueCode();
            showPec();
            $fiscal_code.find('span.optional').show();
            $vat.find('span.optional').hide();
            break;
    }

    $(document.body).trigger( 'update_checkout');
}

/**
 * Shows customer type
 * @param show
 * @param mandatory
 */
function showCustomerType(show = true, mandatory = true){
    if(isForcedCustomerType()){
        return;
    }
    let $customer_type = $("#billing_wb_woo_invoicing_customer_type_field");
    if(show){
        $customer_type.removeClass("wbeut-hidden woocommerce-validated");
        if(mandatory){
            $customer_type.addClass('validate-required woocommerce-invalid-required-field woocommerce-invalid');
        }
    }else{
        $customer_type.addClass("wbeut-hidden");
        if(mandatory){
            $customer_type.removeClass('validate-required woocommerce-invalid-required-field woocommerce-invalid');
        }
    }
}

/**
 * Hides the customer type
 */
function hideCustomerType(){
    showCustomerType(false);
}

/**
 * Shows fiscal code
 * @param show
 * @param mandatory
 */
function showFiscalCode(show = true, mandatory = true){
    let $fiscal_code = $("#billing_wb_woo_invoicing_fiscal_code_field");
    if(show){
        $fiscal_code.removeClass("wbeut-hidden woocommerce-validated");
        if(mandatory){
            $fiscal_code.addClass('validate-required validate-fiscal-code woocommerce-invalid-required-field woocommerce-invalid');
        }
    }else{
        $fiscal_code.addClass("wbeut-hidden");
        if(mandatory){
            $fiscal_code.removeClass('validate-required validate-fiscal-code woocommerce-invalid-required-field woocommerce-invalid');
        }
    }

    //v2.1.6 - Do not verify fiscal code for companies (many companies use vat as fiscal code)
    let $customer_type = $("#billing_wb_woo_invoicing_customer_type_field");
    if($customer_type.find("select").val() === "company"){
        $fiscal_code.removeClass('validate-fiscal-code');
    }
}

/**
 * Hides fiscal codes
 */
function hideFiscalCode(){
    showFiscalCode(false);
}

/**
 * Shows VAT
 * @param show
 */
function showVat(show = true){
    let $vat = $("#billing_wb_woo_invoicing_vat_field");
    if(show){
        $vat.removeClass("wbeut-hidden woocommerce-validated").addClass('validate-required validate-vat woocommerce-invalid-required-field woocommerce-invalid');
    }else{
        $vat.addClass("wbeut-hidden").removeClass('validate-required validate-vat woocommerce-invalid-required-field woocommerce-invalid');
    }
}

/**
 * Hides VAT
 */
function hideVat(){
    showVat(false)
}

/**
 * Shows VIES Check
 * @param show
 */
function showViesCheck(show = true){
    let $vies_check = $("#billing_wb_woo_invoicing_vies_valid_field");
    if(show){
        $vies_check.removeClass("wbeut-hidden");
    }else{
        $vies_check.addClass("wbeut-hidden").attr("checked",false);
    }
}

/**
 * Hides VIES Check
 */
function hideViesCheck(){
    showViesCheck(false);
}

/**
 * Show company field
 */
function showCompany(show = true){
    let $company_input = $("#billing_company_field");
    if(show){
        $company_input.removeClass("wbeut-hidden woocommerce-validated").addClass('validate-required woocommerce-invalid-required-field woocommerce-invalid');
    }else{
        $company_input.addClass("wbeut-hidden").removeClass('validate-required woocommerce-invalid-required-field woocommerce-invalid');
    }
}

/**
 * Hides company field
 */
function hideCompany(){
    showCompany(false);
}

/**
 * Toggle unique code
 * @param show
 */
function showUniqueCode(show = true){
    let $unique_code = $("#billing_wb_woo_invoicing_unique_code_field");
    if(show){
        $unique_code.removeClass("wbeut-hidden");
    }else{
        $unique_code.addClass("wbeut-hidden");
    }
}

/**
 * Hides unique code
 */
function hideUniqueCode(){
    showUniqueCode(false);
}

/**
 * Toggle pec
 * @param show
 */
function showPec(show = true){
    let $unique_code = $("#billing_wb_woo_invoicing_pec_field");
    if(show){
        $unique_code.removeClass("wbeut-hidden");
    }else{
        $unique_code.addClass("wbeut-hidden");
    }
}

/**
 * Hides unique code
 */
function hidePec(){
    showPec(false);
}

/**
 * Hides all fields
 */
function hideAll(){
    hideCustomerType();
    hideFiscalCode();
    hideVat();
    hideViesCheck();
    hideCompany();
    hideUniqueCode();
    hidePec();
}

/**
 *
 * @return {jQuery|HTMLElement|*}
 */
function getRequestInvoiceCheckbox(){
    return $('input[name="billing_wb_woo_invoicing_request_invoice"]');
}

/**
 *
 * @return {boolean}
 */
function isForcedCustomerType(){
    let $customerTypeInput = $('[name="forced_customer_type"]');
    return elementAvailable($customerTypeInput) && $customerTypeInput.attr('type') === 'hidden';
}

/**
 *
 * @return {boolean|string}
 */
function getForcedCustomerType(){
    if(!isForcedCustomerType()){
        return false;
    }
    return $('[name="forced_customer_type"]').val();
}