<div class="slidein navigation-mobile" id="mobileNav" data-slidein-nav data-slidein-toggle="#slidein-nav__toggle" inert>
    <div class="navigation-mobile__inner">
        <?php wp_nav_menu([
            'theme_location' => 'main',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters('waboot/navigation/main/class', 'navigation navbar-nav')
        ]); ?>
    </div>

    <button class="navigation-mobile__close" data-slidein-close aria-label="Close Mobile Navigation" aria-expanded="false" aria-controls="mobileNav">
        <i class="fal fa-times"></i>
    </button>
</div>
