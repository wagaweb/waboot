<?php \Waboot\template_tags\post_navigation('nav-above'); ?>
<?php while ( have_posts() ) : the_post(); $pt = get_post_type(); ?>
	<?php if(locate_template("templates/parts/content-".$pt.".php", false, false) != '') : ?>
		<?php get_template_part('templates/parts/content', $pt); ?>
	<?php else: ?>
		<?php get_template_part('templates/parts/content', 'single'); ?>
	<?php endif; ?>
	<?php
	if(comments_open() || '0' != get_comments_number()){
		comments_template('/templates/comments.php',true);
	}
	?>
<?php endwhile; ?>
<?php \Waboot\template_tags\post_navigation('nav-below'); ?>
