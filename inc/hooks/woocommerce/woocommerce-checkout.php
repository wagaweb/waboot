<?php

namespace Waboot\inc\woocommerce;

if(!\function_exists('is_woocommerce')){
    return; //Do not load any of the following if WooCommerce is not enabled
}

// Hides ALL shipping rates in ALL zones when Free Shipping is available
add_filter( 'woocommerce_package_rates', function( $rates ) {
    $free = array();
    foreach ( $rates as $rate_id => $rate ) {
        if ( 'free_shipping' === $rate->method_id ) {
            $free[ $rate_id ] = $rate;
            break;
        }
    }
    return ! empty( $free ) ? $free : $rates;
}, 100 );

// Adds Payment Request button (Apple Pay) on the Checkout page
add_filter('wc_stripe_show_payment_request_on_checkout', '__return_true');

add_action('woocommerce_before_checkout_form', function () {
	get_template_part('templates/view-parts/woocommerce/checkout/steps');
	//get_template_part('templates/view-parts/woocommerce/checkout/welcome');
	//get_template_part('templates/view-parts/woocommerce/checkout/welcome-back');
	//get_template_part('templates/view-parts/woocommerce/checkout/user-registration');
	get_template_part('templates/view-parts/woocommerce/checkout/billing-addresses');
}, 5);

add_filter( 'woocommerce_checkout_fields', function($checkout_fields) {
	unset( $checkout_fields[ 'billing' ][ 'billing_email' ] );
	return $checkout_fields;
}, 9999 );


add_filter('woocommerce_checkout_fields', function($checkout_fields) {
	$checkout_fields['billing']['billing_email']['priority'] = 5;
	$checkout_fields['billing']['billing_phone']['priority'] = 35;
	return $checkout_fields;
});

add_filter('woocommerce_checkout_fields', function($checkout_fields) {
	$checkout_fields[ 'billing' ][ 'billing_email' ][ 'class' ][0] = 'form-row-first';
	$checkout_fields[ 'billing' ][ 'billing_phone' ][ 'class' ][0] = 'form-row-last';
	$checkout_fields[ 'billing' ][ 'billing_country' ][ 'class' ][0] = 'form-row-first';
	$checkout_fields[ 'billing' ][ 'billing_address_1' ][ 'class' ][0] = 'form-row-last';
	$checkout_fields[ 'billing' ][ 'billing_postcode' ][ 'class' ][0] = 'form-row-first';
	return $checkout_fields;
});

// Funzione per nascondere campi nel checkout in base alle condizioni
add_action('wp', function () {
	// Verifica se l'utente è loggato
	if (is_user_logged_in()) {
		// Nascondi il campo "Nome" nel checkout
		add_filter('woocommerce_checkout_fields', function ($fields) {
			unset($fields['billing']['billing_first_name']);
			return $fields;
		});

		// Nascondi il campo "Cognome" nel checkout
		add_filter('woocommerce_checkout_fields', function ($fields) {
			unset($fields['billing']['billing_last_name']);
			return $fields;
		});

		// Nascondi il campo "Password" nel checkout
		add_filter('woocommerce_checkout_fields', function ($fields) {
			unset($fields['billing']['account_password']);
			return $fields;
		});

		add_filter('woocommerce_checkout_fields', function ($fields) {
			unset($fields['billing']['billing_address_2']);
			return $fields;
		});

		$user_id = get_current_user_id();

		$billingPhone = get_user_meta($user_id, 'billing_phone', true);
		$billingCountry = get_user_meta($user_id, 'billing_country', true);
		$billingAddress1 = get_user_meta($user_id, 'billing_address_1', true);
		$billingPostCode = get_user_meta($user_id, 'billing_postcode', true);
		$billingCity = get_user_meta($user_id, 'billing_city', true);
		$billingState = get_user_meta($user_id, 'billing_state', true);

		if (!empty($billingPhone)) {
			add_filter('woocommerce_checkout_fields', function ($fields) {
				unset($fields['billing']['billing_phone']);
				return $fields;
			});
		}

		if (!empty($billingCountry)) {
			add_filter('woocommerce_checkout_fields', function ($fields) {
				unset($fields['billing']['billing_country']);
				return $fields;
			});
		}

		if (!empty($billingAddress1)) {
			add_filter('woocommerce_checkout_fields', function ($fields) {
				unset($fields['billing']['billing_address_1']);
				return $fields;
			});
		}

		if (!empty($billingPostCode)) {
			add_filter('woocommerce_checkout_fields', function ($fields) {
				unset($fields['billing']['billing_postcode']);
				return $fields;
			});
		}

		if (!empty($billingCity)) {
			add_filter('woocommerce_checkout_fields', function ($fields) {
				unset($fields['billing']['billing_city']);
				return $fields;
			});
		}

		if (!empty($billingState)) {
			add_filter('woocommerce_checkout_fields', function ($fields) {
				unset($fields['billing']['billing_state']);
				return $fields;
			});
		}
	}
});
