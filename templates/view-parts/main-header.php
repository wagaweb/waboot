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

    <a href="javascript:;" class="header__toggle header__link--nav slidein-nav__toggle" data-open-sidenav=".sidenavigation">
        <span class="sr-only"><?php _e("Toggle navigation",LANG_TEXTDOMAIN); ?></span>
        <span class="icon-bar icon-bar--top"></span>
        <span class="icon-bar icon-bar--middle"></span>
        <span class="icon-bar icon-bar--bottom"></span>
    </a>

    <div class="header__navigation" role="navigation">
        <?php wp_nav_menu([
            'theme_location' => 'main',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters('waboot/navigation/main/class', 'navigation navbar-nav')
        ]); ?>

        <!-- Search Toggle -->
        <a href="javascript:;" class="header__link--search slidein-search__toggle" data-open-sidenav=".sidesearch">
            <i class="far fa-search"></i>
        </a>
    </div>

</div>
