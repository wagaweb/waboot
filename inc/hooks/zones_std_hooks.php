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
			$tpl_part = ["templates/wordpress/content","blog"];
			break;
		case Utilities::PAGE_TYPE_STATIC_HOME:
			$tpl_part = ["templates/wordpress/content","page"];
			break;
		case Utilities::PAGE_TYPE_BLOG_PAGE:
			$tpl_part = ["templates/wordpress/content","blog"];
			break;
		case Utilities::PAGE_TYPE_COMMON:
			if($wp_query->is_single()){
				$tpl_part = ["templates/wordpress/content","single"];
			}elseif($wp_query->is_page()){
				$tpl_part = ["templates/wordpress/content","page"];
			}elseif($wp_query->is_author()){
				$tpl_part = ["templates/wordpress/content","author"];
			}elseif($wp_query->is_search()){
				$tpl_part = ["templates/wordpress/content","search"];
			}elseif($wp_query->is_archive()){
				$tpl_part = ["templates/wordpress/content","archive"];
			}elseif($wp_query->is_404()){
				$tpl_part = ["templates/wordpress/content","404"];
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