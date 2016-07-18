<?php
	/*
	 * Prints out <head> section
	 */
	get_template_part("/templates/wordpress/parts/head");
?>
<?php do_action( 'waboot_head_after' ); ?>
<body <?php body_class(); ?> >
	<div id="page" class="<?php echo of_get_option( 'page_width','container' ); ?> hfeed site">
	<!-- BEGIN: header -->
	<header id="masthead" class="site-header header-wrapper" role="banner" data-zone="header">
		<div class="header-inner">
			<?php if(function_exists("Waboot")) Waboot()->layout->render_zone("header"); ?>
			<?php do_action("waboot/header"); ?>
		</div>
	</header>
	<!-- END: header -->


