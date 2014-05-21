<?php
/**
 * Nasconde il titolo per post e pagine, utilizzando il modulo behavior
 * @param $title
 * @return string
 * @uses waboot_entry_title_text filter (inc/hooks.php::waboot_do_entry_title)
 * @since 1.0
 */
function waboot_title_toggler($title){
    $show_title = get_behavior("show-title");
    if(is_singular() && ($show_title == "0" || !$show_title || $show_title == 0)){
        return "";
    }
    return $title;
}
add_filter("waboot_entry_title_text","waboot_title_toggler");

/**
 * Creates the title based on current view
 * @since 1.0
 */
function waboot_wp_title( $title, $sep ) {

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
        $title = "$title $sep " . sprintf( __( 'Page %s', 'alienship' ), max( $paged, $page ) );

    return $title;
}
add_filter( 'wp_title', 'waboot_wp_title', 10, 2 );

if ( ! function_exists( 'waboot_comment_reply_link' ) ):
    /**
     * Style comment reply links as buttons
     * @since 1.0
     */
    function waboot_comment_reply_link( $link ) {

        return str_replace( 'comment-reply-link', 'btn btn-default btn-xs', $link );
    }
    add_filter( 'comment_reply_link', 'waboot_comment_reply_link' );
endif;

if ( ! function_exists( 'waboot_nice_search_redirect' ) ):
    /**
     * Pretty search URL. Changes /?s=foo to /search/foo. http://txfx.net/wordpress-plugins/nice-search/
     * @since Alien Ship 0.3
     */
    function waboot_nice_search_redirect() {

        if ( is_search() && get_option( 'permalink_structure' ) != '' && strpos( $_SERVER['REQUEST_URI'], '/wp-admin/' ) === false && strpos( $_SERVER['REQUEST_URI'], '/search/' ) === false ) {
            wp_redirect( home_url( '/search/' . str_replace( array( ' ', '%20' ),  array( '+', '+' ), get_query_var( 's' ) ) ) );
            exit();
        }
    }
    add_action( 'template_redirect', 'waboot_nice_search_redirect' );
endif;

if ( ! function_exists( 'waboot_excerpt_more') ):
    /*
     * Style the excerpt continuation
     */
    function waboot_excerpt_more( $more ) {

        return ' ... <a href="'. get_permalink( get_the_ID() ) . '">'. __( 'Continue Reading ', 'alienship' ) .' &raquo;</a>';
    }
    add_filter('excerpt_more', 'waboot_excerpt_more');
endif;

/**
 * Cleanup the head
 * @source http://geoffgraham.me/wordpress-how-to-clean-up-the-header/
 * @since 1.0
 */
function waboot_head_cleanup() {
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
add_action('init', 'waboot_head_cleanup');