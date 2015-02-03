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

if ( ! function_exists( 'waboot_set_default_components' ) ):
    /**
     * Set the default components
     * @param $components
     * @return array
     */
    function waboot_set_default_components($components){
        $components[] = "slideshow";
        $components[] = "colorbox";

        return $components;
    }
    add_filter("wbf_default_components","waboot_set_default_components");
endif;

if ( ! function_exists( 'waboot_mainnav_class' ) ):
    function waboot_mainnav_class($classes){
        $options = of_get_option( 'waboot_navbar_align' );
        $classes[] = $options;

        return implode(' ', $classes);
    }
    add_filter("waboot_mainnav_class","waboot_mainnav_class");
endif;