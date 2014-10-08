<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package Waboot
 * @since Waboot 1.0
 */
?>
<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php do_action( 'waboot_entry_header' ); ?>
	<div class="entry-content">
		<?php the_content( __( 'Continue Reading &raquo;', 'waboot' ) ); ?>
        <?php wp_link_pages(); ?>
        <?php edit_post_link( __( ' Edit', 'waboot' ), '<span class="edit-link pull-right"><i class="glyphicon glyphicon-pencil"></i>', '</span>' ); ?>
	</div><!-- .entry-content -->
</article><!-- #post-<?php the_ID(); ?> -->