<?php

require_once("template_tags/entry-header.php");
require_once("template_tags/entry-footer.php");

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
        echo apply_filters( 'alienship_site_title_content', $title );
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

