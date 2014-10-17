<?php
/**
 * The main template file.
 *
 * @package Waboot
 */

get_header(); ?>
<?php if ( get_behavior( 'layout' ) == "full-width" ) : ?>
    <div id="primary" class="<?php echo apply_filters( 'waboot_primary_container_class', 'content-area col-sm-12' ); ?>">
<?php else : ?>
    <div id="primary" class="<?php echo apply_filters( 'waboot_primary_container_class', 'content-area col-sm-8' ); ?>">
<?php endif; ?>
    <h1 class="entry-title"><?php single_post_title(); ?></h1>
        <main id="main" class="site-main" role="main">
            <?php if ( have_posts() ) : ?>
                <?php waboot_content_nav( 'nav-above' ); // display content nav above posts ?>
                <?php
                while ( have_posts() ) {
                    the_post();
                    /* Include the Post-Format-specific template for the content.
                     * If you want to override this in a child theme then include a file
                     * called content-___.php (where ___ is the Post Format name) and that will be used instead.
                     */
                    get_template_part( '/templates/parts/content', get_post_format() );
                }
                ?>
                <?php waboot_content_nav( 'nav-below' ); // display content nav below posts? ?>
            <?php else: ?>
                <?php
                // No results
                get_template_part( '/templates/parts/content', 'none' );
                ?>
            <?php endif; //have_posts ?>
        </main><!-- #main -->
    </div><!-- #primary -->
<?php
get_sidebar();
get_footer();