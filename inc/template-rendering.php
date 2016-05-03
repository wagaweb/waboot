<?php

namespace Waboot\functions;
use WBF\includes\mvc\HTMLView;

/**
 * Renders archive.php content
 *
 * @param $template_file
 */
function render_archives($template_file){
	$args = [];
	(new HTMLView($template_file))->clean()->display($args);
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