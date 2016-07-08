<?php

namespace Waboot\hooks\layout;
use Waboot\Layout;
use WBF\modules\behaviors\Behavior;

/**
 * Prepare the classes for mainwrap container
 * 
 * @param $classes
 * @return string
 */
function set_main_wrapper_container_classes($classes) {
	$body_layout = \Waboot\functions\get_body_layout();
	$cols_size = \Waboot\functions\get_cols_sizes();
	$classes_array = explode(" ", $classes);

	if($body_layout){
		if ($body_layout == "full-width") {
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
add_filter("waboot/layout/main_wrapper/classes", __NAMESPACE__."\\set_main_wrapper_container_classes");

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