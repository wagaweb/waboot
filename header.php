<?php get_template_part("/templates/parts/head"); ?>
<?php do_action( 'waboot_head_after' ); ?>
<body <?php body_class(); ?> >
	<?php if(function_exists("Waboot")) Waboot()->layout->render_zone("header"); ?>
	<?php do_action("waboot/header"); ?>


