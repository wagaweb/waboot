<?php

require_once("hooks/entry-header.php");
require_once("hooks/entry-footer.php");

if ( ! function_exists( 'waboot_do_site_title' ) ):
    /**
     * Displays site title at top of page
     *
     * @since 1.1.1
     */
    function waboot_do_site_title() {

        // Use H1 on home, paragraph elsewhere
        $element = is_front_page() || is_home() ? 'h1' : 'p';

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
     * @since 1.1.1
     */
    function waboot_do_site_description() {

        // Use H2 on home, paragraph elsewhere
        $element = is_front_page() || is_home() ? 'h2' : 'p';

        // Put it all together
        $description = '<' . $element . ' id="site-description" class="site-description">' . esc_html( get_bloginfo( 'description' ) ) . '</' . $element . '>';

        // Echo the description
        echo apply_filters( 'alienship_site_description_content', $description );
    }
    add_action( 'waboot_site_description', 'waboot_do_site_description' );
endif;

if ( ! function_exists( 'waboot_do_archive_page_title' ) ):
    /**
     * Display page title on archive pages
     * @since 1.0
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
                    printf( __( 'Author: %s', 'alienship' ), '<span class="vcard"><a class="url fn n" href="' . get_author_posts_url( get_the_author_meta( "ID" ) ) . '" title="' . esc_attr( get_the_author() ) . '" rel="me">' . get_the_author() . '</a></span>' );

                } elseif ( is_day() ) {
                    printf( __( 'Day: %s', 'alienship' ), '<span>' . get_the_date() . '</span>' );

                } elseif ( is_month() ) {
                    printf( __( 'Month: %s', 'alienship' ), '<span>' . get_the_date( 'F Y' ) . '</span>' );

                } elseif ( is_year() ) {
                    printf( __( 'Year: %s', 'alienship' ), '<span>' . get_the_date( 'Y' ) . '</span>' );

                } elseif ( is_tax( 'post_format', 'post-format-aside' ) ) {
                    _e( 'Asides', 'alienship' );

                } elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
                    _e( 'Galleries', 'alienship');

                } elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
                    _e( 'Images', 'alienship');

                } elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
                    _e( 'Videos', 'alienship' );

                } elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
                    _e( 'Quotes', 'alienship' );

                } elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
                    _e( 'Links', 'alienship' );

                } elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
                    _e( 'Statuses', 'alienship' );

                } elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
                    _e( 'Audios', 'alienship' );

                } elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
                    _e( 'Chats', 'alienship' );

                } else {
                    _e( 'Archives', 'alienship' );

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