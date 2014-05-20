<?php
if ( ! function_exists( 'waboot_locate_template_uri' ) ):
    /**
     * Snatched from future release code in WordPress repo.
     *
     * Retrieve the URI of the highest priority template file that exists.
     *
     * Searches in the stylesheet directory before the template directory so themes
     * which inherit from a parent theme can just override one file.
     *
     * @param string|array $template_names Template file(s) to search for, in order.
     * @return string The URI of the file if one is located.
     */
    function waboot_locate_template_uri( $template_names ) {

        $located = '';
        foreach ( (array) $template_names as $template_name ) {
            if ( ! $template_name )
                continue;

            if ( file_exists( get_stylesheet_directory() . '/' . $template_name ) ) {
                $located = get_stylesheet_directory_uri() . '/' . $template_name;
                break;
            } else if ( file_exists( get_template_directory() . '/' . $template_name ) ) {
                $located = get_template_directory_uri() . '/' . $template_name;
                break;
            }
        }

        return $located;
    }
endif;