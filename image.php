<?php
/**
 * The template for displaying image attachments.
 *
 * @package Waboot
 * @since Waboot 1.0
 */

get_header(); ?>

	<div id="primary" class="image-attachment">

		<main id="main" class="site-main" role="main">

			<?php while ( have_posts() ) : the_post(); ?>

				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>

						<div class="entry-meta">
							<?php
							$metadata = wp_get_attachment_metadata();
							printf( __( 'Published <span class="entry-date"><abbr class="published" title="%1$s">%2$s</abbr></span> at <a href="%3$s" title="Link to full-size image">%4$s &times; %5$s</a> in <a href="%6$s" title="Return to %7$s" rel="gallery">%8$s</a>', 'waboot' ),
								esc_attr( get_the_time() ),
								get_the_date(),
								wp_get_attachment_url(),
								$metadata['width'],
								$metadata['height'],
								esc_url( get_permalink( $post->post_parent ) ),
								esc_attr( strip_tags( get_the_title( $post->post_parent ) ) ),
								get_the_title( $post->post_parent )
							);
							edit_post_link( __( 'Edit', 'waboot' ), '<span class="sep">|</span> <span class="edit-link">', '</span>' ); ?>
						</div><!-- .entry-meta -->

						<nav role="navigation" id="image-navigation" class="image-navigation">
							<ul class="pager">
								<li><?php previous_image_link( false, __( '&laquo; Previous', 'waboot' ) ); ?></li>
								<li><?php next_image_link( false, __( 'Next &raquo;', 'waboot' ) ); ?></li>
							</ul>
						</nav>
					</header>

					<?php do_action( 'waboot_entry_content_before' ); ?>
					<div class="entry-content">

						<div class="entry-attachment">
							<div class="attachment">
								<?php waboot_the_attached_image(); ?>
							</div><!-- .attachment -->

							<?php if ( has_excerpt() ) : ?>
								<div class="entry-caption">
									<?php the_excerpt(); ?>
								</div>
							<?php endif; ?>
						</div><!-- .entry-attachment -->

						<?php
						the_content();
						wp_link_pages();
						?>
					</div><!-- .entry-content -->
					<?php do_action( 'waboot_entry_content_after' ); ?>

					<footer class="entry-meta">
						<?php
						if ( comments_open() && pings_open() ) : // Comments and trackbacks open
							printf( __( '<a class="comment-link" href="#respond" title="Post a comment">Post a comment</a> or leave a trackback: <a class="trackback-link" href="%s" title="Trackback URL for your post" rel="trackback">Trackback URL</a>.', 'waboot' ), get_trackback_url() );

						elseif ( ! comments_open() && pings_open() ) : // Only trackbacks open
							printf( __( 'Comments are closed, but you can leave a trackback: <a class="trackback-link" href="%s" title="Trackback URL for your post" rel="trackback">Trackback URL</a>.', 'waboot' ), get_trackback_url() );

						elseif ( comments_open() && ! pings_open() ) : // Only comments open
							_e( 'Trackbacks are closed, but you can <a class="comment-link" href="#respond" title="Post a comment">post a comment</a>.', 'waboot' );

						elseif ( ! comments_open() && ! pings_open() ) : // Comments and trackbacks closed
							_e( 'Both comments and trackbacks are currently closed.', 'waboot' );

						endif;

						edit_post_link( __( 'Edit', 'waboot' ), ' <span class="edit-link">', '</span>' ); ?>
					</footer><!-- .entry-meta -->
				</article><!-- #post-<?php the_ID(); ?> -->

				<?php comments_template(); ?>

			<?php endwhile; // end of the loop. ?>

		</main><!-- #main -->

	</div><!-- #primary -->

<?php get_footer(); ?>