<main id="main" class="site-main" role="main">
	<?php if ( have_posts() ) : ?>
		<div>
			<?php while(have_posts()) :  the_post(); ?>
				<?php get_template_part( '/templates/parts/content', get_post_format() ); ?>
			<?php endwhile; ?>
		</div>
	<?php else: ?>
		<?php get_template_part('/templates/parts/content', 'none'); // No results ?>
	<?php endif; //have_posts ?>
</main><!-- #main -->