<?php


/**
 * Creates the title based on current view
 * @since 0.1.0
 */
function wbf_wp_title( $title, $sep ) {

    global $paged, $page;

    if ( is_feed() )
        return $title;

    // Add the site name.
    $title .= get_bloginfo( 'name', 'display' );

    // Add the site description for the home/front page.
    $site_description = get_bloginfo( 'description', 'display' );

    if ( $site_description && ( is_home() || is_front_page() ) )
        $title = "$title $sep $site_description";

    // Add a page number if necessary.
    if ( $paged >= 2 || $page >= 2 )
        $title = "$title $sep " . sprintf( __( 'Page %s', 'wbf' ), max( $paged, $page ) );

    return $title;
}
add_filter( 'wp_title', 'wbf_wp_title', 10, 2 );

if ( ! function_exists( 'wbf_comment_reply_link' ) ):
    /**
     * Style comment reply links as buttons
     * @since 0.1.0
     */
    function wbf_comment_reply_link( $link ) {

        return str_replace( 'comment-reply-link', 'btn btn-default btn-xs', $link );
    }
    add_filter( 'comment_reply_link', 'wbf_comment_reply_link' );
endif;

if ( ! function_exists( 'wbf_nice_search_redirect' ) ):
    /**
     * Pretty search URL. Changes /?s=foo to /search/foo. http://txfx.net/wordpress-plugins/nice-search/
     * @since 0.1.0
     */
    function wbf_nice_search_redirect() {

        if ( is_search() && get_option( 'permalink_structure' ) != '' && strpos( $_SERVER['REQUEST_URI'], '/wp-admin/' ) === false && strpos( $_SERVER['REQUEST_URI'], '/search/' ) === false ) {
            wp_redirect( home_url( '/search/' . str_replace( array( ' ', '%20' ),  array( '+', '+' ), get_query_var( 's' ) ) ) );
            exit();
        }
    }
    add_action( 'template_redirect', 'wbf_nice_search_redirect' );
endif;

if ( ! function_exists( 'wbf_excerpt_more') ):
    /*
     * Style the excerpt continuation
     */
    function wbf_excerpt_more( $more ) {

        return ' ... <a href="'. get_permalink( get_the_ID() ) . '">'. __( 'Continue Reading ', 'wbf' ) .' &raquo;</a>';
    }
    add_filter('excerpt_more', 'wbf_excerpt_more');
endif;

/**
 * Cleanup the head
 * @source http://geoffgraham.me/wordpress-how-to-clean-up-the-header/
 * @since 0.1.0
 */
function wbf_head_cleanup() {
    // EditURI link
    remove_action( 'wp_head', 'rsd_link' );
    // Category feed links
    remove_action( 'wp_head', 'feed_links_extra', 3 );
    // Post and comment feed links
    remove_action( 'wp_head', 'feed_links', 2 );
    // Windows Live Writer
    remove_action( 'wp_head', 'wlwmanifest_link' );
    // Index link
    remove_action( 'wp_head', 'index_rel_link' );
    // Previous link
    remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
    // Start link
    remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
    // Canonical
    remove_action('wp_head', 'rel_canonical', 10, 0 );
    // Shortlink
    remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
    // Links for adjacent posts
    remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
    // WP version
    remove_action( 'wp_head', 'wp_generator' );
}
add_action('init', 'wbf_head_cleanup');

/**
 * Add WP Better email support for gravity form
 * @param $notification
 * @param $form
 * @param $entry
 * @return mixed
 */
function change_notification_format( $notification, $form, $entry ) {
    // is_plugin_active is not availble on front end
    if( !is_admin() )
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    // does WP Better Emails exists and activated ?
    if( !is_plugin_active('wp-better-emails/wpbe.php') )
        return $notification;

    // change notification format to text from the default html
    $notification['message_format'] = "text";
    // disable auto formatting so you don't get double line breaks
    $notification['disableAutoformat'] = true;

    return $notification;
}
add_filter('gform_notification', 'change_notification_format', 10, 3);