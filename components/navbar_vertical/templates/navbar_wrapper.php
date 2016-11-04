<div id="navbar-wrapper" class="<?php echo $navbar_class; ?>">
	<div id="navbar-inner">
		<nav class="navbar navbar-default main-navigation" role="navigation">
			<?php echo $content; ?>
			<?php if ( of_get_option('waboot_mobilenav_style') === 'offcanvas' ) { get_template_part('/templates/parts/nav-offcanvas'); } ?>
		</nav>
	</div>
</div><!-- #navbar-wrapper -->