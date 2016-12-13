<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-content">
		<?php the_content(); ?>
	</div>
    <?php do_action( 'waboot/entry/footer' ); ?>
</article><!-- #post -->