<?php
/**
 * Alien Ship functions and definitions
 *
 * @package Alien Ship
 * @subpackage Functions
 * @author John Parris
 * @copyright Copyright (c) 2012, John Parris
 * @link http://www.johnparris.com/alienship/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 * @since Alien Ship 0.1
 */
if ( ! isset( $content_width ) )
    $content_width = 940; // pixels

// Display a notice about menu functionality
function alienship_admin_notice_menus() {

    global $current_user, $pagenow;
    $user_id = $current_user->ID;

    // Check that we're an admin, that we're on the menus page, and that the user hasn't already ignored the message
    if ( current_user_can( 'administrator' ) && $pagenow =='nav-menus.php' && ! get_user_meta( $user_id, 'alienship_admin_notice_menus_ignore_notice' ) ) {
        echo '<div class="updated"><p>';
        printf( __( 'Dropdown menus work a little differently in Alien Ship. They do not activate on mouse hover, but on click instead. This means that the top/parent menu item does not click through to a page, but only activates the dropdown. Sub-menus are not supported. Design your menus with this in mind. For more information, read the <a href="http://www.johnparris.com/alienship/documentation" target="_blank">Alien Ship documentation</a> online. | <a href="%1$s">Hide this notice</a>' ), '?alienship_admin_notice_menus_ignore=0' );
        echo "</p></div>";
    }
}
add_action( 'admin_notices', 'alienship_admin_notice_menus' );

function alienship_admin_notice_menus_ignore() {

    global $current_user;
    $user_id = $current_user->ID;

    // If user clicks to ignore the notice, add that to their user meta
    if ( isset( $_GET['alienship_admin_notice_menus_ignore'] ) && '0' == $_GET['alienship_admin_notice_menus_ignore'] ) {
        add_user_meta( $user_id, 'alienship_admin_notice_menus_ignore_notice', 'true', true );
    }
}
add_action( 'admin_init', 'alienship_admin_notice_menus_ignore' );

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