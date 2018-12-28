<?php while ( have_posts() ) : the_post(); ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<header class="entry__header">
			<?php the_title( '<h1 class="entry__title">', '</h1>' ); ?>

			<div class="entry__meta">
				<?php
				$metadata = wp_get_attachment_metadata();
				printf( __( 'Published <span class="entry__date"><abbr class="published" title="%1$s">%2$s</abbr></span> at <a href="%3$s" title="Link to full-size image">%4$s &times; %5$s</a> in <a href="%6$s" title="Return to %7$s" rel="gallery">%8$s</a>', 'waboot' ),
					esc_attr( get_the_time() ),
					get_the_date(),
					wp_get_attachment_url(),
					$metadata['width'],
					$metadata['height'],
					esc_url( get_permalink( $post->post_parent ) ),
					esc_attr( strip_tags( get_the_title( $post->post_parent ) ) ),
					get_the_title( $post->post_parent )
				);
				?>
			</div><!-- .entry-meta -->

			<nav role="navigation" id="image__navigation" class="image__navigation">
				<ul class="pager">
					<li class="prev__link"><?php previous_image_link( false, __( '&laquo; Previous', 'waboot' ) ); ?></li>
					<li class="next__link"><?php next_image_link( false, __( 'Next &raquo;', 'waboot' ) ); ?></li>
				</ul>
			</nav>
		</header>
		<div class="entry__content">

			<div class="entry__attachment">
				<div class="attachment">
					<?php \Waboot\template_tags\the_attached_image(); ?>
				</div><!-- .attachment -->

				<?php if ( has_excerpt() ) : ?>
					<div class="entry__caption">
                        <p><?php \Waboot\template_tags\the_trimmed_excerpt(20, '...'); ?> <a class="more__link" href="<?php the_permalink() ?>"><?php _e('Continue reading', 'waboot') ?></a></p>
					</div>
				<?php endif; ?>
			</div><!-- .entry-attachment -->

			<?php
			the_content();
			wp_link_pages();
			?>
		</div><!-- .entry-content -->

		<footer class="entry__footer">
			<?php
			if ( comments_open() && pings_open() ) : // Comments and trackbacks open
				printf( __( '<a class="comment__link" href="#respond" title="Post a comment">Post a comment</a> or leave a trackback: <a class="trackback__link" href="%s" title="Trackback URL for your post" rel="trackback">Trackback URL</a>.', 'waboot' ), get_trackback_url() );
			elseif ( ! comments_open() && pings_open() ) : // Only trackbacks open
				printf( __( 'Comments are closed, but you can leave a trackback: <a class="trackback__link" href="%s" title="Trackback URL for your post" rel="trackback">Trackback URL</a>.', 'waboot' ), get_trackback_url() );
			elseif ( comments_open() && ! pings_open() ) : // Only comments open
				_e( 'Trackbacks are closed, but you can <a class="comment__link" href="#respond" title="Post a comment">post a comment</a>.', 'waboot' );
			elseif ( ! comments_open() && ! pings_open() ) : // Comments and trackbacks closed
				_e( 'Both comments and trackbacks are currently closed.', 'waboot' );
			endif;
			?>
		</footer><!-- .entry-meta -->
	</article>

	<?php comments_template('/templates/comments.php'); ?>

<?php endwhile; // end of the loop. ?>