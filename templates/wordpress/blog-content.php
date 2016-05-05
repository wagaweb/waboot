<?php
/*
 * The main blog template. It is hooked at the "main" zone in "zones_std_hooks.php"
 */
?>
<?php if(have_posts()): ?>
	<?php while(have_posts()) :  the_post(); ?>
		<?php get_template_part( '/templates/post-formats/content', get_post_format() ); ?>
	<?php endwhile; ?>
<?php else: ?>
	<?php get_template_part('/templates/parts/content', 'none'); // No results ?>
<?php endif; //have_posts ?>