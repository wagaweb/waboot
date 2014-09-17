<?php

require_once("vendor/BootstrapNavMenuWalker.php");

class WabootNavMenuWalker extends BootstrapNavMenuWalker {

    /**
     * Starts a new <ul> element
     * @param string $output
     * @param int $depth
     * @param array $args
     */
    function start_lvl( &$output, $depth = 0, $args = array() ) {

        $padding = 10 * $depth;
        $padding_output = "style='margin-left:{$padding}px'";

        $indent = str_repeat( "\t\t\t\t", $depth );
        $submenu = ($depth > 0) ? ' sub-menu' : '';

        $output	   .= "\n".$indent."<ul class='dropdown-menu ".$submenu." depth_".$depth."'>\n";

        /*if($depth == 0)
            $output	   .= "\n$indent<ul class=\"dropdown-menu$submenu depth_$depth\">\n";
        else
            $output	   .= "\n$indent<ul class=\"dropdown-menu$submenu depth_$depth\" style=\"margin-left: 160px; margin-top: -31px; \">\n";*/

    }

    /**
     * Starts a new <li><a> elements
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $item   Menu item data object.
     * @param int    $depth  Depth of menu item. Used for padding.
     * @param array(in reality this is a stdClass) $args  An array of arguments. @see wp_nav_menu()
     * @param int    $id     Current item ID.
     */
    function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {

        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        /**
         * <li> element
         */
        $classes = empty( $item->classes ) ? array() : (array) $item->classes; //$item->classes contains wordpress-given menu item classes

        // managing divider: add divider class to an element to get a divider before it.
        $divider_class_position = array_search('divider', $classes);
        if($divider_class_position !== false){
            $output .= "<li class=\"divider\"></li>\n";
            unset($classes[$divider_class_position]);
        }

        $classes[] = ($args->has_children) ? 'dropdown' : '';
        $classes[] = ($item->current || $item->current_item_ancestor) ? 'active' : '';
        $classes[] = 'menu-item-' . $item->ID;
        if($depth > 0 && $args->has_children){
            $classes[] = 'dropdown-submenu';
        }


        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) ); //array_filter( $classes ) will remove any empty or false element
        $class_names = ' class="' . esc_attr( $class_names ) . '"';

        $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
        $id = strlen( $id ) ? ' id="' . esc_attr( $id ) . '"' : '';

        $output .= $indent . '<li' . $id . $class_names . '>';

        /**
         * <a> element
         */
        $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
        $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
        $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
        $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';
        if($args->has_children){
            $attributes .= ' class="dropdown-toggle" data-toggle="dropdown"';
        }

        $item_output = $args->before;
        $item_output .= '<a'. $attributes .'>';
        $item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
        $item_output .= '<br /><span class="menudescription">' . $item->description . '</span>';
        if($args->has_children && $depth == 0){
            $item_output .= '<b class="caret"></b></a>'; //first level <li><a> with submenus
        }elseif($args->has_children && $depth > 0){
            $item_output .= '<b class="arrow-right"></b></a>'; //subsequent levels of <li><a> with submenus
        }else{
            $item_output .= '</a>';
        }
        $item_output .= $args->after;

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }

    /**
     * Menu Fallback
     * =============
     * If this function is assigned to the wp_nav_menu's fallback_cb variable
     * and a manu has not been assigned to the theme location in the WordPress
     * menu manager the function with display nothing to a non-logged in user,
     * and will add a link to the WordPress menu manager if logged in as an admin.
     *
     */
    public static function fallback($args = null) {
        if ( ! current_user_can( 'manage_options' ) )
        {
            return;
        }

        // see wp-includes/nav-menu-template.php for available arguments
        extract( $args );

        $link = $link_before
            . '<a href="' .admin_url( 'nav-menus.php' ) . '">' . $before . 'Add a menu' . $after . '</a>'
            . $link_after;

        // We have a list
        if ( FALSE !== stripos( $items_wrap, '<ul' )
            or FALSE !== stripos( $items_wrap, '<ol' )
        )
        {
            $link = "<li>$link</li>";
        }

        $output = sprintf( $items_wrap, $menu_id, $menu_class, $link );
        if ( ! empty ( $container ) )
        {
            $output  = "<$container class='$container_class' id='$container_id'>$output</$container>";
        }

        if ( $echo )
        {
            echo $output;
        }

        return $output;
    }
}

function waboot_nav_menu_fallback($args){
    if ( ! current_user_can( 'manage_options' ) )
    {
        return false;
    }

    extract( $args ); // see wp-includes/nav-menu-template.php for available arguments

    $link = $link_before
        . '<a href="' .admin_url( 'nav-menus.php' ) . '">' . $before . 'Add a menu' . $after . '</a>'
        . $link_after;

    // We have a list
    if ( FALSE !== stripos( $items_wrap, '<ul' )
        or FALSE !== stripos( $items_wrap, '<ol' )
    )
    {
        $link = "<li>$link</li>";
    }

    $output = sprintf( $items_wrap, $menu_id, $menu_class, $link );
    if ( ! empty ( $container ) )
    {
        $output  = "<$container class='$container_class' id='$container_id'>$output</$container>";
    }

    if ( $echo )
    {
        echo $output;
    }

    return $output;
}