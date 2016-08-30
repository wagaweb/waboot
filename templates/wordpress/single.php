<?php \Waboot\template_tags\post_navigation('nav-above'); ?>
<?php while ( have_posts() ) : the_post(); ?>
	<?php get_template_part('templates/wordpress/parts/content', 'single'); ?>
	<?php
	if(comments_open() || '0' != get_comments_number()){
		comments_template('/comments.php',true);
	}
	?>
<?php endwhile; ?>
<?php \Waboot\template_tags\post_navigation('nav-below'); ?>
