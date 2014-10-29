<?php

require_once("hooks/entry-header.php");
require_once("hooks/entry-footer.php");

if ( ! function_exists( 'waboot_do_site_title' ) ):
    /**
     * Displays site title at top of page
     *
     * @since 0.1.0
     */
    function waboot_do_site_title() {

        // Use H1 on home, paragraph elsewhere
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
     *
     * @since 0.1.0
     */
    function waboot_do_site_description() {

        // Use H2 on home, paragraph elsewhere
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

function waboot_add_compile_sets($sets){
    $theme = apply_filters("waboot_compiled_stylesheet_name",wp_get_theme()->stylesheet);

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

/**
 * Add a "Compile Less" button to the toolbar
 * @param $wp_admin_bar
 * @since 0.1.1
 */
function waboot_add_admin_compile_button($wp_admin_bar){
    global $post;

    if ( current_user_can( 'manage_options' ) ) {
        $args = array(
            'id'    => 'waboot_compile',
            'title' => 'Compile Less',
            'href'  => add_query_arg('compile','true'),
            'meta'  => array( 'class' => 'toolbar-compile-less-button' )
        );
        $wp_admin_bar->add_node( $args );
    }
}
add_action( 'admin_bar_menu', 'waboot_add_admin_compile_button', 990 );

/**
 * Add env notice to the admin bar
 * @param $wp_admin_bar
 * @since 0.2.0
 */
function waboot_add_env_notice($wp_admin_bar){
    global $post;

    if ( current_user_can( 'manage_options' ) ) {
        $args = array(
            'id'    => 'env_notice',
            'title' => '['.WABOOT_ENV."]",
            'href'  => "#",
            'meta'  => array( 'class' => 'toolbar-env-notice' )
        );
        $wp_admin_bar->add_node( $args );
    }
}
add_action( 'admin_bar_menu', 'waboot_add_env_notice', 980 );

if(!is_admin() && !function_exists("waboot_mobile_body_class")):
/**
 * Adds mobile classes to body
 */
function waboot_mobile_body_class($classes){
    global $md;
    if($md->isMobile()){
        $classes[] = "mobile";
        if($md->is_ios()) $classes[] = "mobile-ios";
        if($md->is_android()){
            $classes[] = "mobile-android";
            $classes[] = "mobile-android-".$md->version('Android');
        }
        if($md->is_windows_mobile()) $classes[] = "mobile-windows";
        if($md->isTablet()) $classes[] = "mobile-tablet";
        if($md->isIphone()){
            $classes[] = "mobile-iphone";
            $classes[] = "mobile-iphone-".$md->version('IPhone');
        }
        if($md->isIpad()){
            $classes[] = "mobile-ipad";
            $classes[] = "mobile-ipad-".$md->version('IPad');
        }
        if($md->is('Kindle')) $classes[] = "mobile-kindle";
        if($md->is('Samsung')) $classes[] = "mobile-samsung";
        if($md->is('SamsungTablet')) $classes[] = "mobile-samsungtablet";
    }else{
        $classes[] = "desktop";
    }
    return $classes;
}
add_filter('body_class','waboot_mobile_body_class');
endif;