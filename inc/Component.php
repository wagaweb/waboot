<?php

namespace Waboot;

class Component extends \WBF\modules\components\Component{
	/**
	 * @var string
	 */
	var $default_zone = "header";
	/**
	 * @var int
	 */
	var $default_priority = 10;
	/**
	 * @var string
	 */
	var $theme_relative_path;
	/**
	 * @var array
	 */
	var $registered_style_assets = [];

	const ZONE_NONE_KEY = '__none';

	public function __construct( array $component ) {
		parent::__construct( $component );
		$this->theme_relative_path = "components/".$this->directory_name;
	}

	public function theme_options($options){
		$options = parent::theme_options($options);

		if(!function_exists("Waboot")) return $options;
		$zones = WabootLayout()->getZones();
		if(empty($zones) || !isset($zones['header'])) return $options;

		$zone_options = call_user_func(function() use($zones){
			//todo: implement this:
			/*$opts = [
				self::ZONE_NONE_KEY => _x('none','Null component hook zone','waboot')
			];*/
			$opts = [];
			foreach($zones as $k => $v){
				$opts[$k] = $v['slug'];
			}
			return $opts;
		});

		/*$options[] = [
			'name' => _x( 'Zone Settings', 'component settings', 'waboot' ),
			'desc' => _x( 'Choose zone settings for this component', 'component_settings', 'waboot' ),
			'type' => 'info',
			'id'   => strtolower($this->name).'_zone-settings_info',
			'component' => true,
			'component_name' => $this->name
		];*/
		$options[] = [
			'name' => _x( 'Position', 'component settings', 'waboot' ),
			'desc' => _x( 'Choose in which zone you want to display', 'component_settings', 'waboot' ),
            'class' => 'zone_position half_option',
			'id'   => strtolower($this->name).'_display_zone',
			'std'  => isset($this->default_zone) ? $this->default_zone : "header",
			'options' => $zone_options,
			'type' => 'select',
			'component' => true,
			'component_name' => $this->name
		];
		$options[] = [
			'name' => _x( 'Priority', 'component settings', 'waboot' ),
			'desc' => _x( 'Choose the display priority', 'component_settings', 'waboot' ),
            'class' => 'zone_priority half_option',
			'id'   => strtolower($this->name).'_display_priority',
			'std'  => isset($this->default_priority) ? (string) $this->default_priority : "10",
			'type' => 'text',
			'component' => true,
			'component_name' => $this->name
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
		$p = $this->default_priority;
		if(function_exists("\\Waboot\\functions\\get_option")){
			$p_opt = \Waboot\functions\get_option(strtolower($this->name)."_display_priority");
			if($p_opt){
				$p = $p_opt;
			}
		}
		return $p;
	}

	/**
	 * Register an action to the component zone
	 * @param callable $action
	 */
	public function add_zone_action(callable $action){
		try{
			$display_zone = $this->get_display_zone();
			if($display_zone !== self::ZONE_NONE_KEY){
				$display_priority = $this->get_display_priority();
				WabootLayout()->add_zone_action($display_zone,$action,intval($display_priority));
			}
		}catch (\Exception $e){}
	}
}