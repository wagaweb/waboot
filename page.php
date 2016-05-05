<?php get_header(); ?>
	<div id="main-wrap">
		<?php Waboot()->layout->render_zone("aside-primary"); ?>
		<main>
			<?php if(have_posts()) : the_post(); ?>
				<?php get_template_part("templates/wordpress/page","content"); ?>
			<?php else : ?>
				<?php get_template_part("templates/parts/content","none"); ?>
			<?php endif; ?>
		</main>
		<?php Waboot()->layout->render_zone("aside-secondary"); ?>
	</div><!-- #main-wrap -->
<?php get_footer(); ?>