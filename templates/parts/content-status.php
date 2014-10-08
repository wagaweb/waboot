<?php
/**
 * The template for displaying posts in the Status post format
 *
 * @package Waboot
 * @since Waboot 1.0
 */
?>
<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-content">
		<?php the_content(); ?>
	</div>
	<?php do_action( 'waboot_entry_footer' ); ?>
</article><!-- #post -->