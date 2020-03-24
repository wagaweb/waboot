<?php

namespace Waboot\inc\hooks;

use Waboot\inc\core\utils\Utilities;

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