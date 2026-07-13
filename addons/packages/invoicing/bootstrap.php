<?php

namespace Waboot\addons\packages\invoicing;

use function Waboot\addons\getAddonDirectory;

/*
 * Settings: you can edit these
 */
const BYPASS_VALIDATIONS = false;
const FORCE_INVOICING = false;
const FORCE_CUSTOMER_TYPE = false; //'individual', 'company', or false.
/*
 * //End: Settings
 */

const FIELD_REQUEST_INVOICE = "billing_wb_woo_invoicing_request_invoice";
const FIELD_CUSTOMER_TYPE = "billing_wb_woo_invoicing_customer_type";
const FIELD_FISCAL_CODE = "billing_wb_woo_invoicing_fiscal_code";
const FIELD_VAT = "billing_wb_woo_invoicing_vat";
const FIELD_VIES_VALID_CHECK = "billing_wb_woo_invoicing_vies_valid";

const FIELD_UNIQUE_CODE = 'billing_wb_woo_invoicing_unique_code';
const FIELD_PEC = 'billing_wb_woo_invoicing_pec';

const FIELD_ADMIN_SHOP_BILLING_COUNTRY = "wb_woo_invoicing_shop_billing_country";
const FIELD_ADMIN_REQUEST_INVOICE_CHECK = "wb_woo_invoicing_request_invoice_check";
const FIELD_ADMIN_SHOP_BILLING_COUNTRY_RATE_AS_DEFAULT = "wb_woo_invoicing_shop_billing_country_is_default_rate";

require_once getAddonDirectory('invoicing').'/Validator.php';
require_once getAddonDirectory('invoicing').'/template-functions.php';

/*
 * Public hooks
 */
require_once getAddonDirectory('invoicing').'/frontend.php';

/*
 * Backend hooks
 */
require_once getAddonDirectory('invoicing').'/backend.php';