<?php
/**
 * The template for displaying posts in the Link post format
 *
 * @package Waboot
 * @since Waboot 1.0
 */

?>
<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<h2 class="entry-title">
			<a class="entry-title" title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" href="<?php echo waboot_link_format_helper( 'link' ); ?>">
				<?php the_title(); ?>&rarr;
			</a>
		</h2>
	</header><!-- .entry-header -->
	<div class="entry-content">
		<?php echo waboot_link_format_helper( 'post_content' ); // displays post content without the link. See inc/template-tags.php.
		wp_link_pages(); ?>
	</div><!-- .entry-content -->
	<?php do_action( 'waboot_entry_footer' ); ?>
</article><!-- #post-<?php the_ID(); ?> -->