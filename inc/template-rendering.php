<?php

namespace Waboot\functions;
use WBF\components\mvc\HTMLView;

/**
 * Renders archive.php content
 *
 * @param $template_file
 */
function render_archives($template_file){
	$args = get_archives_template_vars();
	(new HTMLView($template_file))->clean()->display($args);
}

/**
 * Gets the variables needed to render an archive.php page
 * 
 * @return array
 */
function get_archives_template_vars(){
	$vars = [];

	$vars['display_title'] = (bool) \Waboot\functions\get_option("blogpage_display_title",true) && \Waboot\functions\get_option("blogpage_title_position","bottom") == "bottom"; //todo: add this
	$vars['page_title'] = get_archive_page_title();
	$vars['term_description'] = term_description();
	$vars['blog_style'] = get_blog_layout();
	$vars['blog_class'] = get_blog_class();
	$vars['display_nav_above'] = (bool) \Waboot\functions\get_option('content_nav_above', 1); //todo: add this
	$vars['display_nav_below'] =  (bool) \Waboot\functions\get_option('content_nav_below', 1); //todo: add this

	return $vars;
}

/**
 * Gets the additional variables needed to render an aside.php
 *
 * @param $slug
 *
 * @return array
 */
function get_aside_template_vars($slug){
	$vars = [];
	switch($slug){
		case "aside-primary":
			$vars['classes'] = call_user_func(function(){
				if(has_filter("waboot_primary_container_class")){
					return apply_filters('waboot_primary_container_class', 'col-sm-4'); //backward compatibility
				}else{
					return apply_filters('waboot/layout/sidebar/primary/classes', 'col-sm-4');
				}
			});
			break;
		case "aside-secondary":
			$vars['classes'] = call_user_func(function(){
				if(has_filter("waboot_primary_container_class")){
					return apply_filters('waboot_secondary_container_class', 'col-sm-4'); //backward compatibility
				}else{
					return apply_filters('waboot/layout/sidebar/secondary/classes', 'col-sm-4');
				}
			});
			break;
	}

	$vars['container_classes'] = call_user_func(function(){
		if(has_filter("waboot_sidebar_container_class")){
			return apply_filters('waboot_sidebar_container_class', 'aside-area'); //backward compatibility
		}else{
			return apply_filters('waboot/layout/sidebar/container/classes', 'aside-area');
		}
	});

	return $vars;
}

/**
 * Get additional variables needed to render the main wrapper
 */
function get_main_wrapper_template_vars(){
	$vars['classes'] = apply_filters( 'waboot/layout/main_wrapper/classes', 'main-wrapper content-area' );
	
	return $vars;
}