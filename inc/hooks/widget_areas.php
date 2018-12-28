<?php

namespace Waboot\hooks\widget_areas;
use WBF\components\mvc\HTMLView;

/**
 * Guess what?
 *
 * @param bool $is_active Whether a sidebar is in use.
 * @param string|int $index Sidebar name, id or number to check.
 *
 * @return bool true if the sidebar is in use, false otherwise.
 */
function check_if_multiple_widget_area_is_active($is_active,$index){
	$areas = \Waboot\functions\get_widget_areas();
	if(!isset($areas[$index]) || !isset($areas[$index]['type']) || $areas[$index]['type'] != "multiple") return $is_active;
	if(!isset($areas[$index]['subareas']) && intval($areas[$index]['subareas']) <= 0) return $is_active;
	for($i = 1; $i<=intval($areas[$index]['subareas']); $i++){
		if(is_active_sidebar($index."-".$i)){
			return true;
		}
	}
	return $is_active;
}
add_filter("is_active_sidebar",__NAMESPACE__."\\check_if_multiple_widget_area_is_active",10,2);

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
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
		];
		$args = wp_parse_args($area_args, $args);
		if(isset($area_args['type']) && $area_args['type'] == "multiple" && isset($area_args['subareas']) && intval($area_args['subareas']) > 0){
			for($i = 1; $i<=intval($area_args['subareas']); $i++){
				$my_args = $args;
				$my_args['id'] = $args['id']."-".$i;
				if(preg_match("/\{\{ ?n ?\}\}/",$args['name'])){ //You can use {{ n }} in the widget area name to specify where to put the number in the name
					$my_args['name'] = preg_replace("/\{\{ ?n ?\}\}/",$i,$args['name']);
				}else{
					$my_args['name'] = $args['name']." ".$i;
				}
				register_sidebar($my_args);
			}
		}else{
			register_sidebar($args);
		}
	}

	$widgets = [
		'Waboot\inc\widgets\RecentPosts' => 'inc/widgets/RecentPosts.php'
	];

	foreach ($widgets as $name => $file) {
		if($name == 'Waboot\inc\widgets\RecentPosts' && !function_exists("wbf_get_posts")) continue;
		if ($filepath = locate_template($file)) {
			require_once $filepath;
			register_widget( $name );
		}
	}
}
add_action("widgets_init",__NAMESPACE__."\\register_widget_areas", 12);

/**
 * Add an action to each widget-area render zone to display the widgets in the area itself in that render zone
 */
function add_widget_areas_to_zones(){
	$areas = \Waboot\functions\get_widget_areas();
	foreach($areas as $area_id => $area_args){
		//Add Widget Area to zone
		if(!isset($area_args['render_zone'])){
			continue;
		}
		$priority = isset($area_args['render_priority']) ? intval($area_args['render_priority']) : 50;
		if(!is_active_sidebar($area_id)){
			continue;
		}
		//Adds an action to the "render_zone" to display the widget area.
		try{
			$display_sidebar = function() use($area_id,$area_args){
				if(isset($area_args['type']) && $area_args['type'] == "multiple"){
					\Waboot\functions\print_widgets_in_area($area_id);
				}else{
					$tpl = "templates/widget_areas/standard.php"; //standard widget area tpl

					//Search for specific widget areas templates
					$search_in = [
						get_stylesheet_directory(),
						get_template_directory()
					];
					$search_in = array_unique($search_in);
					foreach($search_in as $dirname){
						$filename = $dirname."/templates/widget_areas/".$area_id.".php";
						if(file_exists($filename)){
							$tpl = "templates/widget_areas/".$area_id.".php";
							break;
						}
					}

					$v = new HTMLView($tpl);

					$v->clean()->display([
						'area_id' => $area_id
					]);
				}
			};
			WabootLayout()->add_zone_action($area_args['render_zone'],$display_sidebar,$priority);
		}catch(\Exception $e){
			trigger_error($e->getMessage());
		}
	}
}
add_action("wp",__NAMESPACE__."\\add_widget_areas_to_zones");