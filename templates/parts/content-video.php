<?php
/**
 * @package Waboot
 * @since Waboot 1.0
 */
?>
<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php do_action( 'waboot_entry_header' ); ?>
	<div class="entry-content">
		<?php $first_video = et_get_first_video(); ?>
		<?php if ( $first_video ) : ?>
			<div class="wb-video-container">
				<?php echo $first_video; ?>
			</div>
		<?php endif; ?>


		<?php // the_content(); ?>
        <?php wp_link_pages(); ?>
	</div><!-- .entry-content -->
	<?php do_action( 'waboot_entry_footer' ); ?>
</article><!-- #post-<?php the_ID(); ?> -->