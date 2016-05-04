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
	
	$display_title = (bool) \Waboot\functions\get_option("blogpage_display_title") && \Waboot\functions\get_option("blogpage_title_position") == "bottom"; //todo: add this
	$page_title = get_archive_page_title();
	$term_description = term_description();
	$blog_style = get_blog_layout();
	$blog_class = get_blog_class();
	$display_nav_above = (bool) \Waboot\functions\get_option('content_nav_above', 1); //todo: add this
	$display_nav_below =  (bool) \Waboot\functions\get_option('content_nav_below', 1); //todo: add this

	$vars = compact($display_title,$page_title,$term_description,$blog_style,$blog_class,$display_nav_above,$display_nav_below);
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