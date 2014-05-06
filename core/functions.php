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

if ( ! function_exists( 'alienship_locate_template_uri' ) ):
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
    function alienship_locate_template_uri( $template_names ) {

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

/**
 * Alien Ship RSS Feed Dashboard Widget
 *
 * Retrieve the latest news from Alien Ship home page
 *
 * @since 1.0
 *
 */
function alienship_rss_dashboard_widget() {

    echo '<div class="rss-widget">';
    wp_widget_rss_output( array(
        'url'          => 'http://www.johnparris.com/alienship/feed',
        'title'        => 'Alien Ship News',
        'items'        => 3,
        'show_summary' => 1,
        'show_author'  => 0,
        'show_date'    => 1
    ) );
    echo '</div>';
}

function alienship_custom_dashboard_widgets() {

    wp_add_dashboard_widget( 'dashboard_custom_feed', 'Alien Ship News', 'alienship_rss_dashboard_widget' );
}
add_action('wp_dashboard_setup', 'alienship_custom_dashboard_widgets');

/**
 * Creates the title based on current view
 * @since 1.0
 */
function alienship_wp_title( $title, $sep ) {

    global $paged, $page;

    if ( is_feed() )
        return $title;

    // Add the site name.
    $title .= get_bloginfo( 'name', 'display' );

    // Add the site description for the home/front page.
    $site_description = get_bloginfo( 'description', 'display' );

    if ( $site_description && ( is_home() || is_front_page() ) )
        $title = "$title $sep $site_description";

    // Add a page number if necessary.
    if ( $paged >= 2 || $page >= 2 )
        $title = "$title $sep " . sprintf( __( 'Page %s', 'alienship' ), max( $paged, $page ) );

    return $title;
}
add_filter( 'wp_title', 'alienship_wp_title', 10, 2 );

/**
 * Define theme layouts
 * @since 1.0
 */
function alienship_layouts_strings() {

    $strings = array(
        'default'           => __( 'Default', 'alienship' ),
        '2c-l'              => __( 'Content left. Sidebar right.', 'alienship' ),
        '2c-r'              => __( 'Content right. Sidebar left.', 'alienship' ),
        '1c'                => __( 'Full width. No sidebar.', 'alienship' ),
    );
    return $strings;
}
add_filter( 'theme_layouts_strings', 'alienship_layouts_strings' );

/**
 * Apply custom stylesheet to the visual editor.
 *
 * @since 1.0
 * @uses add_editor_style()
 * @uses get_stylesheet_uri()
 */
function waboot_editor_styles() {
    add_editor_style( get_stylesheet_uri() );
    add_editor_style( 'css/bootstrap.min.css' );
    add_editor_style( 'admin/css/tinymce.css' ); //Overwrite some bootstrap stylesheet
}
add_action( 'init', 'waboot_editor_styles' );

/**
 * Apply "post-type relative" custom stylesheet to visual editor
 * @since 1.0
 * @uses add_editor_style()
 * @uses get_post_type()
 */
function waboot_post_type_editot_styles(){
    global $post;
    $post_type = get_post_type( $post->ID );
    $editor_style = 'tinymce-' . $post_type . '.css';
    add_editor_style( "admin/css/".$editor_style );
}
add_action( 'pre_get_posts', 'waboot_post_type_editot_styles' );