<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php do_action( 'waboot/entry/header' ); ?>
	<div class="entry-content">
		<?php echo do_shortcode( '[audio]' ); ?>
		<?php wp_link_pages(); ?>
	</div><!-- .entry-content -->
	<?php do_action( 'waboot/entry/footer' ); ?>
</article><!-- #post-<?php the_ID(); ?> -->