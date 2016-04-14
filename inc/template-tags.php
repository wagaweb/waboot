<?php

namespace Waboot\template_tags;
use WBF\includes\Utilities;

/**
 * Display the breadcrumb for $post_id or global $post->ID
 * @param null $post_id
 * @param string $current_location the current location of breadcrumb. Not used at the moment, but it can be any arbitrary string
 * @param array $args settings for breadcrumb (see: waboot_breadcrumb_trail() documentation)
 *
 * @since 0.3.10
 */
function breadcrumb($post_id = null, $current_location = "", $args = array()) {
	global $post;

	//Get post ID
	if(!isset($post_id)){
		if(isset($post) && isset($post->ID) && $post->ID != 0){
			$post_id = $post->ID;
		}
	}

	if(function_exists('wbf_breadcrumb_trail')) {
		if(is_404()) return;

		$current_page_type = Utilities::get_current_page_type();

		$args = wp_parse_args($args, array(
			'container' => "div",
			'separator' => "/",
			'show_browse' => false,
			'additional_classes' => ""
		));

		$allowed_locations = call_user_func(function(){
			$bc_locations = \Waboot\functions\get_option('breadcrumb_locations',[]);
			$allowed = array();
			foreach($bc_locations as $k => $v){
				if($v == "1"){
					$allowed[] = $k;
				}
			}
			return $allowed;
		});

		if($current_page_type != "common"){
			//We are in some sort of homepage
			if(in_array("homepage", $allowed_locations)) {
				wbf_breadcrumb_trail($args);
			}
		}else{
			//We are NOT in some sort of homepage
			if(!is_archive() && !is_search() && isset($post_id)){
				//We are in a common page
				$current_post_type = get_post_type($post_id);
				if (!isset($post_id) || $post_id == 0 || !$current_post_type) return;
				if(in_array($current_post_type, $allowed_locations)) {
					wbf_breadcrumb_trail($args);
				}
			}else{
				//We are in some sort of archive
				$show_bc = false;
				if(is_tag() && in_array('tag',$allowed_locations)){
					$show_bc = true;
				}elseif(is_tax() && in_array('tax',$allowed_locations)){
					$show_bc = true;
				}elseif(is_archive() && in_array('archive',$allowed_locations)){
					$show_bc = true;
				}
				if($show_bc){
					waboot_breadcrumb_trail($args);
				}
			}
		}
	}
}