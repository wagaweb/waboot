<section>
    <h5>Indirizzo email</h5>
    <ul>
        <li>federica@waga.it</li>
    </ul>

    <h5>Indirizzo di spedizione</h5> <a href="#">Modifica <i class="fas fa-pencil"></i></a>
    <ul>
        <?php
        if (is_user_logged_in()) {
	        $user = wp_get_current_user();

/*	        $email = $user->user_email;
	        echo '<h5>Indirizzo e-mail:</h5>' . $email;*/

	        $shippingAddress = get_user_meta($user->ID, 'shipping_address_1', true);
	        $shippingPostCode = get_user_meta($user->ID, 'shipping_postcode', true);
	        $shippingCountry = get_user_meta($user->ID, 'shipping_country', true);
	        $shippingCity = get_user_meta($user->ID, 'shipping_city', true);
	        $phoneNumber = get_user_meta($user->ID, 'billing_phone', true);

	        if (!empty($shippingAddress)) {
		        echo '<ul>';
		        echo '<li>' . $shippingAddress . ' - ' . $shippingPostCode . '</li>';
		        if (!empty($shippingCity)) {
			        echo '<li>' . $shippingCity . '</li>';
		        }
		        if (!empty($shippingCountry)) {
			        echo '<li>' . $shippingCountry . '</li>';
		        }
		        if (!empty($phoneNumber)) {
			        echo '<li>' . $phoneNumber . '</li>';
		        }
		        echo '</ul>';
	        }
        }
        ?>
    </ul>
</section>