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

/**
 * Determines the theme layout and active sidebars, and prints the HTML structure
 * with appropriate grid classes depending on which are activated.
 *
 * @since 1.0
 * @uses alienship_sidebar_class()
 * @param string $prefix Prefix of the widget to be displayed. Example: "footer" for footer-1, footer-2, etc.
 */
function waboot_do_sidebar( $prefix = false ) {

    if ( ! $prefix )
        _doing_it_wrong( __FUNCTION__, __( 'You must specify a prefix when using alienship_do_sidebar.', 'alienship' ), '1.0' );

        // Get our grid class
        $sidebar_class = waboot_sidebar_class( $prefix );

        if ( $sidebar_class ): ?>

            <div class="<?php echo $prefix; ?>-sidebar-row row">
                <?php do_action( 'waboot_sidebar_row_top' );

                if ( is_active_sidebar( $prefix.'-1' ) ): ?>
                    <aside id="<?php echo $prefix; ?>-sidebar-1" class="sidebar widget<?php echo $sidebar_class; ?>">
                        <?php dynamic_sidebar( $prefix.'-1' ); ?>
                    </aside>
                <?php endif;


                if ( is_active_sidebar( $prefix.'-2' ) ): ?>
                    <aside id="<?php echo $prefix; ?>-sidebar-2" class="sidebar widget<?php echo $sidebar_class; ?>">
                        <?php dynamic_sidebar( $prefix.'-2' ); ?>
                    </aside>
                <?php endif;


                if ( is_active_sidebar( $prefix.'-3' ) ): ?>
                    <aside id="<?php echo $prefix; ?>-sidebar-3" class="sidebar widget<?php echo $sidebar_class; ?>">
                        <?php dynamic_sidebar( $prefix.'-3' ); ?>
                    </aside>
                <?php endif;


                if ( is_active_sidebar( $prefix.'-4' ) ): ?>
                    <aside id="<?php echo $prefix; ?>-sidebar-4" class="sidebar widget<?php echo $sidebar_class; ?>">
                        <?php dynamic_sidebar( $prefix.'-4' ); ?>
                    </aside>
                <?php endif;

                do_action( 'waboot_sidebar_row_bottom' ); ?>
            </div><!-- .row -->

        <?php endif; //$sidebar_class
}

/**
 * Count the number of active widgets to determine dynamic wrapper class
 * @since 1.0
 */
function waboot_sidebar_class( $prefix = false ) {

    if ( ! $prefix )
        _doing_it_wrong( __FUNCTION__, __( 'You must specify a prefix when using alienship_sidebar_class.', 'alienship' ), '1.0' );

    $count = 0;

    if ( is_active_sidebar( $prefix.'-1' ) )
        $count++;

    if ( is_active_sidebar( $prefix.'-2' ) )
        $count++;

    if ( is_active_sidebar( $prefix.'-3' ) )
        $count++;

    if ( is_active_sidebar( $prefix.'-4' ) )
        $count++;

    $class = '';

    switch ( $count ) {
        case '1':
            $class = ' col-sm-12';
            break;

        case '2':
            $class = ' col-sm-6';
            break;

        case '3':
            $class = ' col-sm-4';
            break;

        case '4':
            $class = ' col-sm-3';
            break;
    }

    if ( $class )
        return $class;
}

if ( ! function_exists( 'waboot_get_header_image' ) ):
    /**
     * Returns header image and accompanying markup
     *
     * @since 1.1.1
     * @return array $header_image_attributes (filtered) Header image attributes
     */
    function waboot_get_header_image() {

        global $post;
        $output = '';

        // Get the header image
        if ( get_header_image() ) {

            $header_image_width = get_theme_support( 'custom-header', 'width' );
            $header_image_height = get_theme_support( 'custom-header', 'height' );

            $output = '<a href="' . esc_url( home_url( '/' ) ) . '">';

            // Check if this is a post or page, if it has a thumbnail, and if it's a big one
            if ( is_singular() && has_post_thumbnail( $post->ID )
                /* $src, $width, $height */
                && ( $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), array( $header_image_width, $header_image_height ) ) )
                && $image[1] >= $header_image_width ) {

                // We have a LARGE image
                $featured_header_image = 'yes';
                $output .= get_the_post_thumbnail( $post->ID, 'post-thumbnail' );

            } else {

                $featured_header_image = 'no';
                $header_image_width  = get_custom_header()->width;
                $header_image_height = get_custom_header()->height;
                $output .= '<img src="' . get_header_image() . '" width="' . $header_image_width . '" height="' . $header_image_height . '" class="header-image" alt="">';
            }
            $output .= '</a>';

            $header_image_attributes = array(
                'width'    => $header_image_width,
                'height'   => $header_image_height,
                'featured' => $featured_header_image,
                'output'   => $output,
            );

            return apply_filters( 'waboot_header_image_attributes', $header_image_attributes );

        }else{
            return false;
        }
    }
endif;

if ( ! function_exists( 'waboot_link_format_helper' ) ) :
    /**
     * Returns the first post link and/or post content without the link.
     * Used for the "Link" post format.
     *
     * @since 1.0.1
     * @param string $output "link" or "post_content"
     * @return string Link or Post Content without link.
     */
    function waboot_link_format_helper( $output = false ) {

        if ( ! $output )
            _doing_it_wrong( __FUNCTION__, __( 'You must specify the output you want - either "link" or "post_content".', 'alienship' ), '1.0.1' );

        $post_content = get_the_content();
        $link_start = stristr( $post_content, "http" );
        $link_end = stristr( $link_start, "\n" );

        if ( ! strlen( $link_end ) == 0 ) {
            $link_url = substr( $link_start, 0, -( strlen( $link_end ) + 1 ) );
        } else {
            $link_url = $link_start;
        }

        $post_content = substr( $post_content, strlen( $link_url ) );

        // Return the first link in the post content
        if ( 'link' == $output )
            return $link_url;

        // Return the post content without the first link
        if ( 'post_content' == $output )
            return $post_content;
    }
endif;

if ( ! function_exists( 'waboot_the_attached_image' ) ) :
    /**
     * Prints the attached image with a link to the next attached image.
     */
    function waboot_the_attached_image() {

        $post                = get_post();
        $attachment_size     = apply_filters( 'alienship_attachment_size', array( 1200, 1200 ) );
        $next_attachment_url = wp_get_attachment_url();

        /**
         * Grab the IDs of all the image attachments in a gallery so we can get the
         * URL of the next adjacent image in a gallery, or the first image (if
         * we're looking at the last image in a gallery), or, in a gallery of one,
         * just the link to that image file.
         */
        $attachment_ids = get_posts( array(
            'post_parent'    => $post->post_parent,
            'fields'         => 'ids',
            'numberposts'    => -1,
            'post_status'    => 'inherit',
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'order'          => 'ASC',
            'orderby'        => 'menu_order ID'
        ) );

        // If there is more than 1 attachment in a gallery...
        if ( count( $attachment_ids ) > 1 ) {
            foreach ( $attachment_ids as $attachment_id ) {
                if ( $attachment_id == $post->ID ) {
                    $next_id = current( $attachment_ids );
                    break;
                }
            }

            // get the URL of the next image attachment...
            if ( $next_id )
                $next_attachment_url = get_attachment_link( $next_id );

            // or get the URL of the first image attachment.
            else
                $next_attachment_url = get_attachment_link( array_shift( $attachment_ids ) );
        }

        printf( '<a href="%1$s" title="%2$s" rel="attachment">%3$s</a>',
            esc_url( $next_attachment_url ),
            the_title_attribute( array( 'echo' => false ) ),
            wp_get_attachment_image( $post->ID, $attachment_size )
        );
    }
endif;

if ( ! function_exists( 'waboot_archive_get_posts' ) ):
    /**
     * Display archive posts and exclude sticky posts
     * @since 1.0
     * @unused
     */
    function waboot_archive_get_posts() {

        global $do_not_duplicate, $page, $paged;

        if ( is_category() ) {
            $cat_ID = get_query_var( 'cat' );
            $args = array(
                'cat'                 => $cat_ID,
                'post_status'         => 'publish',
                'post__not_in'        => array_merge( $do_not_duplicate, get_option( 'sticky_posts' ) ),
                'ignore_sticky_posts' => 1,
                'paged'               => $paged
            );
            $wp_query = new WP_Query( $args );

        } elseif (is_tag() ) {
            $current_tag = single_tag_title( "", false );
            $args = array(
                'tag_slug__in'        => array( $current_tag ),
                'post_status'         => 'publish',
                'post__not_in'        => array_merge( $do_not_duplicate, get_option( 'sticky_posts' ) ),
                'ignore_sticky_posts' => 1,
                'paged'               => $paged
            );
            $wp_query = new WP_Query( $args );

        } else {
            new WP_Query();
        }
    }
endif;

