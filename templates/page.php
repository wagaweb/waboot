<?php while ( have_posts() ) : the_post(); ?>
	<?php
		$required_tpl = get_post_meta( get_the_ID(), '_wp_page_template', true ); //Gets page template (Waboot automatically inject templates into Wordpress by \Waboot\hooks\inject_templates())
		if(preg_match("/.php/",$required_tpl)) $required_tpl = "page"; //this is not a Waboot-injected template, so fallback to page.
	?>
	<?php if(locate_template("templates/parts-tpl/content-".$required_tpl.".php", false, false) != '') : ?>
		<?php get_template_part('templates/parts-tpl/content',$required_tpl); ?>
	<?php else: ?>
		<?php get_template_part('templates/parts/content','page'); ?>
	<?php endif; ?>
	<?php
	if(comments_open() || '0' != get_comments_number()){
		comments_template('/templates/comments.php',true);
	}
	?>
<?php endwhile; ?>
