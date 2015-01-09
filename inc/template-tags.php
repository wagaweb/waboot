<?php

if ( !function_exists("images_url") ) :
    function images_url(){
        echo get_images_url();
    }
endif;

if ( !function_exists("get_images_url") ) :
    function get_images_url(){
        $base_dir = get_template_directory_uri();
        if(is_child_theme()){
            $base_dir = get_stylesheet_directory_uri();
        }
        return apply_filters("waboot_images_url",$base_dir."/assets/images");
    }
endif;

if ( ! function_exists( 'waboot_content_nav' ) ):
    /**
     * Display navigation to next/previous pages when applicable
     *
     * @since 0.1.0
     * @from Waboot
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
            <ul class="pager">
                <?php
                if ( is_single() ) : // navigation links for single posts

                    previous_post_link( '<li class="previous">%link</li>', '<span class="meta-nav">' . _x( '&laquo;', 'Previous post link', 'waboot' ) . '</span> %title' );
                    next_post_link( '<li class="next">%link</li>', '%title <span class="meta-nav">' . _x( '&raquo;', 'Next post link', 'waboot' ) . '</span>' );

                elseif ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages

                    if ( get_next_posts_link() ) : ?>
                        <li class="pull-right"><?php next_posts_link( __( 'Next page <span class="meta-nav">&raquo;</span>', 'waboot' ) ); ?></li>
                    <?php endif;

                    if ( get_previous_posts_link() ) : ?>
                        <li class="pull-left"><?php previous_posts_link( __( '<span class="meta-nav">&laquo;</span> Previous page', 'waboot' ) ); ?></li>
                    <?php endif;

                endif; ?>
            </ul>
        </nav><!-- #<?php echo $nav_id; ?> -->
    <?php
    }
endif; // waboot_content_nav

if ( ! function_exists( 'waboot_archive_sticky_posts' ) ):
    /**
     * Display sticky posts on archive pages
     * @since 0.1.0
     * @from Waboot
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
                <?php _e( 'Pingback:', 'waboot' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( 'Edit', 'waboot' ), '<span class="edit-link">', '</span>' ); ?>
            </div>

        <?php else : ?>

        <li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?>>
            <article id="div-comment-<?php comment_ID(); ?>" class="comment-body">
                <footer class="comment-meta">
                    <div class="comment-author vcard">
                        <?php if ( 0 != $args['avatar_size'] ) echo get_avatar( $comment, $args['avatar_size'] ); ?>
                        <?php printf( __( '%s <span class="says">says:</span>', 'waboot' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
                    </div><!-- .comment-author -->

                    <div class="comment-metadata">
                        <a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
                            <time datetime="<?php comment_time( 'c' ); ?>">
                                <?php printf( _x( '%1$s at %2$s', '1: date, 2: time', 'waboot' ), get_comment_date(), get_comment_time() ); ?>
                            </time>
                        </a>
                        <?php edit_comment_link( __( 'Edit', 'waboot' ), '<span class="edit-link">', '</span>' ); ?>
                    </div><!-- .comment-metadata -->

                    <?php if ( '0' == $comment->comment_approved ) : ?>
                        <p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'waboot' ); ?></p>
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

function waboot_has_sidebar($prefix){
    $has_sidebar = false;
    for ($i = 1; $i <= 4; $i++) {
        if (is_active_sidebar($prefix . "-" . $i)) {
            $has_sidebar = true;
        }
    }
    return $has_sidebar;
}

/**
 * Determines the theme layout and active sidebars, and prints the HTML structure
 * with appropriate grid classes depending on which are activated.
 *
 * @since 0.1.0
 * @uses waboot_sidebar_class()
 * @param string $prefix Prefix of the widget to be displayed. Example: "footer" for footer-1, footer-2, etc.
 */
function waboot_do_sidebar( $prefix = false ) {
    if ( ! $prefix )
        _doing_it_wrong( __FUNCTION__, __( 'You must specify a prefix when using waboot_do_sidebar.', 'waboot' ), '1.0' );

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
 * @since 0.1.0
 */
function waboot_sidebar_class( $prefix = false ) {
    if ( ! $prefix )
        _doing_it_wrong( __FUNCTION__, __( 'You must specify a prefix when using waboot_sidebar_class.', 'waboot' ), '1.0' );

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

if ( ! function_exists( 'waboot_link_format_helper' ) ) :
    /**
     * Returns the first post link and/or post content without the link.
     * Used for the "Link" post format.
     *
     * @since 0.1.0
     * @param string $output "link" or "post_content"
     * @return string Link or Post Content without link.
     */
    function waboot_link_format_helper( $output = false ) {

        if ( ! $output )
            _doing_it_wrong( __FUNCTION__, __( 'You must specify the output you want - either "link" or "post_content".', 'waboot' ), '1.0.1' );

        $post_content = get_the_content();
        $link_start = stristr( $post_content, "http" );
        $link_end = stristr( $link_start, "\n" );

        $link = preg_match( '/<a\s[^>]*?href=[\'"](.+?)[\'"]/is', $post_content, $matches );

        if ( ! strlen( $link_end ) == 0 ) {
            $link_url = substr( $link_start, 0, -( strlen( $link_end ) + 1 ) );
        } else {
            $link_url = $link_start;
        }

        $post_content = substr( $post_content, strlen( $link_url ) );

        // Return the first link in the post content
        if ( 'link' == $output ){
            if($link){
                return $matches[1];
            }else{
                return "";
            }
        }

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
        $attachment_size     = apply_filters( 'waboot_attachment_size', array( 1200, 1200 ) );
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
     * @since 0.1.0
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

if (!function_exists("waboot_breadcrumb")):
    /**
     * Display the breadcrumb for $post_id or global $post->ID
     * @param null $post_id
     * @param string $current_location the current location of breadcrumb. Not used at the moment.
     * @param array $args settings for breadcrumb (see: waboot_breadcrumb_trail() documentation)
     * @since 0.3.10
     */
    function waboot_breadcrumb($post_id = null, $current_location = "", $args = array())
    {
        global $post;
        if (function_exists('waboot_breadcrumb_trail')) {
            if (is_front_page() || is_404() ) return;

            $post_id = isset($post_id) ? $post_id : $post->ID;
            $current_post_type = get_post_type($post_id);
            $args = wp_parse_args($args, array(
                'container' => "div",
                'separator' => "/",
                'show_browse' => false,
                'additional_classes' => ""
            ));

            if (!isset($post_id) || $post_id == 0 || !$current_post_type) return;

            $bc_locations = of_get_option('waboot_breadcrumb_locations', array('post', 'page'));
            if (array_key_exists($current_post_type, $bc_locations) && $bc_locations[$current_post_type] == 1) {
                waboot_breadcrumb_trail($args);
            }
        }
    }
endif;

if (!function_exists("waboot_topnav_wrapper")):
    function waboot_topnav_wrapper(){
        $social_position = of_get_option('waboot_social_position');
        $social_position_class = $social_position == "topnav-left" ? "pull-left" : "pull-right";
        $topnavmenu_position = of_get_option('waboot_topnavmenu_position');
        $topnavmenu_position_class = $topnavmenu_position == "left" ? "pull-left" : "pull-right";
        $has_menu = has_nav_menu('top');
        $must_display_topnav = (is_active_sidebar('topbar') || ($social_position == 'topnav-right' || $social_position == 'topnav-left') || $has_menu) ? true : false;

        if ($must_display_topnav):
            ?>
            <!-- Navbar: Begin -->
            <div id="topnav-wrapper">
                <div id="topnav-inner" class="<?php echo of_get_option('waboot_topnav_width', 'container-fluid'); ?> ">

                    <div class="<?php echo $social_position_class; ?>">
                        <?php get_template_part('/templates/parts/social-widget'); ?>
                    </div>

                    <div class="<?php echo $topnavmenu_position_class; ?>">
                        <?php if ($has_menu) get_template_part('/templates/parts/menu', 'top'); ?>
                    </div>

                    <?php dynamic_sidebar('topbar'); ?>
                </div>
            </div>
            <!-- Navbar: End -->
        <?php
        endif;
    }
endif;

if(!function_exists("waboot_get_sidebar_layout")):
    function waboot_get_sidebar_layout(){
        if(is_home()){
            return of_get_option('waboot_blogpage_sidebar_layout');
        }else{
            return get_behavior('layout');
        }
    }
endif;

if(!function_exists("waboot_get_sidebar_layouts")){
    function waboot_get_sidebar_layouts(){
        return apply_filters("waboot_sidebar_layouts",array(
            array(
                "name" => __("Full width. No sidebar.","waboot"),
                "value" => "full-width"
            ),
            array(
                "name" => __("Sidebar right","waboot"),
                "value" => "sidebar-right"
            ),
            array(
                "name" => __("Sidebar left","waboot"),
                "value" => "sidebar-left"
            ),
            '_default' => 'sidebar-right'
        ));
    }
}

if(!function_exists("waboot_get_compiled_stylesheet_name")):
	function waboot_get_compiled_stylesheet_name(){
		return apply_filters("waboot_compiled_stylesheet_name",wp_get_theme()->stylesheet);
	}
endif;