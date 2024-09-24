<div class="header__inner">
    <div class="header__logo">
        <a href="<?php echo home_url( '/' ); ?>" aria-label="<?php echo get_bloginfo('name'); ?>">
            <?php if ( \Waboot\inc\getLogo() !== '' ) : ?>
                <?php \Waboot\inc\theLogo(false, 'header__logo'); ?>
            <?php else : ?>
                <?php echo get_bloginfo('name'); ?>
            <?php endif; ?>
        </a>
    </div>

    <button class="header__toggle header__link--nav slidein-nav__toggle" data-open-sidenav=".sidenavigation" aria-label="<?php _e("Toggle navigation",LANG_TEXTDOMAIN); ?>">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </button>

    <?php get_template_part('templates/view-parts/navigation-mobile'); ?>

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
            <a href="<?php echo wc_get_page_permalink( 'cart' ); ?>"><i class="fa-light fa-chevron-left"></i> <?php _e('Torna al carrello', LANG_TEXTDOMAIN); ?></a>
            <!--<a href="<?php // echo wc_get_page_permalink( 'shop' ); ?>"><i class="fa-light fa-chevron-left"></i> Torna allo shop</a>-->
        <?php else : ?>
            <button class="header__link--search slidein-search__toggle" data-open-sidenav=".sidesearch">
                <i class="fa-light fa-search"></i>
            </button>

            <?php get_template_part('templates/view-parts/sidesearch'); ?>

            <a href="/my-account">
                <i class="fa-light fa-user"></i>
            </a>

            <a href="/wishlist">
                <i class="fa-light fa-heart"></i>
            </a>

            <?php if(!is_cart() && !is_checkout()) : ?>
                <a href="javascript:;" class="minicart-toggle" id="minicart-toggle" data-open-minicart=".minicart">
                    <span class="minicart__counter" data-cart-items></span>
                    <i class="fa-light fa-shopping-bag"></i>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>