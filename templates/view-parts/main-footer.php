<div class="footer__inner">
    <?php
    $blockContent = parse_blocks( get_post_field( 'post_content', 1950 )) ;
    echo render_block( $blockContent[0] );
    ?>
</div>

<div class="footer__closure">
    <div class="footer__left">
        <div class="footer__copy">© <?php echo date( 'Y' ); ?>. <?php echo get_bloginfo(); ?>. All Rights Reserved.</div>

        <?php
        wp_nav_menu( [
            'theme_location' => 'bottom',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters('waboot/navigation/main/class', 'footer__menu footer__menu--bottom')
        ] ); ?>
    </div>

    <div class="footer__right">
        <?php wp_nav_menu( [
            'theme_location' => 'social',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters('waboot/navigation/main/class', 'footer__menu footer__menu--social')
        ] );
        ?>
    </div>
</div>
