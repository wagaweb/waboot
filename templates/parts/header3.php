<?php
/**
 * The template used to load the Main Menu in header*.php
 *
 * @package Waboot
 * @since Waboot 1.0
 */
?>
    <!-- Main menu -->

    <nav id="navbar-3" class="<?php echo apply_filters( 'waboot_main_navbar_class' , 'navbar navbar-default main-navigation' ); ?>" role="navigation">

        <div id="logo">
            <?php if ( of_get_option( 'waboot_logo_in_navbar' ) != "" ) : ?>
                <a href="<?php echo home_url( '/' ); ?>"><img src="<?php echo of_get_option( 'waboot_logo_in_navbar' ); ?>"> </a>
            <?php else : ?>
                <?php
                do_action( 'waboot_site_title' );
                // do_action( 'waboot_site_description' );
                ?>
            <?php endif; ?>

            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="offcanvas" data-target=".navbar-mobile-collapse" data-canvas="body">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>

        </div>

        <?php if ( of_get_option('waboot_social_position', 'header-right') == 'header-right' ) : ?>
            <div id="header-right">
                <?php if ( of_get_option('waboot_social_position') === 'header-right' ) { include 'social-widget.php'; } ?>
                <?php dynamic_sidebar( 'header-right' ); ?>
            </div>
        <?php endif; ?>

        <div class="collapse navbar-collapse navbar-main-collapse navbar-right">
            <?php wp_nav_menu( array(
                    'theme_location' => 'main',
                    'depth'          => 0,
                    'container'      => false,
                    'menu_class'     => 'nav navbar-nav',
                    'walker'	     => new WabootNavMenuWalker(),
                    'fallback_cb' => 'waboot_nav_menu_fallback'
                )
            ); ?>

            <?php if ( of_get_option( 'waboot_search_bar', '1' ) ) : ?>
                <form id="searchform" class="navbar-form navbar-right" role="search" action="<?php echo site_url(); ?>" method="get">
                    <div class="form-group">
                        <input id="s" name="s" type="text" class="form-control" placeholder="<?php esc_attr_e( 'Search &hellip;', 'waboot' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
                    </div>
                    <button id="searchsubmit" type="submit" name="submit" class="btn btn-default">Submit</button>
                </form>
            <?php endif; ?>
        </div>

        <?php get_template_part('/templates/parts/mobile-nav'); ?>

    </nav>

    <!-- End Main menu -->
