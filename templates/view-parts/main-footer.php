<div class="footer__closure">
    <div class="footer__copy"><?php echo date( 'Y' ) ?> © <?php echo get_bloginfo(); ?> - <?php esc_html_e( 'All Rights Reserved', LANG_TEXTDOMAIN ); ?></div>

    <nav aria-label="<?php esc_attr_e('Menu footer', LANG_TEXTDOMAIN); ?>">
        <?php wp_nav_menu([
            'theme_location' => 'bottom',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters('waboot/navigation/main/class', '')
        ]); ?>
    </nav>
</div>
