<?php

namespace Waboot\hooks\widget_areas;

/**
 * Register widget areas
 */
function register_widget_areas(){
	$areas = \Waboot\functions\get_widget_areas();

	foreach($areas as $area_id => $area_args){
		$args = [
			'name' => $area_args['name'],
			'description' => isset($area_args['description']) ? $area_args['description'] : "",
			'id' => $area_id,
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<h2 class="rounded">',
			'after_title' => '</h2>',
		];
		register_sidebar($args);

		//Add Widget Area to zone
		if(isset($area_args['render_zone'])){
			$priority = isset($area_args['render_priority']) ? intval($area_args['render_priority']) : 50;
			if(is_active_sidebar($area_id)){
				Waboot()->layout->add_zone_action($area_args['render_zone'],function() use($area_id){
					dynamic_sidebar($area_id);
				},$priority);
			}
		}
	}

	/*$widgets = [
		'Waboot\inc\widgets\Social' => 'inc/widgets/Socials.php',
		'Waboot\inc\widgets\RecentPosts' => 'inc/widgets/RecentPosts.php'
	];*/

	/*foreach ($widgets as $name => $file) {
		if ($filepath = locate_template($file)) {
			require_once $filepath;
			if($name == 'Waboot\inc\widgets\RecentPosts' && !function_exists("wbf_get_posts")) continue;
			register_widget( $name );
		}
	}*/
}
add_action("widgets_init",__NAMESPACE__."\\register_widget_areas");