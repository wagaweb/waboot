<?php
	/*
	 * Prints out <head> section
	 */
	get_template_part("/templates/parts/head");
?>
<?php do_action( 'waboot_head_after' ); ?>
<body <?php body_class(); ?> >
	<!-- BEGIN: header -->
	<header id="masthead" class="site-header" role="banner" data-zone="header">
		<?php if(function_exists("Waboot")) Waboot()->layout->render_zone("header"); ?>
		<?php do_action("waboot/header"); ?>
	</header>
	<!-- END: header -->


