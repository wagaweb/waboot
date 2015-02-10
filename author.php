<?php
/**
 * The template for displaying Author Archive pages.
 *
 * @package Waboot
 * @since Waboot 1.0
 */

get_header(); ?>
	<section id="main-wrap" class="<?php echo apply_filters( 'waboot_mainwrap_container_class', 'content-area col-sm-8' ); ?>">
		<main id="main" class="site-main" role="main">
			<?php if (of_get_option('waboot_blogpage_title_position') == "bottom") : ?>
				<header class="page-header">
					<?php
					do_action( 'waboot_archive_page_title', "<h1 class=\"page-title\">", "</h1>" );
					$term_description = term_description();
					if ( ! empty( $term_description ) )
						printf( '<div class="taxonomy-description">%s</div>', $term_description );
					?>
				</header>
			<?php endif; ?>
			<?php if ( have_posts() ) {
				waboot_content_nav( 'nav-above' ); // display content nav above posts?
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
	</section><!-- #main-wrap -->
<?php
get_sidebar();
get_footer(); ?>
