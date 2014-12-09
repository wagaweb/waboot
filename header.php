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
	    <!--[if lt IE 8]><p class="browsehappy alert alert-danger">You are using an outdated browser. Please <a class="alert-link" href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p><![endif]-->

        <?php if ( is_active_sidebar( 'topbar' ) || (of_get_option('waboot_social_position') == 'topnav-right' || of_get_option('waboot_social_position') == 'topnav-left' ) || has_nav_menu( 'top' )) : ?>
            <!-- Navbar: Begin -->
            <div id="topnav-wrapper">
                <div id="topnav-inner" class="<?php echo of_get_option( 'waboot_topnav_width','container-fluid' ); ?> ">

                <?php if ( of_get_option('waboot_social_position') == 'topnav-left' ) : ?>
                    <div class="pull-left"> <?php get_template_part('/templates/parts/social-widget'); ?> </div><!-- .pull-left -->
                <?php endif; ?>
                <?php if ( of_get_option('waboot_social_position') == 'topnav-right' ) : ?>
                    <div class="pull-right"> <?php get_template_part('/templates/parts/social-widget'); ?> </div><!-- .pull-right -->
                <?php endif; ?>

                <?php if ( of_get_option('waboot_topnavmenu_position') == 'left' ) : ?>
                    <div class="pull-left">
                        <?php if(has_nav_menu( 'top' )) get_template_part( '/templates/parts/menu', 'top' ); ?>
                    </div>
                <?php endif; ?>
                <?php if ( of_get_option('waboot_topnavmenu_position') == 'right' ) : ?>
                    <div class="pull-right">
                        <?php if(has_nav_menu( 'top' )) get_template_part( '/templates/parts/menu', 'top' ); ?>
                    </div>
                <?php endif; ?>

                <?php dynamic_sidebar( 'topbar' ); ?>
                </div>
            </div>
            <!-- Navbar: End -->
        <?php endif; ?>

	    <div id="page" class="<?php echo of_get_option( 'waboot_page_width','container' ); ?> hfeed site">
            <div id="header-wrapper">
                <div id="header-inner" class="<?php echo of_get_option( 'waboot_header_width' ); ?>">
                    <header id="masthead" class="site-header" role="banner">
                    <?php
                    switch(of_get_option('waboot_header_layout', 'header1')){
                        case 'header3':
                            get_template_part( '/templates/parts/header3' );
                            break;
                        case 'header2':
                            get_template_part( '/templates/parts/header2' );
                            break;
                        default:
                            get_template_part( '/templates/parts/header1' );
                            break;
                    }
                    ?>

                    </header><!-- #masthead -->
                </div>
		    </div><!-- #header-wrapper -->

            <?php if ( is_active_sidebar( 'banner' ) || (WBF::component_is_loaded("slideshow") && SlideshowComponent::has_images()) ) : ?>
                <div id="banner-wrapper" class="<?php echo of_get_option( 'waboot_banner_width','container' ); ?>">
                    <div id="banner-inner">
	                    <?php
		                    if(WBF::component_is_loaded("slideshow")) {
			                    SlideshowComponent::display_slideshow();
		                    }
	                    ?>
                        <?php dynamic_sidebar( 'banner' ); ?>
                    </div>
                </div>
            <?php endif; ?>

            <div id="content-wrapper">
                <?php waboot_breadcrumb(null, 'before_inner', array('wrapper_start'=>'<div class="container">','wrapper_end'=>'</div>')); ?>
				<?php do_action("waboot_before_inner"); ?>
                <div id="content-inner" class="<?php echo of_get_option( 'waboot_content_width','container' ); ?>">
                    <div id="content" class="site-content row <?php if(get_behavior('layout') == "sidebar-left") echo 'sidebar-left'; ?>">