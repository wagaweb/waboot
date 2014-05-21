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
        if ( 'nav-below' == $nav_id && ! of_get_option( 'waboot_content_nav_below', 1 )
            || 'nav-above' == $nav_id && ! of_get_option( 'waboot_content_nav_above' ) )
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
            'tag_id'         => of_get_option( 'waboot_featured_posts_tag' ),
            'posts_per_page' => of_get_option( 'waboot_featured_posts_maxnum' ),
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
            'tag_id'         => of_get_option( 'waboot_featured_posts_tag' ),
            'posts_per_page' => of_get_option( 'waboot_featured_posts_maxnum' ),
        );
        $featured_query = new WP_Query( $args );

        if ( $featured_query->have_posts() ) { ?>
            <div class="row">
                <div class="col-sm-12">
                    <div id="featured-carousel" class="carousel slide">

                        <?php // Featured post indicators?
                        if ( of_get_option( 'waboot_featured_posts_indicators', 0 ) ) { ?>
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

if ( ! function_exists( 'waboot_archive_sticky_posts' ) ):
    /**
     * Display sticky posts on archive pages
     * @since 1.0
     * @from Alienship
     */
    function waboot_archive_sticky_posts() {

        $sticky = get_option( 'sticky_posts' );
        if ( ! empty( $sticky ) ) {
            global $do_not_duplicate, $page, $paged;
            $do_not_duplicate = array();

            if ( is_category() ) {
                $cat_ID = get_query_var( 'cat' );
                $sticky_args = array(
                    'post__in'    => $sticky,
                    'cat'         => $cat_ID,
                    'post_status' => 'publish',
                    'paged'       => $paged
                );

            } elseif ( is_tag() ) {
                $current_tag = get_queried_object_id();
                $sticky_args = array(
                    'post__in'     => $sticky,
                    'tag_id'       => $current_tag,
                    'post_status'  => 'publish',
                    'paged'        => $paged
                );
            }

            if ( ! empty( $sticky_args ) ):
                $sticky_posts = new WP_Query( $sticky_args );

                if ( $sticky_posts->have_posts() ):
                    global $post;

                    while ( $sticky_posts->have_posts() ) : $sticky_posts->the_post();
                        array_push( $do_not_duplicate, $post->ID );
                        get_template_part( '/templates/parts/content', get_post_format() );
                    endwhile;
                endif; // if have posts
            endif; // if ( ! empty( $sticky_args ) )
        } //if not empty sticky
    }
endif; //waboot_archive_sticky_posts

if ( ! function_exists( 'waboot_comment' ) ) :
    /**
     * Template for comments and pingbacks.
     *
     * Used as a callback by wp_list_comments() for displaying the comments.
     */
    function waboot_comment( $comment, $args, $depth ) {
        $GLOBALS['comment'] = $comment;

        if ( 'pingback' == $comment->comment_type || 'trackback' == $comment->comment_type ) : ?>

            <li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
            <div class="comment-body">
                <?php _e( 'Pingback:', 'alienship' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( 'Edit', 'alienship' ), '<span class="edit-link">', '</span>' ); ?>
            </div>

        <?php else : ?>

        <li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?>>
            <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
                <footer class="comment-meta">
                    <div class="comment-author vcard">
                        <?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
                        <?php printf( __( '%s <span class="says">says:</span>', 'alienship' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
                    </div><!-- .comment-author -->

                    <div class="comment-metadata">
                        <a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
                            <time datetime="<?php comment_time( 'c' ); ?>">
                                <?php printf( _x( '%1$s at %2$s', '1: date, 2: time', 'alienship' ), get_comment_date(), get_comment_time() ); ?>
                            </time>
                        </a>
                        <?php edit_comment_link( __( 'Edit', 'alienship' ), '<span class="edit-link">', '</span>' ); ?>
                    </div><!-- .comment-metadata -->

                    <?php if ( '0' == $comment->comment_approved ) : ?>
                        <p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'alienship' ); ?></p>
                    <?php endif; ?>
                </footer><!-- .comment-meta -->

                <div class="comment-content">
                    <?php comment_text(); ?>
                </div><!-- .comment-content -->

                <?php
                comment_reply_link( array_merge( $args, array(
                    'add_below' => 'div-comment',
                    'depth'     => $depth,
                    'max_depth' => $args['max_depth'],
                    'before'    => '<div class="reply">',
                    'after'     => '</div>',
                ) ) );
                ?>
            </article><!-- .comment-body -->

        <?php
        endif;
    }
endif; // ends check for waboot_comment()

