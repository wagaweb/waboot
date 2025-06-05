<div class="slidein navigation-mobile" data-slidein-nav data-slidein-toggle="#slidein-nav__toggle">
    <div class="navigation-mobile__inner" aria-label="Mobile Navigation">
        <?php wp_nav_menu([
            'theme_location' => 'main',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters('waboot/navigation/main/class', 'navigation navbar-nav')
        ]); ?>
    </div>

    <button class="navigation-mobile__close" data-slidein-close aria-label="Close"><i class="fal fa-times"></i></button>
</div>
