<div id="navbar-wrapper" class="nav-<?php echo of_get_option( 'waboot_header_layout' ); ?>">
	<div id="navbar-inner" class="<?php echo of_get_option( 'waboot_navbar_width' ); ?>">
		<nav class="navbar navbar-default main-navigation" role="navigation">
			<?php get_template_part('/templates/parts/nav-main'); ?>
			<?php if ( of_get_option('waboot_mobilenav_style') === 'offcanvas' ) { get_template_part('/templates/parts/nav-offcanvas'); } ?>
		</nav>
	</div>
</div><!-- #navbar-wrapper -->