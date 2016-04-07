<?php get_header(); ?>
	<div id="main-wrap">
		<?php Waboot()->layout->render_zone("aside-primary"); ?>
		<main>
			Page
		</main>
		<?php Waboot()->layout->render_zone("aside-secondary"); ?>
	</div><!-- #main-wrap -->
<?php get_footer(); ?>