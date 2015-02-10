<?php
/**
 * The main template file.
 *
 * @package Waboot
 */

get_header();
$blog_style = waboot_get_blog_layout();
?>
    <div id="main-wrap" class="<?php echo apply_filters( 'waboot_mainwrap_container_class', 'content-area col-sm-8' ); ?>">
        <main id="main" class="site-main" role="main">
            <?php if (of_get_option('waboot_blogpage_title_position') == "bottom") : ?>
                <?php waboot_index_title('<h1 class=\'entry-header\'>', '</h1>'); ?>
            <?php endif; ?>
            <?php if ( have_posts() ) : ?>
                <?php waboot_content_nav( 'nav-above' ); // display content nav above posts ?>
                <div class="blog-<?php echo $blog_style; ?>">
                    <?php
                    while ( have_posts() ) {
                        the_post();
                        /* Include the Post-Format-specific template for the content.
                         * If you want to override this in a child theme then include a file
                         * called content-___.php (where ___ is the Post Format name) and that will be used instead.
                         */
                        if($blog_style == "masonry"){
	                        get_template_part( '/templates/parts/content', "blog-masonry" );
                        }
                        elseif($blog_style == "timeline"){
                            get_template_part( '/templates/parts/content', "blog-timeline" );
                        }else{
	                        get_template_part( '/templates/parts/content', get_post_format() );
                        }
                    }
                    ?>
                </div>
                <?php waboot_content_nav( 'nav-below' ); // display content nav below posts? ?>
            <?php else: ?>
                <?php get_template_part('/templates/parts/content', 'none'); // No results ?>
            <?php endif; //have_posts ?>
        </main><!-- #main -->
    </div><!-- #main-wrap -->
<?php
get_sidebar();
get_footer();