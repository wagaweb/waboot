<?php
/**
 * The template for displaying all single posts.
 *
 * @package Waboot
 * @since Waboot 1.0
 */

get_header(); ?>
    <div id="primary" class="<?php echo apply_filters( 'waboot_primary_container_class', 'content-area col-sm-8' ); ?>">
        <main id="main" role="main" class="site-main">
            <?php while ( have_posts() ) : the_post();  ?>
                <?php waboot_content_nav( 'nav-above' ); // display content nav above posts? ?>
                <?php
                /* Include the Post-Format-specific template for the content.
                 * If you want to override this in a child theme then include a file
                 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
                 */
                get_template_part( '/templates/parts/content', $post_format);
                ?>
                <?php waboot_content_nav( 'nav-below' ); // display content nav below posts? ?>
                <?php // If comments are open or we have at least one comment, load up the comment template
                if ( comments_open() || '0' != get_comments_number() )
                    comments_template( '', true );
                ?>
            <?php endwhile; ?>
        </main><!-- #main -->
    </div><!-- #primary -->
<?php
get_sidebar();
get_footer(); ?>