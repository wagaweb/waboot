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
	<div id="main-wrap" class="<?php echo apply_filters( 'waboot_mainwrap_container_class', 'content-area col-sm-8' ); ?>">
        <main id="main" class="site-main" role="main">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( '/templates/parts/content', 'page' ); ?>
                <?php comments_template( '', true ); ?>
            <?php endwhile; ?>
        </main><!-- #main -->
    </div><!-- #main-wrap -->
<?php
get_sidebar();
get_footer();