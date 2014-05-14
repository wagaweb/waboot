<?php
/**
 * Non visualizza il titolo per post e pagine, utilizzando il modulo behavior e il filtro implementato da Alienship
 * @param $title
 * @return string
 * @todo Rinominare il filtro di Alienship
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