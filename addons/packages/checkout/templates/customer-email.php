<div class="customer-email__wrapper">
    <div class="customer-email__header">
        <h4><?php _e('Contacts', LANG_TEXTDOMAIN); ?></h4>
        <?php wc_print_notice( apply_filters( 'woocommerce_checkout_login_message', esc_html__( 'Already have an account?', 'woocommerce' ) ) . ' <a href="#" class="checkout-login__toggle">' . esc_html__( 'Login', 'woocommerce' ) . '</a>', 'notice' ); ?>
    </div>


    <p class="form-row">
        <label for="c-email"><?php _e('Your Email'); ?></label>
        <input autocomplete="off" id="c-email" name="c-email" type="email" value="">
    </p>
    <!--<p class="form-row">
    <label>
        <input type="checkbox" id="c-sub" name="c-sub" value="1">
        <small>Consento a ricevere via email le newsletter</small>
    </label>
    </p>-->
</div>