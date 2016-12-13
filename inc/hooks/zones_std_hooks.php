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
			$tpl_part = ["templates/wordpress/blog",null];
			break;
		case Utilities::PAGE_TYPE_STATIC_HOME:
			$tpl_part = ["templates/wordpress/page",null];
			break;
		case Utilities::PAGE_TYPE_BLOG_PAGE:
			$tpl_part = ["templates/wordpress/blog",null];
			break;
		case Utilities::PAGE_TYPE_COMMON:
			if(is_attachment() && wp_attachment_is_image()){
				$tpl_part = ["templates/wordpress/image",null]; //Note: this is a special case ported from Waboot 0.x
			}
			elseif($wp_query->is_single()){
				$tpl_part = ["templates/wordpress/single",null];
			}elseif($wp_query->is_page()){
				$tpl_part = ["templates/wordpress/page",null];
			}elseif($wp_query->is_author()){
				$tpl_part = ["templates/wordpress/author",null];
			}elseif($wp_query->is_search()){
				$tpl_part = ["templates/wordpress/search",null];
			}elseif($wp_query->is_archive()){
				$tpl_part = ["templates/wordpress/archive",null];
			}elseif($wp_query->is_404()){
				$tpl_part = ["templates/wordpress/404",null];
			}else{
				throw new \Exception("Unrecognized content type");
			}
			break;
		default:
			throw new \Exception("Unrecognized page type");
			break;
	}

	//Actually includes the template, making filterable.
	if(isset($tpl_part)){
		$tpl_part = apply_filters("waboot/layout/content/template",$tpl_part,$page_type);
		get_template_part($tpl_part[0],$tpl_part[1]);
	}
}
\Waboot()->layout->add_zone_action("content",__NAMESPACE__."\\add_main_content");