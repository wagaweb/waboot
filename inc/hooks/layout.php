<?php

namespace Waboot\hooks\layout;
use function Waboot\functions\get_archive_option;
use Waboot\Layout;
use WBF\components\utils\Query;
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
				if(!WabootLayout()->can_render_zone("aside-primary")){
					$layout = Layout::LAYOUT_FULL_WIDTH;
				}
				break;
			case Layout::LAYOUT_TWO_SIDEBARS:
				//If we have two sidebar and the primary is empty, go full width
				if(!WabootLayout()->can_render_zone("aside-primary")){
					$layout = Layout::LAYOUT_FULL_WIDTH;
				}elseif(!WabootLayout()->can_render_zone("aside-secondary")){
					//If we have two sidebar and the secondary is empty, go primary right
					$layout = Layout::LAYOUT_PRIMARY_RIGHT;
				}
				break;
			case Layout::LAYOUT_TWO_SIDEBARS_LEFT:
				//If we have two sidebar and the primary is empty, go full width
				if(!WabootLayout()->can_render_zone("aside-primary")){
					$layout = Layout::LAYOUT_FULL_WIDTH;
				}elseif(!WabootLayout()->can_render_zone("aside-secondary")){
					//If we have two sidebar to the left and the secondary is empty, go primary left
					$layout = Layout::LAYOUT_PRIMARY_LEFT;
				}
				break;
			case Layout::LAYOUT_TWO_SIDEBARS_RIGHT:
				//If we have two sidebar and the primary is empty, go full width
				if(!WabootLayout()->can_render_zone("aside-primary")){
					$layout = Layout::LAYOUT_FULL_WIDTH;
				}elseif(!WabootLayout()->can_render_zone("aside-secondary")){
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
 * Adds body layout to WP standard body class
 *
 * @param $classes
 *
 * @return array
 */
function alter_body_class($classes){
	$classes[] = \Waboot\functions\get_body_layout();
	return $classes;
}
add_filter('body_class', __NAMESPACE__.'\\alter_body_class');

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
			Layout::remove_cols_classes($classes_array); //Remove all wbcol classes
			$classes_array[] = WabootLayout()->get_col_grid_class()."12";
		} else {
			Layout::remove_cols_classes($classes_array); //Remove all wbcol classes
			$classes_array[] = WabootLayout()->get_col_grid_class().$cols_size['main'];
			//Three cols with main in the middle? Then add pull and push
			//if($body_layout == "two-sidebars"){
				//$classes_array[] = "col-sm-push-".$cols_size['primary'];
			//}
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
		Layout::remove_cols_classes($classes_array); //Remove all wbcol classes
		$classes_array[] = WabootLayout()->get_col_grid_class().Layout::layout_width_to_int($size);
		//Three cols with main in the middle? Then add pull and push
		//if(\Waboot\functions\get_body_layout() == "two-sidebars"){
			//$cols_size = \Waboot\functions\get_cols_sizes();
			//$classes_array[] = "col-sm-pull-".$cols_size['main'];
		//}
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
		Layout::remove_cols_classes($classes_array); //Remove all wbcol classes
		$classes_array[] = WabootLayout()->get_col_grid_class().Layout::layout_width_to_int($size);
	}

	$classes = implode(" ",$classes_array);
	return $classes;
}
add_filter("waboot/layout/sidebar/secondary/classes", __NAMESPACE__."\\set_secondary_sidebar_container_classes");

/**
 * Use \Waboot\functions\get_option('primary-sidebar-size') for sidebar size in archive pages
 *
 * @param \WBF\modules\behaviors\Behavior $b
 *
 * @return \WBF\modules\behaviors\Behavior
 */
function set_primary_sidebar_size(Behavior $b){
	if(is_archive()){
		if(is_category()){
			$sidebar_width = \Waboot\functions\get_option("blog_primary_sidebar_size");
		}else{
			$sidebar_width = get_archive_option('primary_sidebar_size');
		}
		if(!isset($sidebar_width) || !$sidebar_width || is_null($sidebar_width)){
			$sidebar_width = 0;
		}
		$b->value = $sidebar_width;
	}
	return $b;
}
//Removed on 07-25-2017: Archives can't have behaviors
//add_filter("wbf/modules/behaviors/get/primary-sidebar-size", __NAMESPACE__."\\set_primary_sidebar_size");

/**
 * Use \Waboot\functions\get_option('secondary-sidebar-size') for sidebar size in archive pages
 *
 * @param \WBF\modules\behaviors\Behavior $b
 *
 * @return \WBF\modules\behaviors\Behavior
 */
function set_secondary_sidebar_size(Behavior $b){
	if(is_archive()){
		if(is_category()){
			$sidebar_width = \Waboot\functions\get_option("blog_secondary_sidebar_size");
		}else{
			$sidebar_width = get_archive_option('secondary_sidebar_size');
		}
		if(!isset($sidebar_width) || !$sidebar_width || is_null($sidebar_width)){
			$sidebar_width = 0;
		}
		$b->value = $sidebar_width;
	}
	return $b;
}
//Removed on 07-25-2017: Archives can't have behaviors
//add_filter("wbf/modules/behaviors/get/secondary-sidebar-size", __NAMESPACE__."\\set_secondary_sidebar_size");

/**
 * Alter the default comments template location
 *
 * @param $theme_template
 *
 * @return string
 */
function alter_comments_template($theme_template){
	$theme_template = locate_template("templates/comments.php");
	return $theme_template;
}
add_filter('comments_template', __NAMESPACE__."\\alter_comments_template");


/**
 * Style comment reply links as buttons
 * @since 0.1.0
 *
 * @param string $link
 *
 * @return string
 */
function comment_reply_link_classes( $link ) {
	return str_replace( 'comment-reply-link', 'btn', $link );
}
add_filter( 'comment_reply_link', __NAMESPACE__."\\comment_reply_link_classes" );

/**
 * Style the excerpt continuation
 *
 * @param string $more
 *
 * @return string
 */
function alter_excerpt_more( $more ) {
	return ' ... <a href="'. get_permalink( get_the_ID() ) . '">'. __( 'Continue reading', 'waboot' ) .' &raquo;</a>';
}
add_filter('excerpt_more',  __NAMESPACE__."\\alter_excerpt_more" );

/**
 * Set post name as Body Class
 */
function add_slug_body_class( $classes ) {
    global $post;
    if ( isset( $post ) ) {
        $classes[] = $post->post_type . '-' . $post->post_name;
    }
    return $classes;
}
add_filter( "body_class",__NAMESPACE__."\\add_slug_body_class" );

_adds_plugins_wrappers();

/**
 * Automatically adds actions for WBF plugins content wrappers.
 */
function _adds_plugins_wrappers(){
	$plugins = WBF()->get_registered_plugins();
	foreach ($plugins as $name => $params){
		add_action("{$name}/before_main_content",function(){
			\get_template_part("templates/wrapper","start");
		});
		add_action("{$name}/after_main_content",function(){
			\get_template_part("templates/wrapper","end");
		});
	}
}