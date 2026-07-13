<?php

namespace Waboot\addons\packages\invoicing;

use Waboot\inc\core\utils\Utilities;

//Checkout and account fields
add_filter( 'woocommerce_' . "billing_" . 'fields', __NAMESPACE__.'\addBillingFields', 10, 2 );
add_filter( 'woocommerce_form_field_args', __NAMESPACE__.'\alterWoocommerceFormFieldArgs', 10, 3 );
add_filter( 'woocommerce_' . "billing_" . 'fields', __NAMESPACE__.'\moveCompanyField', 11, 2 );
add_action( 'woocommerce_checkout_after_order_review', __NAMESPACE__.'\maybeHiddenFieldsOnCheckout', 10);

//Fields management
add_filter("woocommerce_process_checkout_field_".FIELD_CUSTOMER_TYPE, __NAMESPACE__.'\addCustomerTypeToCustomerData', 10, 1);
add_filter("woocommerce_process_checkout_field_".FIELD_VAT, __NAMESPACE__.'\addVatToCustomerData', 10, 1);

//Fields backend validation
add_action("woocommerce_before_checkout_process", __NAMESPACE__.'\addCheckboxesOptionsToCustomerOnCheckout', 10);
add_filter("woocommerce_process_checkout_field_".FIELD_CUSTOMER_TYPE, __NAMESPACE__.'\validateCustomerTypeOnCheckout', 11, 1);
add_filter("woocommerce_process_checkout_field_".FIELD_FISCAL_CODE, __NAMESPACE__.'\validateFiscalCodeOnCheckout', 11, 1);
add_filter("woocommerce_process_checkout_field_".FIELD_VAT, __NAMESPACE__.'\validateVatOnCheckout', 11, 1);

//Orders related
add_action("woocommerce_checkout_update_order_meta", __NAMESPACE__.'\updateOrderMetaOnCheckout', 11, 2);

//Ajax
add_action( 'wp_ajax_validate_fiscal_code', __NAMESPACE__.'\ajaxValidateFiscalCode' );
add_action( 'wp_ajax_nopriv_validate_fiscal_code', __NAMESPACE__.'\ajaxValidateFiscalCode' );
add_action( 'wp_ajax_validate_vat', __NAMESPACE__.'\ajaxValidateEuVat' );
add_action( 'wp_ajax_nopriv_validate_vat', __NAMESPACE__.'\ajaxValidateEuVat' );

//Assets
add_filter('waboot/assets/js/main/i10n', static function($args){
    $args['params']['invoicing'] = [
        'blogurl' => get_bloginfo("wpurl"),
        'isAdmin' => is_admin(),
        'fields_id' => [
            'request_invoice' => FIELD_REQUEST_INVOICE,
            'customer_type' => FIELD_CUSTOMER_TYPE,
            'fiscal_code' => FIELD_FISCAL_CODE,
            'vat' => FIELD_VAT,
            'vies_valid_check' => FIELD_VIES_VALID_CHECK,
            'unique_code' => FIELD_UNIQUE_CODE,
            'pec' => FIELD_PEC
        ],
        'eu_vat_countries' => WC()->countries->get_european_union_countries('eu_vat'),
        'must_display_invoicing_fields' => mustForceDisplayInvoiceFieldsOnCheckout() ? 'yes' : 'no',
        'shop_billing_country' => getShopBillingCountry()
    ];
    return $args;
});

/**
 * Adds our fields to billing ones
 *
 * @hooked 'woocommerce_billing_fields'
 *
 * @param $address_fields
 * @param $country
 *
 * @return array
 */
function addBillingFields($address_fields, $country): array{
    //$req = $this->plugin->is_invoice_data_required() ? ' <abbr class="required" title="'.__("required", $this->plugin->get_textdomain()).'">*</abbr> ' : '';
    $req = '';
    //$invoice_required = get_option(Plugin::FIELD_ADMIN_REQUEST_INVOICE_CHECK,"no") === 'yes';
    $invoice_required = forceInvoiceDataFieldsOnCheckout();

    /*
     * We can't make them all required, because we need to differentiate between required fields when individual
     * and required fields when company
     */

    $request_billing = [
        FIELD_REQUEST_INVOICE => [
            'label' => _x("Request invoice", "WC Field", 'waboot'),
            'type' => 'checkbox',
            'priority' => 120,
            //'class' => ['form-row-wide']
        ]
    ];
    if(forceCustomerType() === false){
        $customer_type = [
            FIELD_CUSTOMER_TYPE => [
                'label' => _x("Customer type", "WC Field", 'waboot'),
                'type' => 'select',
                'options' => [
                    'individual' => getCustomerTypeLabel('individual'),
                    'company' => getCustomerTypeLabel('company'),
                ],
                'default' => 'individual',
                'required' => $invoice_required,
                'class' => ['wbeut-hidden'],
                'priority' => 121
            ]
        ];
    }else{
        $customer_type = [
            FIELD_CUSTOMER_TYPE => [
                'label' => _x("Customer type", "WC Field", 'waboot'),
                'type' => 'select',
                'options' => [
                    'individual' => getCustomerTypeLabel('individual'),
                    'company' => getCustomerTypeLabel('company'),
                ],
                'default' => forceCustomerType(),
                'required' => true,
                'class' => ['wbeut-hidden'],
                'priority' => 121
            ]
        ];
    }
    $vat = [
        FIELD_VAT => [
            'label' => _x("VAT", "WC Field", 'waboot').$req,
            'type' => 'text',
            'validate' => ['vat'],
            'class' => ['form-row-wide wbeut-hidden'],
            'custom_attributes' => [
                'country' => $country
            ],
            'priority' => 123
        ]
    ];
    $fiscal_code = [
        FIELD_FISCAL_CODE => [
            'label' => _x("Fiscal code", "WC Field", 'waboot').$req,
            'type' => 'text',
            'validate' => ['fiscal-code'],
            'class' => ['form-row-wide wbeut-hidden'],
            'priority' => 124
        ]
    ];
    $code = [
        FIELD_UNIQUE_CODE => [
            'label' => _x("Codice destinatario", "WC Field", 'waboot').$req, //todo: trovare traduzione inglese
            'type' => 'text',
            'class' => ['wbeut-hidden'],
            'priority' => 126
        ]
    ];
    $pec = [
        FIELD_PEC => [
            'label' => _x("PEC", "WC Field", 'waboot').$req,
            'type' => 'text',
            'class' => ['wbeut-hidden'],
            'priority' => 127
        ]
    ];
    $vies_valid_check = [
        FIELD_VIES_VALID_CHECK => [
            'label' => _x("My VAT is VIES Valid", "WC Field", 'waboot'),
            'type' => 'checkbox',
            'class' => ['wbeut-hidden'],
            'priority' => 128
        ]
    ];

    if($invoice_required){
        $address_fields = array_merge($address_fields,$customer_type,$vat,$vies_valid_check,$fiscal_code,$code,$pec);
    }else{
        $address_fields = array_merge($address_fields,$request_billing,$customer_type,$vat,$vies_valid_check,$fiscal_code,$code,$pec);
    }


    return $address_fields;
}

/**
 * Place "*" at our required fields label.
 * We can't make them all defaults, because we need to differentiate between required fields when individual and required fields when company
 *
 * @hooked 'woocommerce_form_field_args'
 */
function alterWoocommerceFormFieldArgs($args, $key, $value){
    $invoice_required = forceInvoiceDataFieldsOnCheckout();
    if(!$invoice_required){
        return $args;
    }
    if(!isFillableField($key)){
        return $args;
    }
    $args['required'] = true; //By putting true here, WooCommerce place the "*" symbol at the field label
    return $args;
}

/**
 * Move company billing field in another position
 *
 * @hooked 'woocommerce_billing_fields', 11
 *
 * @param array $billing_fields
 * @param string $country
 *
 * @return array
 */
function moveCompanyField(array $billing_fields, string $country): array {
    if(isset($billing_fields['billing_company'])){
        $company_field = ["billing_company" => $billing_fields['billing_company']];
        unset($billing_fields['billing_company']);
        $billing_fields = Utilities::associativeArrayAddElementAfter($company_field,'billing_wb_woo_fi_customer_type',$billing_fields);
        $billing_fields['billing_company']['priority'] = 125;
        $billing_fields['billing_company']['class'] = ['wbeut-hidden'];
    }
    return $billing_fields;
}

/**
 * Adds the request invoice hidden fields on checkout if the normal checkbox is not being displayed
 */
function maybeHiddenFieldsOnCheckout(){
    if(forceInvoiceDataFieldsOnCheckout()){
        ?>
        <input type="hidden" value="1" name="<?php echo FIELD_REQUEST_INVOICE; ?>">
        <?php
    }
    if(forceCustomerType() !== false){
        ?>
        <input type="hidden" value="<?php echo forceCustomerType(); ?>" name="forced_customer_type">
        <?php
    }
}

/**
 * Adds customer type to WC Customer object
 *
 * @hooked 'woocommerce_process_checkout_field_*'
 *
 * @param $customer_type
 *
 * @return mixed
 */
function addCustomerTypeToCustomerData($customer_type){
    if(isset($customer_type)){
        $field_name = FIELD_CUSTOMER_TYPE;
        WC()->customer->$field_name = $customer_type;
    }
    return $customer_type;
}

/**
 * Adds customer type to WC Customer object
 *
 * @hooked 'woocommerce_process_checkout_field_*'
 *
 * @param $vat
 *
 * @return mixed
 */
function addVatToCustomerData($vat){
    if(isset($vat)){
        $field_name = FIELD_VAT;
        WC()->customer->$field_name = $vat;
    }
    return $vat;
}

/**
 * Adds correct checkbox values to WC()->customer before checkout validation.
 * For some reason "woocommerce_checkout_update_order_review" recognize as always present the checkboxes selected at least one time.
 */
function addCheckboxesOptionsToCustomerOnCheckout(){
    $post_data = $_POST;
    $data_values = [];
    if(!isset($post_data[FIELD_REQUEST_INVOICE])){
        $data_values[FIELD_REQUEST_INVOICE] = false;
    }
    if(!isset($data_values[FIELD_VIES_VALID_CHECK])){
        $data_values[FIELD_VIES_VALID_CHECK] = false;
    }
    if(!empty($data_values)){
        injectCustomerData($data_values);
    }
}

/**
 * Performs validation on $customer_type
 *
 * @hooked 'woocommerce_process_checkout_field_*'
 *
 * @param $customer_type
 *
 * @return mixed
 */
function validateCustomerTypeOnCheckout($customer_type){
    if(!mustForceDisplayInvoiceFieldsOnCheckout()){
        return $customer_type;
    }
    if($customer_type === ""){
        wc_add_notice( apply_filters( 'wb_woo_fi/invalid_customer_type_field_notice',
            sprintf(
                _x( '%s is required.', 'WC Validation Message', 'waboot'),
                '<strong>'.__("Customer type", 'waboot').'</strong>' )
        ), 'error' );
    }
    return $customer_type;
}

/**
 * Performs validation on fiscal code
 *
 * @credit Umberto Salsi <salsi@icosaedro.it>
 *
 * @hooked 'woocommerce_process_checkout_field_*'
 *
 * @param $fiscal_code
 *
 * @return mixed
 */
function validateFiscalCodeOnCheckout($fiscal_code){
    /*$has_to_validate_fiscal_code = call_user_func(static function(){
        if(!isset($_POST[FIELD_REQUEST_INVOICE]) && !isInvoiceDataRequired()) {
            return false;
        }
        if(!isset($_POST[FIELD_CUSTOMER_TYPE]) || $_POST['billing_country'] !== 'IT' || !isInvoiceDataRequired()) {
            return false;
        }
        if($_POST[FIELD_CUSTOMER_TYPE] === "company") {
            return false;
        } //Do not verify fiscal code for companies (many companies use vat as fiscal code)
        return true;
    });

    $has_to_validate_fiscal_code = apply_filters('wb_woo_fi/checkout/must_validate_fiscal_code',$has_to_validate_fiscal_code);
    */
    if(BYPASS_VALIDATIONS){
        return $fiscal_code;
    }

    if(!isset($_POST[FIELD_REQUEST_INVOICE])){
        return $fiscal_code;
    }

    $result = Validator::validateFiscalCode($fiscal_code);
    if(!$result['is_valid']){
        wc_add_notice( apply_filters( 'wb_woo_fi/invalid_fiscal_code_notice',
            sprintf(
                _x( '%s is not valid.', 'WC Validation Message', 'waboot' ),
                '<strong>'.__("Fiscal Code", 'waboot').'</strong>'
            )
        ), 'error' );
    }
    return $fiscal_code;
}

/**
 * Performs validation on VAT
 *
 * @hooked 'woocommerce_process_checkout_field_*'
 *
 * @param $vat
 *
 * @return mixed
 */
function validateVatOnCheckout($vat){
    if(BYPASS_VALIDATIONS){
        return $vat;
    }
    if(!isset($_POST[FIELD_REQUEST_INVOICE]) && !mustForceDisplayInvoiceFieldsOnCheckout()) {
        return $vat;
    }
    if(!isset($_POST[FIELD_CUSTOMER_TYPE]) || $_POST[FIELD_CUSTOMER_TYPE] === "individual") {
        return $vat;
    }

    if($vat === ''){
        wc_add_notice( sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>'.__("VAT Number", 'waboot').'</strong>' ), 'error', array( 'id' => FIELD_VAT ) );
        return $vat;
    }

    if(isset($_POST[FIELD_VIES_VALID_CHECK])){
        //Advanced validation
        $vies_validation_flag = true;
    }else{
        //Simple validation
        $vies_validation_flag = false;
    }
    if(!Validator::validateEuVat($vat,$vies_validation_flag)){
        wc_add_notice( apply_filters( 'wb_woo_fi/invalid_vat_field_notice',
            sprintf(
                _x( '%s is not a valid.', 'WC Validation Message', 'waboot' ),
                '<strong>'.__("VAT Number", 'waboot').'</strong>'
            )
        ), 'error' );
    }
    return $vat;
}

/**
 * Adds new order meta on checkout
 *
 * @hooked 'woocommerce_checkout_update_order_meta'
 */
function updateOrderMetaOnCheckout($order_id, $posted){
    $form_vars = $_POST;

    $invoice_required = mustForceDisplayInvoiceFieldsOnCheckout();

    if( (isset($posted[FIELD_REQUEST_INVOICE]) && $posted[FIELD_REQUEST_INVOICE] == 1) || $invoice_required ){
        $new_meta = [
            FIELD_CUSTOMER_TYPE => isset($form_vars[FIELD_CUSTOMER_TYPE]) ? sanitize_text_field($form_vars[FIELD_CUSTOMER_TYPE]) : false,
            FIELD_VAT => isset($form_vars[FIELD_VAT]) ? sanitize_text_field($form_vars[FIELD_VAT]) : false,
            FIELD_FISCAL_CODE => isset($form_vars[FIELD_FISCAL_CODE]) ? sanitize_text_field($form_vars[FIELD_FISCAL_CODE]) : false,
            FIELD_PEC => isset($form_vars[FIELD_PEC]) ? sanitize_text_field($form_vars[FIELD_PEC]) : false,
            FIELD_UNIQUE_CODE => isset($form_vars[FIELD_UNIQUE_CODE]) ? sanitize_text_field($form_vars[FIELD_UNIQUE_CODE]) : false,
            FIELD_REQUEST_INVOICE => true,
        ];
    }else{
        $new_meta = [
            FIELD_REQUEST_INVOICE => false,
        ];
    }

    $new_meta = array_filter($new_meta); //remove FALSE values

    foreach ($new_meta as $k => $v){
        update_post_meta($order_id,$k,$v);
    }
}

/**
 * Ajax callback to validate a fiscal code
 */
function ajaxValidateFiscalCode(){
    if(!defined("DOING_AJAX") || !DOING_AJAX) {
        return;
    }
    $fiscal_code = $_POST['fiscal_code'] ?? false;
    if(!$fiscal_code){
        echo json_encode([
            'valid' => false,
            'error' => __("Fiscal code is not valid", 'waboot')
        ]);
        die();
    }
    $result = Validator::validateFiscalCode($fiscal_code);
    echo json_encode([
        'valid' => $result['is_valid'],
        'error' => $result['err_message']
    ]);
    die();
}

/**
 * Ajax callback to validate an EU VAT
 */
function ajaxValidateEuVat(){
    if(!defined("DOING_AJAX") || !DOING_AJAX) {
        return;
    }
    $vat = $_POST['vat'] ?? false;
    $vies_check = isset($_POST['vies_check']) && (bool)$_POST['vies_check'];
    if(!$vat){
        echo json_encode([
            'valid' => false,
            'error' => __("No valid VAT provided", 'waboot')
        ]);
        die();
    }
    $result = Validator::validateEuVat($vat,$vies_check);
    echo json_encode([
        'valid' => $result,
        'error' => !$result ? __("No valid VAT provided", 'waboot') : ""
    ]);
    die();
}