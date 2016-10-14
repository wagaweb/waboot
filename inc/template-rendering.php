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

	$vars['page_title'] = get_archive_page_title();
	$vars['term_description'] = term_description();
	$vars['blog_class'] = get_posts_wrapper_class();
	$vars['display_nav_above'] = (bool) \Waboot\functions\get_option('content_nav_above', 1); //todo: add this
	$vars['display_nav_below'] =  (bool) \Waboot\functions\get_option('content_nav_below', 1); //todo: add this

	$o = get_queried_object();
	$tpl = "";
	if($o instanceof \WP_Term){
		$tpl = "taxonomy-".$o->taxonomy;
	}elseif($o instanceof \WP_Post_Type){
		$tpl = "archive-".$o->name;
	}
	$vars['tpl'] = $tpl;

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
	$classes = apply_filters( 'waboot/layout/main_wrapper/classes', ['main-wrapper']);
	$vars['classes'] = implode(" ",array_unique($classes));

	return $vars;
}

/**
 * Template for comments and pingbacks.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 */
function render_comment($comment, $args, $depth){
	$vars = [
		'additional_comment_class' => empty( $args['has_children'] ) ? '' : 'parent',
		'is_approved' => $comment->comment_approved  != '0',
		'has_avatar' => $args['avatar_size'] != '0',
		'avatar' => get_avatar( $comment, $args['avatar_size'] ),
		'comment' => $comment,
		'args' => $args,
		'depth' => $depth
	];

	$template_file = "templates/view-parts/single-comment.php";
	$v = new HTMLView($template_file);
	$v->display($vars);
}