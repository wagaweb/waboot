<?php
	/*
	 * Prints out <head> section
	 */
	get_template_part("/templates/wordpress/parts/head");
?>
<?php do_action( 'waboot_head_after' ); ?>
<body <?php body_class(); ?> >
    <?php if(function_exists("Waboot")) Waboot()->layout->render_zone("page-before"); ?>
	<div id="page-wrapper" class="page-wrapper hfeed site">
		<div id="page-inner" class="page-inner <?php echo of_get_option( 'page_width','container' ); ?>">
		<!-- BEGIN: header -->
		<header id="masthead" class="site-header header-wrapper" role="banner" data-zone="header">
			<div class="header-inner">
				<?php if(function_exists("Waboot")) Waboot()->layout->render_zone("header"); ?>
				<?php do_action("waboot/header"); ?>
			</div>
		</header>
		<!-- END: header -->


