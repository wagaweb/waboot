<?php
/**
 * The template for displaying posts in the Status post format
 *
 * @package Waboot
 * @since Waboot 1.0
 */
do_action( 'waboot_post_before' ); ?>
<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	do_action( 'waboot_post_top' );
	do_action( 'waboot_entry_content_before' );
	?>
	<div class="entry-content">
		<?php the_content(); ?>
	</div>
	<?php
	do_action( 'waboot_entry_content_after' );
    do_action( 'waboot_entry_footer' );
	do_action( 'waboot_post_bottom' );
	?>
</article><!-- #post -->
<?php do_action( 'waboot_post_after' ); ?>