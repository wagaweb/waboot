<div class="checkout-login__modal">
    <div class="checkout-login__card">
        <button class="checkout-login__close">&times;</button>

        <h3>Login</h3>
        <?php
        woocommerce_login_form(
            array(
/*                'message'  => esc_html__( 'If you have shopped with us before, please enter your details below. If you are a new customer, please proceed to the Billing section.', 'woocommerce' ),*/
                'redirect' => wc_get_checkout_url(),
                'hidden'   => false,
            )
        );
        ?>
    </div>
</div>