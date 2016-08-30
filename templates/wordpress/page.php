<?php while ( have_posts() ) : the_post(); ?>
	<?php get_template_part('templates/wordpress/parts/content','page') ?>
	<?php
	if(comments_open() || '0' != get_comments_number()){
		comments_template('/comments.php',true);
	}
	?>
<?php endwhile; ?>
