<?php

if(!function_exists("waboot_insert_breadcrumb")):
	/**
	 * Display breadcrumb
	 */
	function waboot_insert_breadcrumb(){
		if (function_exists('is_woocommerce') && is_woocommerce()) { //@woocommerce hard-coded integration
			woocommerce_breadcrumb([
				'wrap_before'   => '<div class="breadcrumb-trail breadcrumbs" itemprop="breadcrumb"><div class="container">',
				'wrap_after'   => '</div></div>',
				'delimiter'  => '<span class="sep">&nbsp;&#47;&nbsp;</span>'
			]);
		}else {
			waboot_breadcrumb(null, 'before_inner', ['wrapper_start' => '<div class="container">', 'wrapper_end' => '</div>']);
		}
	}
	add_action("waboot_before_inner","waboot_insert_breadcrumb",5);
endif;	

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
 * Use of_get_option('primary-sidebar-size') for sidebar size in archive pages
 *
 * @param \WBF\modules\behaviors\Behavior $b
 *
 * @return \WBF\modules\behaviors\Behavior
 */
function waboot_set_primary_sidebar_size(\WBF\modules\behaviors\Behavior $b){
	if(is_archive()){
		$primary_sidebar_width = of_get_option("blog_primary_sidebar_size");
		if(!$primary_sidebar_width) $primary_sidebar_width = 0;
		$b->value = $primary_sidebar_width;
	}
	return $b;
}
add_filter("wbf/modules/behaviors/get/primary-sidebar-size","waboot_set_primary_sidebar_size");

/**
 * Use of_get_option('secondary-sidebar-size') for sidebar size in archive pages
 *
 * @param \WBF\modules\behaviors\Behavior $b
 *
 * @return \WBF\modules\behaviors\Behavior
 */
function waboot_set_secondary_sidebar_size(\WBF\modules\behaviors\Behavior $b){
	if(is_archive()){
		$primary_sidebar_width = of_get_option("blog_secondary_sidebar_size");
		if(!$primary_sidebar_width) $primary_sidebar_width = 0;
		$b->value = $primary_sidebar_width;
	}
	return $b;
}
add_filter("wbf/modules/behaviors/get/secondary-sidebar-size","waboot_set_secondary_sidebar_size");

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
		$size = wbft_current_page_type() == "blog_page" || wbft_current_page_type() == "default_home" ? of_get_option("blog_primary_sidebar_size") : get_behavior('primary-sidebar-size');
		return $size;
	}elseif($name == "secondary"){
		$size = wbft_current_page_type() == "blog_page" || wbft_current_page_type() == "default_home" ? of_get_option("blog_secondary_sidebar_size") : get_behavior('secondary-sidebar-size');
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

_adds_plugins_wrappers();

/**
 * Automatically adds actions for WBF plugins content wrappers.
 */
function _adds_plugins_wrappers(){
	$plugins = WBF()->get_registered_plugins();
	foreach ($plugins as $name => $params){
		remove_all_actions("{$name}/before_main_content", 10);
		add_action("{$name}/before_main_content",function(){
			get_template_part("templates/wrapper","start");
		});
		remove_all_actions("{$name}/after_main_content", 10);
		add_action("{$name}/after_main_content",function(){
			get_template_part("templates/wrapper","end");
		});
	}
}