<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package Waboot
 * @since Waboot 1.0
 */
do_action( 'waboot_post_before' ); ?>
<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	do_action( 'waboot_post_top' );
	do_action( 'waboot_entry_header' );
	do_action( 'waboot_entry_content_before' );
	?>
	<div class="entry-content">
		<?php
		the_content( __( 'Continue Reading &raquo;', 'waboot' ) );

		wp_link_pages();

		edit_post_link( __( ' Edit', 'waboot' ), '<span class="edit-link pull-right"><i class="glyphicon glyphicon-pencil"></i>', '</span>' );

		do_action( 'waboot_post_bottom' ); ?>
	</div><!-- .entry-content -->
	<?php do_action( 'waboot_entry_content_after' ); ?>

</article><!-- #post-<?php the_ID(); ?> -->
<?php do_action( 'waboot_post_after' ); ?>