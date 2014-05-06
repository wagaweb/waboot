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
<!--[if lt IE 9]><script src="<?php echo get_template_directory_uri(); ?>/js/html5shiv.min.js" type="text/javascript"></script><![endif]-->

<?php
wp_head();
do_action( 'alienship_head' ); ?>

<style type="text/css">

body {
	background-color: <?php echo of_get_option( 'wship_body_bgcolor' ); ?> !important;
	background-image: url(<?php echo of_get_option( 'wship_body_bgimage' ); ?>);
  	background-repeat: <?php echo of_get_option( 'wship_body_bgrepeat' ); ?>;
  	background-position: <?php echo of_get_option( 'wship_body_bgpos' ); ?>;
  	background-attachment: <?php echo of_get_option( 'wship_body_bgattach' ); ?>;
}
#header-wrapper {
	background-color: <?php echo of_get_option( 'wship_header_bgcolor' ); ?>;
}
#banner-wrapper {
	background-color: <?php echo of_get_option( 'wship_banner_bgcolor' ); ?>;
}
#content-wrapper {
	background-color: <?php echo of_get_option( 'wship_content_bgcolor' ); ?>;
}
#contentbottom-wrapper {
	background-color: <?php echo of_get_option( 'wship_bottom_bgcolor' ); ?>;
}
#footer-wrapper {
	background-color: <?php echo of_get_option( 'wship_footer_bgcolor' ); ?>;
}
#logo {
	text-align: <?php echo of_get_option( 'wship_logo_align' ); ?>;
	float: <?php echo of_get_option( 'wship_logo_align', 'left,right' ); ?>;
	<?php if ( of_get_option( 'wship_float_navbar', 1 ) ) {?> display: inline-block; <?php } ?>
}
#page {
	background-color: <?php echo of_get_option( 'wship_page_bgcolor' ); ?>;
}
#header-wrapper .navbar-collapse {
	background-color: <?php echo of_get_option( 'wship_navbar_bgcolor' ); ?>;
}

</style>

</head>

<body <?php body_class(); ?> >

	<!--[if lt IE 9]><p class="browsehappy alert alert-danger">You are using an outdated browser. Please <a class="alert-link" href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p><![endif]-->

	<?php
	if ( of_get_option( 'wship_boxed_navbar', 1 ) ) { ?>
	<div class="container" style="padding:0;">
	<?php } ?>
	
	<?php
	if ( of_get_option( 'alienship_show_top_navbar', 1 ) )
		get_template_part( '/templates/parts/menu', 'top' );
	?>
		
	<?php
	if ( of_get_option( 'wship_boxed_navbar', 1 ) ) { ?>
	</div>
	<?php } ?>

	<div id="page" class="<?php echo of_get_option( 'wship_page_width' ); ?> hfeed site">

		<?php do_action( 'alienship_header_before' ); ?>
		<div id="header-wrapper" class="<?php echo of_get_option( 'wship_header_width' ); ?>">
		<header id="masthead" class="site-header" role="banner">
		
			<?php
			// Header image
			do_action( 'alienship_header_image' );

			// Main menu
			if ( has_nav_menu('main') ) {
				get_template_part( '/templates/parts/menu', 'main' );
			} ?>
		
		</header><!-- #masthead -->
		</div><!-- #header-wrapper -->
		<?php do_action( 'alienship_header_after' );

	do_action( 'alienship_content_before' ); ?>
	
	
	<?php if ( is_active_sidebar( 'banner' ) ) : ?>
		<div id="banner-wrapper" class="<?php echo of_get_option( 'wship_banner_width' ); ?>">
		<div id="banner">
			<?php dynamic_sidebar( 'banner' ); ?>
		</div>
		</div>
	<?php endif; ?>
		
	<div id="content-wrapper" class="<?php echo of_get_option( 'wship_content_width' ); ?>">
	<div id="content" class="site-content row <?php if(get_behavior('layout') == "sidebar-left") echo 'sidebar-left'; ?>">

	<?php if ( function_exists( 'breadcrumb_trail' ) && !is_front_page() )
		breadcrumb_trail( array(
			'container'   => 'div',
			'separator'   => '/',
			'show_browse' => false
			)
		);
