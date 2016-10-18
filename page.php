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
	<?php get_template_part("templates/wrapper","start"); ?>

        <?php while ( have_posts() ) : the_post(); ?>
            <?php get_template_part( '/templates/parts/content', 'page' ); ?>
            <?php comments_template( '', true ); ?>
        <?php endwhile; ?>

	<?php get_template_part("templates/wrapper","end"); ?>
<?php
get_sidebar();
get_footer();