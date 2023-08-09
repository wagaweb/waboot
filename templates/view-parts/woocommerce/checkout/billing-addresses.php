<?php
$editBillingURL = get_bloginfo('url') . '/my-account/edit-address/billing/';

if (is_user_logged_in()) :
	$user = wp_get_current_user();
	$email = $user->user_email;
?>
<section class="woocommerce-checkout-billing-addresses">
    <?php if($email) : ?>
        <h5>Indirizzo email:</h5>
        <ul>
            <li><?php echo $email; ?></li>
        </ul>
    <?php endif; ?>

    <h5>Indirizzo di spedizione <a href="<?php echo $editBillingURL; ?>">Modifica <i class="fal fa-pencil"></i></a></h5>

    <ul>
        <?php
	        $billingAddress = get_user_meta($user->ID, 'billing_address_1', true);
	        $billingPostCode = get_user_meta($user->ID, 'billing_postcode', true);
	        $billingCountry = get_user_meta($user->ID, 'billing_country', true);
	        $billingCity = get_user_meta($user->ID, 'billing_city', true);
	        $billingPhone = get_user_meta($user->ID, 'billing_phone', true);

	        if (!empty($billingAddress)) {
		        echo '<li>' . $billingAddress . ' - ' . $billingPostCode . '</li>';
		        if (!empty($billingCity)) {
			        echo '<li>' . $billingCity . '</li>';
		        }
		        if (!empty($billingCountry)) {
			        echo '<li>' . $billingCountry . '</li>';
		        }
		        if (!empty($billingPhone)) {
			        echo '<li>' . $billingPhone . '</li>';
		        }
	        }
        ?>
    </ul>
</section>
<?php endif; ?>