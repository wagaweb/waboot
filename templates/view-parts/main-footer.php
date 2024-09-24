<div class="footer__inner">
    <div class="footer__row">
        <div class="footer__col">
            <h5>Shop</h5>
            <?php wp_nav_menu([
                'theme_location' => 'footer-shop',
                'depth' => 0,
                'fallback_cb' => '__return_false',
                'container' => false,
                'menu_class' => apply_filters('waboot/navigation/main/class', 'footer__menu')
            ]); ?>
        </div>

        <div class="footer__col">
            <h5>Account</h5>
            <?php wp_nav_menu([
                'theme_location' => 'footer-account',
                'depth' => 0,
                'fallback_cb' => '__return_false',
                'container' => false,
                'menu_class' => apply_filters('waboot/navigation/main/class', 'footer__menu')
            ]); ?>
        </div>

        <div class="footer__col">
            <h5><?php _e( 'Contatti', LANG_TEXTDOMAIN ); ?></h5>
            <ul>
                <li>Via Raffaello Sanzio , 6 - Milano</li>
                <li><a href="tel:+39 02 89458072">+39 02 89458072</a></li>
                <li><a href="mailto:info@waga.it">info@waga.it</a></li>
            </ul>
        </div>

        <div class="footer__col">
            <h5>Newsletter</h5>
            <p><?php _e( 'Iscriviti alla nostra newsletter e scopri in anteprima le nuove collezioni, offerte esclusive e consigli di stile.', LANG_TEXTDOMAIN ); ?></p>

            [newsletter form]
        </div>
    </div>

    <div class="footer__social">
        <h5><?php _e( 'Rimani sempre aggiornato!', LANG_TEXTDOMAIN ); ?></h5>

        <?php wp_nav_menu([
            'theme_location' => 'social',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters('waboot/navigation/main/class', 'footer__menu')
        ]); ?>
    </div>
</div>

<div class="footer__closure">
    <div class="footer__copy">© <?php echo date( 'Y' ); ?> <?php echo get_bloginfo(); ?> - P.IVA 00000000000 - All Rights Reserved.</div>

    <ul class="footer__payment-methods">
        <li>
            <img src="<?php echo get_theme_file_uri( 'assets/images/payment-methods/logo-paypal.svg' ); ?>" alt="Paypal">
        </li>
        <li>
            <img src="<?php echo get_theme_file_uri( 'assets/images/payment-methods/logo-mastercard.svg' ); ?>" alt="Mastercard">
        </li>
        <li>
            <img src="<?php echo get_theme_file_uri( 'assets/images/payment-methods/logo-maestro.svg' ); ?>" alt="Maestro">
        </li>
        <li>
            <img src="<?php echo get_theme_file_uri( 'assets/images/payment-methods/logo-american-express.svg' ); ?>" alt="American Express">
        </li>
        <li>
            <img src="<?php echo get_theme_file_uri( 'assets/images/payment-methods/logo-visa.svg' ); ?>" alt="Visa">
        </li>
        <li>
            <img src="<?php echo get_theme_file_uri( 'assets/images/payment-methods/logo-klarna.svg' ); ?>" alt="Klarna">
        </li>
    </ul>
</div>