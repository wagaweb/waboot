<?php
/**
 * The template is for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package Waboot
 * @since Waboot 1.0
 */

get_header(); ?>

    <?php //if ( current_theme_supports( 'theme-layouts' ) && 'layout-1c' == theme_layouts_get_layout()) : ?>
    <?php if ( get_behavior( 'layout' ) == "full-width" ) : ?>
    <div id="primary" class="<?php echo apply_filters( 'waboot_primary_container_class', 'content-area col-sm-12' ); ?>">
    <?php else : ?>
	<div id="primary" class="<?php echo apply_filters( 'waboot_primary_container_class', 'content-area col-sm-8' ); ?>">
    <?php endif; ?>

		<main id="main" class="site-main" role="main">

			<?php
			while ( have_posts() ) : the_post();

				get_template_part( '/templates/parts/content', 'page' );

				comments_template( '', true );

			endwhile;
			?>

		</main><!-- #main -->

	</div><!-- #primary -->
<?php
get_sidebar();
get_footer(); ?>