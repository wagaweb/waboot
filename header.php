<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up to <div id="content">
 *
 * @package Waboot
 * @since Waboot 1.0
 */
?>
<!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <?php get_template_part( '/templates/parts/meta' ); ?>
        <title><?php wp_title( ' | ', true, 'right' ); ?></title>
        <?php
            wp_head();
            do_action( 'waboot_head' );
        ?>
    </head>

    <body <?php body_class(); ?> >
	    <!--[if lt IE 9]><p class="browsehappy alert alert-danger">You are using an outdated browser. Please <a class="alert-link" href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p><![endif]-->

        <?php if ( of_get_option( 'waboot_show_top_navbar', 1 ) ) : ?>
            <!-- Navbar: Begin -->
            <div id="topnav-wrapper" class="<?php echo of_get_option( 'waboot_topnav_width','container-fluid' ); ?> ">
                <?php get_template_part( '/templates/parts/menu', 'top' ); ?>
            </div>
            <!-- Navbar: End -->
        <?php endif; ?>

	    <div id="page" class="<?php echo of_get_option( 'waboot_page_width','container' ); ?> hfeed site">
		    <?php do_action( 'waboot_header_before' ); ?>
		    <div id="header-wrapper" class="<?php echo of_get_option( 'waboot_header_width' ); ?>">
                <header id="masthead" class="site-header" role="banner">

                <?php if ( of_get_option('waboot_header_layout') === 'header3' ) : ?>
                	<?php get_template_part( '/templates/parts/header3' ); ?>
                <?php elseif ( of_get_option('waboot_header_layout') === 'header2' ) : ?>
                    <?php get_template_part( '/templates/parts/header2' ); ?>
                <?php else : ?>
                	<?php get_template_part( '/templates/parts/header1' ); ?>
                <?php endif; ?>
                
                
                <?php
                    // Header image
                    // do_action( 'waboot_header_image' );
                    // Main menu
                    // if ( has_nav_menu('main') ) get_template_part( '/templates/parts/menu', 'main' );
                ?>
                
                
                
                </header><!-- #masthead -->
		    </div><!-- #header-wrapper -->
		    <?php do_action( 'waboot_header_after' ); ?>

	        <?php do_action( 'waboot_content_before' ); ?>

            <?php if ( is_active_sidebar( 'banner' ) ) : ?>
                <div id="banner-wrapper" class="<?php echo of_get_option( 'waboot_banner_width','container' ); ?>">
                    <div id="banner">
                        <?php dynamic_sidebar( 'banner' ); ?>
                    </div>
                </div>
            <?php endif; ?>
		
	        <div id="content-wrapper" class="<?php echo of_get_option( 'waboot_content_width','container' ); ?>">
                <div id="content" class="site-content row <?php if(get_behavior('layout') == "sidebar-left") echo 'sidebar-left'; ?>">

                <?php if ( function_exists( 'waboot_breadcrumb_trail' ) && !is_front_page() ) : ?>
                    <?php
                        waboot_breadcrumb_trail( array(
                            'container'   => 'div',
                            'separator'   => '/',
                            'show_browse' => false
                        ));
                    ?>
                <?php endif; ?>