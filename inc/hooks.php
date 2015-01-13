<?php

require_once("hooks/entry-header.php");
require_once("hooks/entry-footer.php");
require_once("hooks/layout.php");

if ( ! function_exists( 'waboot_do_site_title' ) ):
    /**
     * Displays site title at top of page
     * @since 0.1.0
     */
    function waboot_do_site_title() {
        // Use H1
        $element = 'h1';
        // Title content that goes inside wrapper
        $site_name = sprintf( '<a href="%s" title="%s" rel="home">%s</a>', trailingslashit( home_url() ), esc_attr( get_bloginfo( 'name' ) ), get_bloginfo( 'name' ) );
        // Put it all together
        $title = '<' . $element . ' id="site-title" class="site-title">' . $site_name . '</' . $element .'>';
        // Echo the title
        echo apply_filters( 'waboot_site_title_content', $title );
    }
    add_action( 'waboot_site_title', 'waboot_do_site_title' );
endif;

if( ! function_exists( 'waboot_do_site_description' ) ):
    /**
     * Displays site description at top of page
     * @since 0.1.0
     */
    function waboot_do_site_description() {
        // Use H2
        $element = 'h2';
        // Put it all together
        $description = '<' . $element . ' id="site-description" class="site-description">' . esc_html( get_bloginfo( 'description' ) ) . '</' . $element . '>';
        // Echo the description
        echo apply_filters( 'waboot_site_description_content', $description );
    }
    add_action( 'waboot_site_description', 'waboot_do_site_description' );
endif;

if ( ! function_exists( 'waboot_do_archive_page_title' ) ):
    /**
     * Display page title on archive pages
     * @since 0.1.0
     */
    function waboot_do_archive_page_title() { ?>
        <header class="page-header">
            <h1 class="page-title">
                <?php
                if ( is_category() ) {
                    single_cat_title();

                } elseif ( is_tag() ) {
                    single_tag_title();

                } elseif ( is_author() ) {
                    printf( __( 'Author: %s', 'waboot' ), '<span class="vcard"><a class="url fn n" href="' . get_author_posts_url( get_the_author_meta( "ID" ) ) . '" title="' . esc_attr( get_the_author() ) . '" rel="me">' . get_the_author() . '</a></span>' );

                } elseif ( is_day() ) {
                    printf( __( 'Day: %s', 'waboot' ), '<span>' . get_the_date() . '</span>' );

                } elseif ( is_month() ) {
                    printf( __( 'Month: %s', 'waboot' ), '<span>' . get_the_date( 'F Y' ) . '</span>' );

                } elseif ( is_year() ) {
                    printf( __( 'Year: %s', 'waboot' ), '<span>' . get_the_date( 'Y' ) . '</span>' );

                } elseif ( is_tax( 'post_format', 'post-format-aside' ) ) {
                    _e( 'Asides', 'waboot' );

                } elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
                    _e( 'Galleries', 'waboot');

                } elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
                    _e( 'Images', 'waboot');

                } elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
                    _e( 'Videos', 'waboot' );

                } elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
                    _e( 'Quotes', 'waboot' );

                } elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
                    _e( 'Links', 'waboot' );

                } elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
                    _e( 'Statuses', 'waboot' );

                } elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
                    _e( 'Audios', 'waboot' );

                } elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
                    _e( 'Chats', 'waboot' );

                } else {
                    _e( 'Archives', 'waboot' );

                } ?>
            </h1>

            <?php
            // show an optional category description
            $term_description = term_description();
            if ( ! empty( $term_description ) )
                printf( '<div class="taxonomy-description">%s</div>', $term_description ); ?>

        </header>
    <?php }
    add_action( 'waboot_archive_page_title', 'waboot_do_archive_page_title' );
endif;

if( ! function_exists('waboot_load_gfonts') ):
    function waboot_load_gfonts($options){
        $options[] = "waboot_primaryfont";
        $options[] = "waboot_secondaryfont";

        return $options;
    }
    add_filter("wbf_of_gfonts_options","waboot_load_gfonts");
endif;

if ( ! function_exists( 'waboot_behaviors_cpts_blacklist' ) ):
    /**
     * Puts some custom post types into blacklist (in these post types the behavior will never be displayed)
     * @param $blacklist
     * @return array
     */
    function waboot_behaviors_cpts_blacklist($blacklist){
        $blacklist[] = "metaslider";
        return $blacklist;
    }
    add_filter("waboot_behaviors_cpts_blacklist","waboot_behaviors_cpts_blacklist");
endif;

function waboot_add_compile_sets($sets){
    $theme = waboot_get_compiled_stylesheet_name();

    return array_merge_recursive($sets,array(
        "theme_frontend" => array(
            "input" => get_stylesheet_directory()."/sources/less/{$theme}.less",
            "output" => get_stylesheet_directory()."/assets/css/{$theme}.css",
            "map" => get_stylesheet_directory()."/assets/css/{$theme}.css.map",
            "map_url" => get_stylesheet_directory_uri()."/assets/css/{$theme}.css.map",
            "cache" => get_stylesheet_directory()."/assets/cache",
            "import_url" => get_stylesheet_directory_uri()
        )
    ));
}
add_filter('waboot_compile_sets','waboot_add_compile_sets');

function waboot_set_compiled_stylesheet_name($name){

    /*$theme = wp_get_theme()->stylesheet;
    if($theme == "wship") $theme = "waboot"; //Brutal compatibility hack :)*/

    if(is_child_theme()){
        return "waboot-child";
    }else{
        return "waboot";
    }
}
add_filter('waboot_compiled_stylesheet_name','waboot_set_compiled_stylesheet_name');