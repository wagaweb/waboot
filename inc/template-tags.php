<?php

if ( ! function_exists( 'waboot_content_nav' ) ):
    /**
     * Display navigation to next/previous pages when applicable
     *
     * @since 1.0
     * @from Alienship
     */
    function waboot_content_nav( $nav_id ) {

        // Return early if theme options are set to hide nav
        if ( 'nav-below' == $nav_id && ! of_get_option( 'alienship_content_nav_below', 1 )
            || 'nav-above' == $nav_id && ! of_get_option( 'alienship_content_nav_above' ) )
            return;

        global $wp_query;

        $nav_class = 'site-navigation paging-navigation pager';

        if ( is_single() )
            $nav_class = 'site-navigation post-navigation pager';
        ?>

        <nav id="<?php echo $nav_id; ?>" class="<?php echo $nav_class; ?>">
            <h3 class="screen-reader-text"><?php _e( 'Post navigation', 'alienship' ); ?></h3>
            <ul class="pager">
                <?php
                if ( is_single() ) : // navigation links for single posts

                    previous_post_link( '<li class="previous">%link</li>', '<span class="meta-nav">' . _x( '&laquo;', 'Previous post link', 'alienship' ) . '</span> %title' );
                    next_post_link( '<li class="next">%link</li>', '%title <span class="meta-nav">' . _x( '&raquo;', 'Next post link', 'alienship' ) . '</span>' );

                elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages

                    if ( get_next_posts_link() ) : ?>
                        <li class="pull-right"><?php next_posts_link( __( 'Next page <span class="meta-nav">&raquo;</span>', 'alienship' ) ); ?></li>
                    <?php endif;

                    if ( get_previous_posts_link() ) : ?>
                        <li class="pull-left"><?php previous_posts_link( __( '<span class="meta-nav">&laquo;</span> Previous page', 'alienship' ) ); ?></li>
                    <?php endif;

                endif; ?>
            </ul>
        </nav><!-- #<?php echo $nav_id; ?> -->
    <?php
    }
endif; // waboot_content_nav

if ( ! function_exists( 'waboot_featured_posts_grid' ) ):
    /**
     * Display featured posts in a grid
     * @since 1.0
     * @from Alienship
     */
    function waboot_featured_posts_grid() {

        $args = array(
            'tag_id'         => of_get_option( 'alienship_featured_posts_tag' ),
            'posts_per_page' => of_get_option( 'alienship_featured_posts_maxnum' ),
        );
        $featured_query = new WP_Query( $args );

        if ( $featured_query->have_posts() ) { ?>
            <ul id="featured-posts-grid" class="block-grid mobile two-up">

                <?php while ( $featured_query->have_posts() ) : $featured_query->the_post();
                    get_template_part( '/templates/parts/content', 'fp-grid' );
                endwhile; ?>

            </ul>
        <?php }
    }
endif; //waboot_featured_posts_grid

if ( ! function_exists( 'waboot_featured_posts_slider' ) ):
    /**
     * Display featured posts in a slider
     * @since 1.0
     * @from Alienship
     */
    function waboot_featured_posts_slider() {

        $args = array(
            'tag_id'         => of_get_option( 'alienship_featured_posts_tag' ),
            'posts_per_page' => of_get_option( 'alienship_featured_posts_maxnum' ),
        );
        $featured_query = new WP_Query( $args );

        if ( $featured_query->have_posts() ) { ?>
            <div class="row">
                <div class="col-sm-12">
                    <div id="featured-carousel" class="carousel slide">

                        <?php // Featured post indicators?
                        if ( of_get_option( 'alienship_featured_posts_indicators', 0 ) ) { ?>
                            <ol class="carousel-indicators">

                                <?php
                                $indicators = $featured_query->post_count;
                                $count = 0;
                                while ( $count != $indicators ) {
                                    echo '<li data-target="#featured-carousel" data-slide-to="' . $count . '"></li>';
                                    $count++;
                                }
                                ?>

                            </ol>
                        <?php } // alienship_featured_posts_indicators ?>

                        <div class="carousel-inner">

                            <?php while ( $featured_query->have_posts() ) : $featured_query->the_post();
                                get_template_part( '/templates/parts/content', 'featured' );
                            endwhile; ?>

                        </div><!-- .carousel-inner -->
                        <a class="left carousel-control" href="#featured-carousel" data-slide="prev"><span class="icon-prev"></span></a>
                        <a class="right carousel-control" href="#featured-carousel" data-slide="next"><span class="icon-next"></span></a>
                    </div><!-- #featured-carousel -->
                </div><!-- .col-sm-12 -->
            </div><!-- .row -->

            <script type="text/javascript">
                jQuery(function() {
                    // Activate the first carousel item //
                    jQuery("div.item:first").addClass("active");
                    jQuery("ol.carousel-indicators").children("li:first").addClass("active");
                    // Start the Carousel //
                    jQuery('.carousel').carousel();
                });
            </script>
        <?php } // if(have_posts()) ?>
        <!-- End featured listings -->
    <?php }
endif; //featured_post_slider

