<?php

namespace Waboot\hooks\entry;
use WBF\includes\mvc\HTMLView;

/**
 * Display title in entry header
 *
 * @param \WP_Post $post
 */
function display_title(\WP_Post $post = null){
	if(!$post) global $post;
	
	$can_display_title = $post instanceof \WP_Post && get_behavior("show-title") == "1" && get_behavior('title-position') == "bottom";
	if(!$can_display_title) return;
	
	if(is_singular()){
		$tpl = "templates/view-parts/entry-title-singular.php";
	}else{
		$tpl = "templates/view-parts/entry-title.php";
	}

	(new HTMLView($tpl))->clean()->display([
		'title' => get_the_title($post->ID)
	]);
}
add_action("waboot/entry/header",__NAMESPACE__."\\display_title");
