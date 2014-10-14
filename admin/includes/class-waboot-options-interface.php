<?php

require_once "class-waboot-options-interface.php";

class Waboot_Options_Framework_Interface extends Options_Framework_Interface{
    /**
     * Generates the tabs that are used in the options menu
     */
    static function optionsframework_tabs() {
        $counter = 0;
        $options = & Options_Framework::_optionsframework_options();
        $menu = '';

        foreach ( $options as $value ) {
            // Heading for Navigation
            if ( $value['type'] == "heading" ) {
                $counter++;
                $class = '';
                $class = ! empty( $value['id'] ) ? $value['id'] : $value['name'];
                $class = preg_replace( '/[^a-zA-Z0-9._\-]/', '', strtolower($class) ) . '-tab';
                $menu .= '<li><a id="options-group-'.  $counter . '-tab" class="nav-tab ' . $class .'" title="' . esc_attr( $value['name'] ) . '" href="' . esc_attr( '#options-group-'.  $counter ) . '">' . esc_html( $value['name'] ) . '</a></li>';
            }
        }

        return $menu;
    }
}