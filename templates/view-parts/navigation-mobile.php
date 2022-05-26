<div class="slidein navigation-mobile" data-slidein-nav data-slidein-toggle="#slidein-nav__toggle">

    <?php if ( \Waboot\inc\getLogo() !== '' ) : ?>
        <a class="logo--mobile" href="<?php echo home_url( '/' ); ?>">
            <?php \Waboot\inc\theLogo(false, 'header__logo'); ?>
        </a>
    <?php endif; ?>

    <div class="navigation-mobile__inner">        
        <?php wp_nav_menu([
            'theme_location' => 'main',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters('waboot/navigation/main/class', 'navigation navbar-nav')
        ]); ?>

        <?php get_search_form(); ?>

        <?php wp_nav_menu([
            'theme_location' => 'main',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters('waboot/navigation/main/class', 'navigation navbar-nav')
        ]); ?>
    </div>

</div>
