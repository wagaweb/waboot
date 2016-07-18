<?php \Waboot\template_tags\post_navigation('nav-above'); ?>
<?php get_template_part('templates/post-formats/content', get_post_format()); ?>
<?php \Waboot\template_tags\post_navigation('nav-below'); ?>
<?php
if(comments_open() || '0' != get_comments_number()){
	comments_template('/comments.php',true);
}
