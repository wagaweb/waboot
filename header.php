<?php
	/*
	 * Prints out <head> section
	 */
	get_template_part("/templates/parts/head");
?>
<?php do_action( 'waboot_head_after' ); ?>
<body <?php body_class(); ?> >
    <?php if(function_exists("WabootLayout")) WabootLayout()->render_zone("page-before"); ?>
	<div id="site-page" class="site-page hfeed site">
		<div id="site-page__wrapper" class="site-page__wrapper <?php echo \Waboot\functions\get_option( 'page_width', WabootLayout()->get_grid_class('container') ); ?>">

		<!-- BEGIN: site-header -->
		<div class="site-header" data-zone="header">
            <?php if(function_exists("WabootLayout")) WabootLayout()->render_zone("header"); ?>
            <?php do_action("waboot/header"); ?>
		</div>
		<!-- END: site-header -->


