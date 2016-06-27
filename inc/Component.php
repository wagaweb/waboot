<?php

namespace Waboot;

class Component extends \WBF\modules\components\Component{

	var $default_zone = "header";
	var $default_prority = 10;

	public function theme_options($options){
		$options = parent::theme_options($options);

		if(!function_exists("Waboot")) return $options;
		$zones = Waboot()->layout->getZones();
		if(empty($zones) || !isset($zones['header'])) return $options;

		$zone_options = call_user_func(function() use($zones){
			$opts = [];
			foreach($zones as $k => $v){
				$opts[$k] = $v['slug'];
			}
			return $opts;
		});

		$options[] = [
			'name' => _x( 'Zone Settings', 'component settings', 'waboot' ),
			'desc' => _x( 'Choose zone settings for this component', 'component_settings', 'waboot' ),
			'type' => 'info'
		];
		$options[] = [
			'name' => _x( 'Position', 'component settings', 'waboot' ),
			'desc' => _x( 'Choose in which zone you want to display', 'component_settings', 'waboot' ),
			'id'   => strtolower($this->name).'_display_zone',
			'std'  => 'header',
			'options' => $zone_options,
			'type' => 'select'
		];
		$options[] = [
			'name' => _x( 'Priority', 'component settings', 'waboot' ),
			'desc' => _x( 'Choose the display priority', 'component_settings', 'waboot' ),
			'id'   => strtolower($this->name).'_display_priority',
			'std'  => '10',
			'type' => 'text'
		];

		return $options;
	}

	public function get_display_zone(){
		$zone = $this->default_zone;
		if(function_exists("\\Waboot\\functions\\get_option")){
			$zone_opt = \Waboot\functions\get_option(strtolower($this->name)."_display_zone");
			if($zone_opt){
				$zone = $zone_opt;
			}
		}
		return $zone;
	}

	public function get_display_priority(){
		$p = $this->default_prority;
		if(function_exists("wb_get_option")){
			$p_opt = \Waboot\functions\get_option(strtolower($this->name)."_display_priority");
			if($p_opt){
				$p = $p_opt;
			}
		}
		return $p;
	}
}