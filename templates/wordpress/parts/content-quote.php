<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry-content">
		<blockquote>
			<?php the_content( __( 'Continue Reading &raquo;', 'waboot' ) ); ?>
		</blockquote>
	</div>
	<?php do_action( 'waboot/entry/footer' ); ?>
</article><!-- #post -->