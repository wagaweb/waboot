<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php do_action("waboot/entry/header"); ?>
	<div class="entry-content">
		<?php the_content( __( 'Continue Reading &raquo;', 'waboot' ) ); ?>
		<?php wp_link_pages(); ?>
		<?php edit_post_link( __(' Edit', 'waboot'), '<span class="edit-link pull-right"><i class="glyphicon glyphicon-pencil"></i>', '</span>'); ?>
	</div><!-- .entry-content -->
</article><!-- #post-<?php the_ID(); ?> -->
<?php 
if(comments_open() || '0' != get_comments_number()){
	comments_template('/comments.php',true);
}