<?php

namespace Waboot\hooks;

use WBF\components\mvc\HTMLView;
use WBF\components\utils\Utilities;

/**
 * Renders main content into the "main" zone.
 */
function add_main_content(){
	/**
	 * @var \WP_Query
	 */
	global $wp_query;

	$page_type = Utilities::get_current_page_type();

	switch($page_type){
		case Utilities::PAGE_TYPE_DEFAULT_HOME:
			get_template_part("templates/wordpress/blog","content");
			break;
		case Utilities::PAGE_TYPE_STATIC_HOME:
			get_template_part("templates/wordpress/page","content");
			break;
		case Utilities::PAGE_TYPE_BLOG_PAGE:
			get_template_part("templates/wordpress/blog","content");
			break;
		case Utilities::PAGE_TYPE_COMMON:
			if($wp_query->is_single()){
				get_template_part("templates/wordpress/single","content");
			}elseif($wp_query->is_page()){
				get_template_part("templates/wordpress/page","content");
			}elseif($wp_query->is_author()){
				get_template_part("templates/wordpress/author","content");
			}elseif($wp_query->is_search()){
				get_template_part("templates/wordpress/search","content");
			}elseif($wp_query->is_archive()){
				get_template_part("templates/wordpress/archive","content");
			}elseif($wp_query->is_404()){
				get_template_part("templates/wordpress/404","content");
			}else{
				throw new \Exception("Unrecognized content type");
			}
			break;
		default:
			throw new \Exception("Unrecognized page type");
			break;
	}
}
\Waboot()->layout->add_zone_action("content",__NAMESPACE__."\\add_main_content");