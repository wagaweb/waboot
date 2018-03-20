/**
 * Bootstrapping html elements
 */
let $ = jQuery;

$('input[type=text]').addClass('form-control');
$('input[type=select]').addClass('form-control');
$('input[type=email]').addClass('form-control');
$('input[type=tel]').addClass('form-control');
$('input[type=submit]').addClass('btn btn-primary');
$('button[type=submit]').addClass('btn btn-primary');
$('textarea').addClass('form-control');
$('select').addClass('form-control');
// Gravity Form
$('.gform_button').addClass('btn btn-primary btn-lg').removeClass('gform_button button');
$('.validation_error').addClass('alert alert-danger').removeClass('validation_error');
$('.gform_confirmation_wrapper').addClass('alert alert-success').removeClass('gform_confirmation_wrapper');
// Tables
$('table').addClass('table');
