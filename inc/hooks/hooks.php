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
                esc_html__('Only authenticated users can access the User endpoint REST API.', 'waboot'),
                array('status' => rest_authorization_required_code())
            );
        }
    }
});*/

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