<div class="header__inner">

    <div class="header__logo">

        <a href="<?php echo home_url( '/' ); ?>">
            <?php if ( \Waboot\inc\getLogo() !== '' ) : ?>
                <?php \Waboot\inc\theLogo(false, 'header__logo'); ?>
            <?php else : ?>
                <?php echo get_bloginfo('name'); ?>
            <?php endif; ?>
        </a>

    </div>

    <button type="button" class="header__toggle" data-toggle="collapse" data-target=".header__navigation">
        <span class="sr-only"><?php _e("Toggle navigation","waboot"); ?></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </button>


    <div class="header__navigation" role="navigation">
        <?php wp_nav_menu([
            'theme_location' => 'main',
            'depth' => 0,
            'container' => false,
            'menu_class' => apply_filters('waboot/navigation/main/class', 'navigation navbar-nav')
        ]); ?>
    </div>

</div>
