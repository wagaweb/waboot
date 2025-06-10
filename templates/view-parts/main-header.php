<?php use Waboot\inc\Accessible_Mega_Menu_Walker;
use Waboot\inc\Walker_Accessible_Menu; ?>

<div class="header__inner">
    <div class="header__logo">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
           aria-label="Logo <?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
            <?php if( \Waboot\inc\getLogo() !== '' ) : ?>
                <?php \Waboot\inc\theLogo( false, 'header__logo' ); ?>
            <?php else : ?>
                <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
            <?php endif; ?>
        </a>
    </div>

    <!--<nav id="main-navigation" class="header__navigation" role="navigation"
         aria-label="<?php /*_e( 'Main navigation', LANG_TEXTDOMAIN ); */?>" aria-hidden="true">
        <?php /*wp_nav_menu( [
            'theme_location' => 'main',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters( 'waboot/navigation/main/class', 'navigation navbar-nav' ),
            'walker' => new Walker_Accessible_Menu(),
        ] ); */?>
    </nav>-->

    <nav id="main-navigation" class="header__megamenu" role="navigation"
         aria-label="<?php _e( 'Megamenu navigation', LANG_TEXTDOMAIN ); ?>" aria-hidden="true">
        <?php wp_nav_menu( [
            'theme_location' => 'megamenu',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters( 'waboot/navigation/main/class', 'navigation navbar-nav' ),
            'walker' => new Accessible_Mega_Menu_Walker(),
        ] ); ?>
    </nav>

    <div class="header__icons shop__icons" aria-label="<?php _e( 'Shop icons and actions', LANG_TEXTDOMAIN ); ?>">
        <?php if( is_checkout() ) : ?>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'cart' ) ); ?>">
                <i class="fal fa-chevron-left" aria-hidden="true"></i>
                <?php _e( 'Torna al carrello', LANG_TEXTDOMAIN ); ?>
            </a>
            <!-- <a href="<?php // echo wc_get_page_permalink( 'shop' ); ?>"><i class="fal fa-chevron-left"></i> Torna allo shop</a> -->
        <?php else : ?>
            <button
                    class="header__link--search slidein-search__toggle"
                    aria-expanded="false"
                    aria-controls="search-sidenav"
                    aria-label="<?php _e( 'Toggle search', LANG_TEXTDOMAIN ); ?>"
                    data-open-sidenav=".sidesearch"
            >
                <i class="fal fa-search" aria-hidden="true"></i>
            </button>

            <?php get_template_part( 'templates/view-parts/sidesearch' ); ?>

            <a href="/my-account" aria-label="<?php _e( 'Account', LANG_TEXTDOMAIN ); ?>">
                <i class="fal fa-user" aria-hidden="true"></i>
            </a>

            <a href="/wishlist" aria-label="<?php _e( 'Wishlist', LANG_TEXTDOMAIN ); ?>">
                <i class="fal fa-heart" aria-hidden="true"></i>
            </a>

            <?php if( !is_cart() && !is_checkout() ) : ?>
                <button
                        class="minicart-toggle"
                        id="minicart-toggle"
                        aria-expanded="false"
                        aria-controls="minicart"
                        aria-label="<?php _e( 'Toggle mini cart', LANG_TEXTDOMAIN ); ?>"
                        data-open-minicart=".minicart"
                >
                    <span class="minicart__counter" data-cart-items aria-live="polite" aria-atomic="true"></span>
                    <i class="fal fa-shopping-bag" aria-hidden="true"></i>
                </button>

                <?php require_once get_template_directory() . '/addons/packages/cart/templates/minicart.php'; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <button
            class="header__toggle header__link--nav slidein-nav__toggle"
            aria-expanded="false"
            aria-controls="main-navigation"
            aria-label="<?php _e( 'Toggle navigation menu', LANG_TEXTDOMAIN ); ?>"
            data-open-sidenav=".sidenavigation"
    >
        <span class="sr-only"><?php _e( "Toggle navigation", LANG_TEXTDOMAIN ); ?></span>
        <span class="icon-bar" aria-hidden="true"></span>
        <span class="icon-bar" aria-hidden="true"></span>
        <span class="icon-bar" aria-hidden="true"></span>
    </button>

    <?php get_template_part( '/templates/view-parts/navigation-mobile' ); ?>
</div>
