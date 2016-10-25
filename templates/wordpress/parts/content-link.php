<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<h2 class="entry-title">
			<a class="entry-title" title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" href="<?php echo Waboot\template_tags\get_filtered_link_post_content( 'link' ); ?>" target="_blank">
				<?php the_title(); ?> &rarr;
			</a>
		</h2>
	</header><!-- .entry-header -->
	<div class="entry-content">
		<?php echo Waboot\template_tags\get_filtered_link_post_content( 'post_content' ); ?>
		<?php wp_link_pages(); ?>
	</div><!-- .entry-content -->
	<?php do_action( 'waboot/entry/footer' ); ?>
</article><!-- #post-<?php the_ID(); ?> -->