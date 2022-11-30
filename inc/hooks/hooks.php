<?php

namespace Waboot\inc\hooks;

use Waboot\inc\core\utils\Utilities;

/**
 * Remove user list endpoint from rest api
 * @see: https://hackertarget.com/wordpress-user-enumeration/
 */
add_filter('rest_endpoints', function($endpoints){
    if(isset($endpoints['/wp/v2/users'])){
        unset($endpoints['/wp/v2/users']);
    }
    if(isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])){
        unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $endpoints;
});

/**
 * Only Allow logged-in rest access to users
 * @see: https://hackertarget.com/wordpress-user-enumeration/
 */
/*add_action('rest_authentication_errors', function(){
    if((strpos($_SERVER['REQUEST_URI'], "users") !== false) || ( isset($_REQUEST['rest_route']) && (strpos($_REQUEST['rest_route'], "users") !== false) )){
        if(!is_user_logged_in()){
            return new \WP_Error(
                'rest_cannot_access',
                esc_html__('Only authenticated users can access the User endpoint REST API.', LANG_TEXTDOMAIN),
                array('status' => rest_authorization_required_code())
            );
        }
    }
});*/

/**
 * Fix Login Error Check
 */
add_filter('login_errors', function($message) {
    global $errors;
    if (isset($errors->errors['invalid_username']) || isset($errors->errors['incorrect_password'])) {
        $message = __('<strong>ERROR</strong>: Invalid username/password combination.', LANG_TEXTDOMAIN) . ' ' .
            sprintf(('<a href="%1$s" title="%2$s">%3$s</a>?'),
                site_url('wp-login.php?action=lostpassword', 'login'),
                __('Password Lost and Found', 'default'),
                __('Lost Password', 'default'));
    }
    return $message;
});

/**
 * Disable Feed
 */
function disableFeeds() {
    wp_die( __( 'No feed available, please visit the homepage!' ) );
}
add_action('do_feed', __NAMESPACE__."\\disableFeeds", 1);
add_action('do_feed_rdf', __NAMESPACE__."\\disableFeeds", 1);
add_action('do_feed_rss', __NAMESPACE__."\\disableFeeds", 1);
add_action('do_feed_rss2', __NAMESPACE__."\\disableFeeds", 1);
add_action('do_feed_atom', __NAMESPACE__."\\disableFeeds", 1);
add_action('do_feed_rss2_comments', __NAMESPACE__."\\disableFeeds", 1);
add_action('do_feed_atom_comments', __NAMESPACE__."\\disableFeeds", 1);
remove_action( 'wp_head', 'feed_links_extra', 3 );
remove_action( 'wp_head', 'feed_links', 2 );

/**
 * Add header metas
 */
function addHeaderMetas(){
    get_template_part('templates/parts/meta');
}
add_action('waboot/head/start',__NAMESPACE__."\\addHeaderMetas");

/**
 * Ignore sticky posts in archives
 * @param \WP_Query $query
 */
function ignoreStickyPostInArchives($query){
    if(is_category() || is_tag() || is_tax()) {
        $query->set('post__not_in',get_option( 'sticky_posts', array() ));
    }
}
add_action('pre_get_posts', __NAMESPACE__.'\\ignoreStickyPostInArchives');

/**
 * Eneble Additional File Types to be Uploaded
 */
add_filter('upload_mimes', function ($mime_types){
    $mime_types['svg'] = 'image/svg+xml'; //Adding svg extension
    $mime_types['zip'] = 'application/zip'; //Adding zip files
    $mimes_types['gz'] = 'application/x-gzip';
    return $mime_types;
}, 99, 1);

add_filter( 'wp_check_filetype_and_ext', function ( $types, $file, $filename, $mimes ) {
    // Do basic extension validation and MIME mapping
    $wp_filetype = wp_check_filetype( $filename, $mimes );
    $ext         = $wp_filetype['ext'];
    $type        = $wp_filetype['type'];
    if( in_array( $ext, array( 'zip', 'gz' ) ) ) { // it allows zip files
        $types['ext'] = $ext;
        $types['type'] = $type;
    }
    return $types;
}, 99, 4 );

/**
 * Eneble Custom CSS Permission
 */
add_filter( 'map_meta_cap', function( $caps, $cap ) {
    if ( 'edit_css' === $cap && is_multisite() ) {
        $caps = array( 'edit_theme_options' );
    }
    return $caps;
}, 20, 2 );

/**
 * Eneble HTML Block Permission
 */
add_filter( 'map_meta_cap', function( $caps, $cap ) {
    if ( 'unfiltered_html' === $cap && is_multisite() ) {
        $caps = array( 'edit_theme_options' );
    }
    return $caps;
}, 20, 2 );
