<section class="step-login" data-step data-step-label="<?php _e("Login",'wawoo') ?>">
    <div id="checkout_login" class="woocommerce_checkout_login">
        <div class="checkout-login">
            <div class="checkout-guest">
                <h5><?php _e("Is this the first time that you purchase?", LANG_TEXTDOMAIN) ?></h5>
                <button class="btn btn-primary" data-action="first-purchase"><?php _e("I don't have an account yet", LANG_TEXTDOMAIN) ?></button>
            </div>
            <div class="checkout-login__form">
                <h5><?php _e("Already have an account?", LANG_TEXTDOMAIN) ?></h5>
                <?php
                woocommerce_login_form(
                    array(
                        // 'message'  => __( 'If you have shopped with us before, please enter your details in the boxes below. If you are a new customer please proceed to the Billing &amp; Shipping section.', 'woocommerce' ),
                        'redirect' => wc_get_page_permalink( 'checkout' ),
                        'hidden'   => false
                    )
                ); ?>
            </div>
        </div>
    </div>
</section>
