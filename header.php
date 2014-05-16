<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up to <div id="content">
 *
 * @package Alien Ship
 * @since Alien Ship 0.1
 */
?>
<!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <?php get_template_part( '/templates/parts/meta' ); ?>
        <title><?php wp_title( '&#8226;', true, 'right' ); ?></title>
        <!--[if lt IE 9]><script src="<?php echo get_template_directory_uri(); ?>/assets/js/html5shiv.min.js" type="text/javascript"></script><![endif]-->
        <?php
            wp_head();
            do_action( 'waboot_head' );
        ?>
    </head>

    <body <?php body_class(); ?> >
	    <!--[if lt IE 9]><p class="browsehappy alert alert-danger">You are using an outdated browser. Please <a class="alert-link" href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p><![endif]-->

        <?php if ( of_get_option( 'alienship_show_top_navbar', 1 ) ) : ?>
            <!-- Navbar: Begin -->
            <?php if ( of_get_option( 'wship_boxed_navbar', 1 ) ) : ?>
                <div class="container" style="padding:0;">
                    <?php get_template_part( '/templates/parts/menu', 'top' ); ?>
                </div>
            <?php else : ?>
                    <?php get_template_part( '/templates/parts/menu', 'top' ); ?>
            <?php endif; ?>
            <!-- Navbar: End -->
        <?php endif; ?>

	    <div id="page" class="<?php echo of_get_option( 'wship_page_width' ); ?> hfeed site">
		    <?php do_action( 'waboot_header_before' ); ?>
		    <div id="header-wrapper" class="<?php echo of_get_option( 'wship_header_width' ); ?>">
                <header id="masthead" class="site-header" role="banner">
                <?php
                    // Header image
                    do_action( 'waboot_header_image' );
                    // Main menu
                    if ( has_nav_menu('main') ) get_template_part( '/templates/parts/menu', 'main' );
                ?>
                </header><!-- #masthead -->
		    </div><!-- #header-wrapper -->
		    <?php do_action( 'waboot_header_after' ); ?>

	        <?php do_action( 'waboot_content_before' ); ?>

            <?php if ( is_active_sidebar( 'banner' ) ) : ?>
                <div id="banner-wrapper" class="<?php echo of_get_option( 'wship_banner_width' ); ?>">
                    <div id="banner">
                        <?php dynamic_sidebar( 'banner' ); ?>
                    </div>
                </div>
            <?php endif; ?>
		
	        <div id="content-wrapper" class="<?php echo of_get_option( 'wship_content_width' ); ?>">
                <div id="content" class="site-content row <?php if(get_behavior('layout') == "sidebar-left") echo 'sidebar-left'; ?>">

                <?php if ( function_exists( 'breadcrumb_trail' ) && !is_front_page() ) : ?>
                    <?php
                        breadcrumb_trail( array(
                            'container'   => 'div',
                            'separator'   => '/',
                            'show_browse' => false
                        ));
                    ?>
                <?php endif; ?>