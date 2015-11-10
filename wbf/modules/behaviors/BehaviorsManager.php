<?php

namespace WBF\modules\behaviors;

class BehaviorsManager{

	static function get($name, $post_id) {
		static $behaviors;
		static $already_obtained_behaviors;

		if(isset($already_obtained_behaviors[$name][$post_id])){
			return $already_obtained_behaviors[$name][$post_id];
		}

		$current_post_type = $post_id != 0 ? get_post_type($post_id) : "page"; //"0" is received when in archives pages, so set the post type to "Pages"
		if(is_null($behaviors)) $behaviors = self::getAll($current_post_type); //retrive all behaviours
		$selected_behavior = new \stdClass();

		foreach ($behaviors as $b) { //find the desidered behaviour
			if ($b->name == $name) {
				$selected_behavior = $b;
			}
		}

		if ($selected_behavior instanceof Behavior) {
			$current_behavior_value = $selected_behavior->get_value($post_id);
			$already_obtained_behaviors[$name][$post_id] = $selected_behavior;
			return $selected_behavior;
		} else {
			return false;
		}
	}

	static function getAll($post_type = null){
		if(is_null($post_type)) $post_type = "*";
		static $behaviors;

		if(is_null($behaviors) || !isset($behaviors[$post_type])){
			$imported_behaviors = self::importPredefined(); //per ora si possono specificare solo via file...
			$behaviors = array();
			foreach($imported_behaviors as $b){
				if(isset($post_type) && $post_type != "*") $b['get_for_posttype'] = $post_type;
				$behaviors[$post_type][] = new Behavior($b);
			}
		}

		if(isset($behaviors[$post_type])){
			return $behaviors[$post_type];
		}
		return [];
	}

	static function count_behaviors_for_node_id($id){
		static $behaviors;
		if(is_null($behaviors)) $behaviors = self::getAll();
		$count = 0;
		foreach($behaviors as $b){
			if($b->is_enable_for_node($id)){
				$count++;
			}
		}

		return $count;
	}

	static function count_behaviors_for_post_type($slug){
		static $behaviors;
		if(is_null($behaviors)) $behaviors = self::getAll();
		$count = 0;
		foreach($behaviors as $b){
			if($b->is_enabled_for_post_type($slug)){
				$count++;
			}
		}

		return $count;
	}

	static function importPredefined(){
		$predef_behaviors = array();

		//Get behaviors from .json files
		$behavior_file = get_theme_root()."/".get_template()."/inc/behaviors.json";
		if (file_exists($behavior_file)) {
			$predef_behaviors = json_decode(file_get_contents($behavior_file, true),true);
		}

		if(is_child_theme()){
			$child_behavior_file = get_stylesheet_directory()."/inc/behaviors.json";
			if(file_exists($child_behavior_file)){
				$child_behaviors = json_decode(file_get_contents($child_behavior_file, true),true);
				$predef_behaviors = array_replace_recursive($predef_behaviors,$child_behaviors);
			}
		}

		//Get from filters
		$predef_behaviors = apply_filters("wbf_add_behaviors",$predef_behaviors);

		return $predef_behaviors;
	}

	static function debug($post_id){
		$behaviors = self::getAll();
		echo "<div style='border: 1px solid #ccc;'><pre>";
		foreach($behaviors as $b){
			echo $b->name.": ";
			var_dump($b->get_value($post_id));
		}
		echo "</div></pre>";
	}
}