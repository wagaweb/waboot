<?php

namespace Waboot\hooks\layout;
use Waboot\Layout;
use WBF\modules\behaviors\Behavior;

/**
 * Set the theme layout to "full-width" when the aside zone has no elements.
 *
 * @param $layout
 *
 * @return string
 */
function alter_body_layout_when_theme_has_no_sidebars($layout){
	if($layout !== Layout::LAYOUT_FULL_WIDTH){
		switch($layout){
			case Layout::LAYOUT_PRIMARY_LEFT:
			case Layout::LAYOUT_PRIMARY_RIGHT:
				//If we have one sidebar, and its empty, go to full width
				if(!Waboot()->layout->can_render_zone("aside-primary")){
					$layout = Layout::LAYOUT_FULL_WIDTH;
				}
				break;
			case Layout::LAYOUT_TWO_SIDEBARS:
				//If we have two sidebar and the primary is empty, go full width
				if(!Waboot()->layout->can_render_zone("aside-primary")){
					$layout = Layout::LAYOUT_FULL_WIDTH;
				}elseif(!Waboot()->layout->can_render_zone("aside-secondary")){
					//If we have two sidebar and the secondary is empty, go primary right
					$layout = Layout::LAYOUT_PRIMARY_RIGHT;
				}
				break;
			case Layout::LAYOUT_TWO_SIDEBARS_LEFT:
				//If we have two sidebar and the primary is empty, go full width
				if(!Waboot()->layout->can_render_zone("aside-primary")){
					$layout = Layout::LAYOUT_FULL_WIDTH;
				}elseif(!Waboot()->layout->can_render_zone("aside-secondary")){
					//If we have two sidebar to the left and the secondary is empty, go primary left
					$layout = Layout::LAYOUT_PRIMARY_LEFT;
				}
				break;
			case Layout::LAYOUT_TWO_SIDEBARS_RIGHT:
				//If we have two sidebar and the primary is empty, go full width
				if(!Waboot()->layout->can_render_zone("aside-primary")){
					$layout = Layout::LAYOUT_FULL_WIDTH;
				}elseif(!Waboot()->layout->can_render_zone("aside-secondary")){
					//If we have two sidebar to the right and the secondary is empty, go primary right
					$layout = Layout::LAYOUT_PRIMARY_RIGHT;
				}
				break;
		}
	}
	return $layout;
}
add_filter("waboot/layout/body_layout",__NAMESPACE__."\\alter_body_layout_when_theme_has_no_sidebars");

/**
 * Prepare the classes for mainwrap container
 * 
 * @param $classes
 * @return string
 */
function set_main_classes($classes) {
	$body_layout = \Waboot\functions\get_body_layout();
	$cols_size = \Waboot\functions\get_cols_sizes();
	$classes_array = explode(" ", $classes);

	if($body_layout){
		if ($body_layout == Layout::LAYOUT_FULL_WIDTH) {
			Layout::remove_cols_classes($classes_array); //Remove all col- classes
			$classes_array[] = "col-sm-12";
		} else {
			Layout::remove_cols_classes($classes_array); //Remove all col- classes
			$classes_array[] = "col-sm-".$cols_size['main'];
			//Three cols with main in the middle? Then add pull and push
			if($body_layout == "two-sidebars"){
				$classes_array[] = "col-sm-push-".$cols_size['primary'];
			}
		}
	}

	$classes = implode(" ",$classes_array);
	return $classes;
}
add_filter("waboot/layout/main/classes", __NAMESPACE__."\\set_main_classes");

/**
 * Prepare the classes for primary container (the primary sidebar)
 * @param $classes
 * @return string
 */
function set_primary_sidebar_container_classes($classes){
	$classes_array = explode(" ",$classes);

	$size = \Waboot\functions\get_sidebar_size("primary");

	if($size){
		Layout::remove_cols_classes($classes_array); //Remove all col- classes
		$classes_array[] = "col-sm-".Layout::layout_width_to_int($size);
		//Three cols with main in the middle? Then add pull and push
		if(\Waboot\functions\get_body_layout() == "two-sidebars"){
			$cols_size = \Waboot\functions\get_cols_sizes();
			$classes_array[] = "col-sm-pull-".$cols_size['main'];
		}
	}

	$classes = implode(" ",$classes_array);
	return $classes;
}
add_filter("waboot/layout/sidebar/primary/classes", __NAMESPACE__."\\set_primary_sidebar_container_classes");

/**
 * Prepare the classes for secondary container (the secondary sidebar)
 * @param $classes
 * @return string
 */
function set_secondary_sidebar_container_classes($classes){
	$classes_array = explode(" ",$classes);

	$size = \Waboot\functions\get_sidebar_size("secondary");

	if($size){
		Layout::remove_cols_classes($classes_array); //Remove all col- classes
		$classes_array[] = "col-sm-".Layout::layout_width_to_int($size);
	}

	$classes = implode(" ",$classes_array);
	return $classes;
}
add_filter("waboot/layout/sidebar/secondary/classes", __NAMESPACE__."\\set_secondary_sidebar_container_classes");

/**
 * Use of_get_option('primary-sidebar-size') for sidebar size in archive pages
 *
 * @param \WBF\modules\behaviors\Behavior $b
 *
 * @return \WBF\modules\behaviors\Behavior
 */
function set_primary_sidebar_size(Behavior $b){
	if(is_archive()){
		$primary_sidebar_width = of_get_option("blog_primary_sidebar_size");
		if(!$primary_sidebar_width) $primary_sidebar_width = 0;
		$b->value = $primary_sidebar_width;
	}
	return $b;
}
add_filter("wbf/modules/behaviors/get/primary-sidebar-size", __NAMESPACE__."\\set_primary_sidebar_size");

/**
 * Use of_get_option('secondary-sidebar-size') for sidebar size in archive pages
 *
 * @param \WBF\modules\behaviors\Behavior $b
 *
 * @return \WBF\modules\behaviors\Behavior
 */
function set_secondary_sidebar_size(Behavior $b){
	if(is_archive()){
		$primary_sidebar_width = of_get_option("blog_secondary_sidebar_size");
		if(!$primary_sidebar_width) $primary_sidebar_width = 0;
		$b->value = $primary_sidebar_width;
	}
	return $b;
}
add_filter("wbf/modules/behaviors/get/secondary-sidebar-size", __NAMESPACE__."\\set_secondary_sidebar_size");

/**
 * Alter the default comments template location
 *
 * @param $theme_template
 *
 * @return string
 */
function alter_comments_template($theme_template){
	$theme_template = locate_template("templates/wordpress/comments.php");
	return $theme_template;
}
add_filter('comments_template', __NAMESPACE__."\\alter_comments_template");

/**
 * Set the templates wrapper start for all waboot compatible plugins
 */
function include_default_plugins_template_wrapper_start(){
	\get_template_part("templates/wrapper","start");
}
add_action("waboot-plugin/before_main_content",__NAMESPACE__."\\include_default_plugins_template_wrapper_start");

/**
 * Set the templates wrapper end for all waboot compatible plugins
 */
function include_default_plugins_template_wrapper_end(){
	\get_template_part("templates/wrapper","end");
}
add_action("waboot-plugin/after_main_content",__NAMESPACE__."\\include_default_plugins_template_wrapper_end");