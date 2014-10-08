<?php
/**
 * The template for displaying Author Archive pages.
 *
 * @package Waboot
 * @since Waboot 1.0
 */

get_header(); ?>

	<section id="primary" class="<?php echo apply_filters( 'waboot_primary_container_class', 'content-area col-sm-8' ); ?>">

		<main id="main" class="site-main" role="main">

			<?php if ( have_posts() ) {

				/* Queue the first post, that way we know
				 * what author we're dealing with (if that is the case).
				 *
				 * We reset this later so we can run the loop
				 * properly with a call to rewind_posts().
				 */
				the_post();

				// Display the archive page title
				do_action( 'waboot_archive_page_title' );

				/* Since we called the_post() above, we need to
				 * rewind the loop back to the beginning that way
				 * we can run the loop properly, in full.
				 */
				rewind_posts();

				waboot_content_nav( 'nav-above' ); // display content nav above posts?

				// Start the Loop
				while ( have_posts() ) : the_post();

					/* Include the Post-Format-specific template for the content.
					 * If you want to override this in a child theme then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( '/templates/parts/content', get_post_format() );

				endwhile;

				// Show navigation below post content
				waboot_content_nav( 'nav-below' );

			} else {

				// No results
				get_template_part( '/templates/parts/content', 'none' );

				} //have_posts ?>
		</main><!-- #main -->

	</section><!-- #primary -->
<?php
get_sidebar();
get_footer(); ?>
