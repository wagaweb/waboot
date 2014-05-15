<?php
/**
 * Non visualizza il titolo per post e pagine, utilizzando il modulo behavior e il filtro implementato da Alienship
 * @param $title
 * @return string
 * @todo Rinominare il filtro di Alienship
 * @since 1.0
 */
function waboot_title_toggler($title){
    $show_title = get_behavior("show-title");
    if($show_title == "0"){
        return "";
    }
    return $title;
}
add_filter("alienship_entry_title_text","waboot_title_toggler");

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