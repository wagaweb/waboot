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
<?php if( get_behavior('title-position') == "top" ) : ?>
	<?php echo waboot_entry_title(); ?>
<?php endif; ?>
<?php if ( get_behavior( 'layout' ) == "full-width" ) : ?>
    <div id="primary" class="<?php echo apply_filters( 'waboot_primary_container_class', 'content-area col-sm-12' ); ?>">
<?php else : ?>
	<div id="primary" class="<?php echo apply_filters( 'waboot_primary_container_class', 'content-area col-sm-8' ); ?>">
<?php endif; ?>
        <main id="main" class="site-main" role="main">
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( '/templates/parts/content', 'page' ); ?>
                <?php comments_template( '', true ); ?>
            <?php endwhile; ?>
        </main><!-- #main -->
    </div><!-- #primary -->
<?php
get_sidebar();
get_footer();