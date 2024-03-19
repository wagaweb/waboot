<div class="footer__closure">
    <div class="footer__left">
        <div class="footer__copy">© <?php echo date( 'Y' ); ?> <?php echo get_bloginfo(); ?> | All Rights Reserved.</div>
    </div>

    <div class="footer__right">
        <?php /*wp_nav_menu( [
            'theme_location' => 'social',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters('waboot/navigation/main/class', 'footer__menu footer__menu--social')
        ] );
        */?>
        <img src="<?php echo get_theme_file_uri('/assets/images/payment-methods.svg') ?>" alt="Payment Methods">
    </div>
</div>


<?php
/*wp_nav_menu( [
    'theme_location' => 'bottom',
    'depth' => 0,
    'fallback_cb' => '__return_false',
    'container' => false,
    'menu_class' => apply_filters('waboot/navigation/main/class', 'footer__menu footer__menu--bottom')
] ); */?>