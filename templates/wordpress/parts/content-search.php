<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php
	if(has_post_format('link')) : ?>
		<header class="entry-header">
			<h2 class="entry-title">
				<a class="entry-title" title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" href="<?php echo \Waboot\template_tags\get_first_link_or_post_content('link'); ?>">
					<?php the_title(); ?>&rarr;
				</a>
			</h2>
		</header><!-- .entry-header -->
	<?php else: ?>
		<?php do_action( 'waboot/entry/header' ); ?>
	<?php endif; ?>
	<div class="entry-content">
		<?php if ( has_post_thumbnail() && ! has_post_format( 'gallery' ) ) : ?>
			<a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>"><?php echo get_the_post_thumbnail( $post->ID, 'thumbnail', array( 'class' => 'alignleft', 'title' => "" ) ); ?></a>
		<?php endif; ?>
		<?php
		if( has_post_format( ['image', 'gallery', 'video', 'audio'] )  && !has_excerpt() ) {
			the_content( __( 'Continue Reading &raquo;', 'waboot' ) ); // Show full content on certain post formats if it doesn't have an excerpt.
		}else{
			the_excerpt(); // Show only excerpt on the rest.
		}
		?>
		<?php wp_link_pages(); ?>
	</div>
	<?php do_action( 'waboot/entry/footer' ); ?>
</article><!-- #post-<?php the_ID(); ?> -->