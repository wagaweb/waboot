<div class="footer__closure">
    <div class="footer__copy"><?php echo date( 'Y' ) ?> © <?php echo get_bloginfo(); ?> - All Rights Reserved</div>

    <?php
    wp_nav_menu([
        'theme_location' => 'bottom',
        'depth' => 0,
        'fallback_cb' => '__return_false',
        'container' => false,
        'menu_class' => apply_filters('waboot/navigation/main/class', '')
    ]); ?>

    <?php wp_nav_menu([
        'theme_location' => 'social',
        'depth' => 0,
        'fallback_cb' => '__return_false',
        'container' => false,
        'menu_class' => apply_filters('waboot/navigation/main/class', '')
        ]);
    ?>
</div>
