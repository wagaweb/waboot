<?php get_header(); ?>
	<div id="main-wrap">
		<?php Waboot()->layout->render_zone("aside-primary"); ?>
		<main>
			<?php get_template_part("templates/wordpress/page","content"); ?>
		</main>
		<?php Waboot()->layout->render_zone("aside-secondary"); ?>
	</div><!-- #main-wrap -->
<?php get_footer(); ?>