<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package Waboot
 * @since Waboot 1.0
 */

get_header(); ?>
	<?php get_template_part("templates/wrapper","start"); ?>

	<?php if ( have_posts() ) : ?>
		<header id="search-results-header" class="page-header">
			<h1 id="search-results-title" class="page-title"><?php printf( __( 'Search Results for: %s', 'waboot' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
		</header>
		<?php waboot_content_nav( 'nav-above' ); // display content nav above posts? ?>
        <?php
            // Start the Loop
            while ( have_posts() ) : the_post();
                get_template_part( '/templates/parts/content', 'search' );
            endwhile;
        ?>
		<?php waboot_content_nav( 'nav-below' ); // display content nav below posts? ?>
	<?php else : ?>
    <?php
		// No results
		get_template_part( '/templates/parts/content', 'none' );
    ?>
    <?php endif; ?>

	<?php get_template_part("templates/wrapper","end"); ?>
<?php
get_sidebar();
get_footer();