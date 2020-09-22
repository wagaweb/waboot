<div class="slidein navigation-mobile" data-slidein-nav data-slidein-toggle="#slidein-nav__toggle">

    <a href="<?php echo home_url( '/' ); ?>">
        <?php if ( \Waboot\inc\getLogo() !== '' ) : ?>
            <?php \Waboot\inc\theLogo(false, 'header__logo'); ?>
        <?php else : ?>
            <?php echo get_bloginfo('name'); ?>
        <?php endif; ?>
    </a>

    <a data-slidein-close><i class="fas fa-times"></i></a>

    <div class="navigation-mobile__inner">

        <?php wp_nav_menu([
            'theme_location' => 'main',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters('waboot/navigation/main/class', 'navigation navbar-nav')
        ]); ?>

    </div>

</div>
