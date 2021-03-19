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
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </a>

    <div class="header__navigation" role="navigation">
        <?php wp_nav_menu([
            'theme_location' => 'main',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters('waboot/navigation/main/class', 'navigation navbar-nav')
        ]); ?>
    </div>

    <div class="header__icons shop__icons">

        <?php if(is_checkout()) : ?>

            <a href="<?php echo wc_get_page_permalink( 'cart' ); ?>"><i class="fal fa-chevron-left"></i> Torna al carrello</a>
            <!--<a href="<?php // echo wc_get_page_permalink( 'shop' ); ?>"><i class="fal fa-chevron-left"></i> Torna allo shop</a>-->

        <?php else : ?>

            <a href="javascript:;" class="header__link--search slidein-search__toggle" data-open-sidenav=".sidesearch">
                <i class="fal fa-search"></i>
            </a>
            <a href="/my-account">
                <i class="fal fa-user"></i>
            </a>
            <a href="/wishlist">
                <i class="fal fa-heart"></i>
            </a>

            <?php if(!is_cart() && !is_checkout()) : ?>
                <a href="javascript:;" class="minicart-toggle" id="minicart-toggle" data-open-minicart=".minicart">
                    <span class="minicart__counter" data-cart-items></span>
                    <i class="fal fa-shopping-bag"></i>
                </a>
            <?php endif; ?>

        <?php endif; ?>

    </div>

</div>
