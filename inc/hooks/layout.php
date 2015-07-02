<?php

if ( ! function_exists( 'waboot_set_mainwrap_container_classes' ) ):
	/**
	 * Prepare the classes for mainwrap container
	 * @param $classes
	 * @return string
	 */
	function waboot_set_mainwrap_container_classes($classes) {
		$body_layout = waboot_get_body_layout();
		$cols_size = _get_cols_sizes();
		$classes_array = explode(" ", $classes);

		if($body_layout){
			if ($body_layout == "full-width") {
				_remove_cols_classes($classes_array); //Remove all col- classes
				$classes_array[] = "col-sm-12";
			} else {
				_remove_cols_classes($classes_array); //Remove all col- classes
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
	add_filter("waboot_mainwrap_container_class","waboot_set_mainwrap_container_classes");
endif;

if ( ! function_exists( 'waboot_set_primary_container_classes' ) ):
	/**
	 * Prepare the classes for primary container (the primary sidebar)
	 * @param $classes
	 * @return string
	 */
	function waboot_set_primary_container_classes($classes){
		$classes_array = explode(" ",$classes);

		$size = _get_sidebar_size("primary");

		if($size){
			_remove_cols_classes($classes_array); //Remove all col- classes
			$classes_array[] = "col-sm-"._layout_width_to_int($size);
			//Three cols with main in the middle? Then add pull and push
			if(waboot_get_body_layout() == "two-sidebars"){
				$cols_size = _get_cols_sizes();
				$classes_array[] = "col-sm-pull-".$cols_size['main'];
			}
		}

		$classes = implode(" ",$classes_array);
		return $classes;
	}
	add_filter("waboot_primary_container_class","waboot_set_primary_container_classes");
endif;

if ( ! function_exists( 'waboot_set_secondary_container_classes' ) ):
	/**
	 * Prepare the classes for secondary container (the secondary sidebar)
	 * @param $classes
	 * @return string
	 */
	function waboot_set_secondary_container_classes($classes){
		$classes_array = explode(" ",$classes);

		$size = _get_sidebar_size("secondary");

		if($size){
			_remove_cols_classes($classes_array); //Remove all col- classes
			$classes_array[] = "col-sm-"._layout_width_to_int($size);
		}

		$classes = implode(" ",$classes_array);
		return $classes;
	}
	add_filter("waboot_secondary_container_class","waboot_set_secondary_container_classes");
endif;

/**
 * Returns the sizes of each column available into current layout
 * @return array of integers
 */
function _get_cols_sizes(){
	$result = array("main"=>12);
	if (waboot_body_layout_has_two_sidebars()) {
		//Primary size
		$primary_sidebar_width = _get_sidebar_size("primary");
		if(!$primary_sidebar_width) $primary_sidebar_width = 0;
		//Secondary size
		$secondary_sidebar_width = _get_sidebar_size("secondary");
		if(!$secondary_sidebar_width) $secondary_sidebar_width = 0;
		//Main size
		$mainwrap_size = 12 - _layout_width_to_int($primary_sidebar_width) - _layout_width_to_int($secondary_sidebar_width);

		$result = array("main"=>$mainwrap_size,"primary"=>_layout_width_to_int($primary_sidebar_width),"secondary"=>_layout_width_to_int($secondary_sidebar_width));
	}else{
		if(waboot_get_body_layout() != "full-width"){
			$primary_sidebar_width = _get_sidebar_size("primary");
			if(!$primary_sidebar_width) $primary_sidebar_width = 0;
			$mainwrap_size = 12 - _layout_width_to_int($primary_sidebar_width);

			$result = array("main"=>$mainwrap_size,"primary"=>_layout_width_to_int($primary_sidebar_width));
		}
	}
	$result = apply_filters("waboot/layout/get_cols_sizes",$result);
	return $result;
}

/**
 * Get the specified sidebar size
 * @param $name ("primary" or "secondary")
 *
 * @return bool
 */
function _get_sidebar_size($name){
	if($name == "primary"){
		$size = wbft_current_page_type() != "blog_page" ? get_behavior('primary-sidebar-size') : \WBF\modules\options\of_get_option("blog_primary_sidebar_size");
		return $size;
	}elseif($name == "secondary"){
		$size = wbft_current_page_type() != "blog_page" ? get_behavior('secondary-sidebar-size') : \WBF\modules\options\of_get_option("blog_secondary_sidebar_size");
		return $size;
	}
	return false;
}

if(!function_exists("waboot_layout_body_class")) :
	function waboot_layout_body_class($classes){
		$classes[] = waboot_get_body_layout();
		return $classes;
	}
	add_filter('body_class','waboot_layout_body_class');
endif;

/**
 * Removes "col-" string values from an array
 * @param array $classes_array
 */
function _remove_cols_classes(array &$classes_array){
	foreach($classes_array as $k => $v){
		if(preg_match("/col-/",$v)){
			unset($classes_array[$k]);
		}
	}
}

/**
 * Convert size labels (1/3, 2/3, ect) into size integers (for using into col-sm-<x>)
 * @param string $width the label
 *
 * @return int
 */
function _layout_width_to_int($width){
	switch($width){
		case '0':
			return 0;
			break;
		case '1/2':
			return 6;
			break;
		case '1/3':
			return 4;
			break;
		case '1/4':
			return 3;
			break;
		case '1/6':
			return 2;
			break;
		default:
			return 4;
			break;
	}
}