<?php

namespace Waboot\functions;
use WBF\includes\mvc\HTMLView;

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
 * Renders page.php content
 */
function render_page(){
	
}

/**
 * Renders single.php content
 */
function render_single(){
	
}