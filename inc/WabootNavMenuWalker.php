<?php

require_once("vendor/BootstrapNavMenuWalker.php");

class WabootNavMenuWalker extends BootstrapNavMenuWalker {

    function start_lvl( &$output, $depth ) {

        $padding = 10 * $depth;
        $padding_output = "style='margin-left:{$padding}px'";

        $indent = str_repeat( "\t\t\t\t", $depth );
        $submenu = ($depth > 0) ? ' sub-menu' : '';

        $output	   .= "\n$indent<ul class=\"dropdown-menu$submenu depth_$depth\">\n";

        /*if($depth == 0)
            $output	   .= "\n$indent<ul class=\"dropdown-menu$submenu depth_$depth\">\n";
        else
            $output	   .= "\n$indent<ul class=\"dropdown-menu$submenu depth_$depth\" style=\"margin-left: 160px; margin-top: -31px; \">\n";*/

    }

    function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {

        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $li_attributes = '';
        $class_names = $value = '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;

        // managing divider: add divider class to an element to get a divider before it.
        $divider_class_position = array_search('divider', $classes);
        if($divider_class_position !== false){
            $output .= "<li class=\"divider\"></li>\n";
            unset($classes[$divider_class_position]);
        }

        $classes[] = ($args->has_children) ? 'dropdown' : '';
        $classes[] = ($item->current || $item->current_item_ancestor) ? 'active' : '';
        $classes[] = 'menu-item-' . $item->ID;
        if($depth && $args->has_children){
            $classes[] = 'dropdown-submenu';
        }


        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
        $class_names = ' class="' . esc_attr( $class_names ) . '"';

        $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
        $id = strlen( $id ) ? ' id="' . esc_attr( $id ) . '"' : '';

        $output .= $indent . '<li' . $id . $value . $class_names . $li_attributes . $padding_output . '>';

        $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
        $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
        $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
        $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
        $attributes .= ($args->has_children) 	    ? ' class="dropdown-toggle" data-toggle="dropdown"' : '';

        $item_output = $args->before;
        $item_output .= '<a'. $attributes .'>';
        $item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
        $item_output .= $args->has_children ? ' <b class="caret"></b></a>' : '</a>';
        $item_output .= $args->after;


        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
}