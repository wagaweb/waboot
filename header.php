<?php get_template_part("/templates/parts/head"); ?>
<?php do_action( 'waboot_head_after' ); ?>
<body <?php body_class(); ?> >
	<?php Waboot()->layout->render_zone("header"); ?>