<main id="main" class="site-main" role="main">
	<?php if ( have_posts() ) : ?>
		<?php waboot_content_nav( 'nav-above' ); // display content nav above posts ?>
		<div class="<?php echo $blog_class; ?>">
			<?php while(have_posts()) :  the_post(); ?>
				<?php get_template_part( '/templates/parts/content', get_post_format() ); ?>
			<?php endwhile; ?>
		</div>
		<?php waboot_content_nav( 'nav-below' ); // display content nav below posts? ?>
	<?php else: ?>
		<?php get_template_part('/templates/parts/content', 'none'); // No results ?>
	<?php endif; //have_posts ?>
</main><!-- #main -->