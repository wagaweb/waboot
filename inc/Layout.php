<?php

namespace Waboot;

use WBF\components\mvc\HTMLView;
use WBF\components\mvc\View;
use WBF\components\utils\Utilities;

class Layout{
	/**
	 * @var array
	 */
	private $zones = [];
	/**
	 * @var array
	 */
	private $grid_classes = [];
	
	const LAYOUT_FULL_WIDTH = "full-width";
	const LAYOUT_PRIMARY_RIGHT = "sidebar-right";
	const LAYOUT_PRIMARY_LEFT = "sidebar-left";
	const LAYOUT_TWO_SIDEBARS = "two-sidebars";
	const LAYOUT_TWO_SIDEBARS_LEFT = "two-sidebars-left";
	const LAYOUT_TWO_SIDEBARS_RIGHT = "two-sidebars-right";

	const GRID_CLASS_ROW = 'row';
	const GRID_CLASS_CONTAINER = 'container-boxed';
	const GRID_CLASS_CONTAINER_FLUID = 'container-fluid';
	const GRID_CLASS_COL_SUFFIX = 'col_suffix';

	/**
	 * Layout constructor.
	 *
	 * @param array $params
	 */
	public function __construct($params = []) {
		$default_grid_classes = [
			self::GRID_CLASS_ROW => 'wbrow',
			self::GRID_CLASS_CONTAINER => 'wbcontainer',
			self::GRID_CLASS_CONTAINER_FLUID => 'wbcontainer--fluid',
			self::GRID_CLASS_COL_SUFFIX => 'wbcol--'
		];
		if(isset($params['grid_classes'])){
			$grid_classes = wp_parse_args($params['grid_classes'],$default_grid_classes);
		}else{
			$grid_classes = $default_grid_classes;
		}
		$this->grid_classes = $grid_classes;
	}

	/**
	 * Creates a new template zone
	 *
	 * @param string $slug
	 * @param string|array|View|bool $template
	 * @param array $args
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function create_zone($slug,$template = false,$args = []){
		//Check for valid slug
		if(preg_match("/ /",$slug)){
			throw new \Exception("You cannot have whitespaces in a zone slug");
		}
		
		if(isset($template) && $template !== false){
			//Checks for valid template
			if(!is_string($template) && !is_array($template) && !$template instanceof View){
				throw new \Exception("You cannot create a zone thats is neither a string, an array or a View instance");
			}
			//Check template existence
			if(is_string($template) || is_array($template)){
				if(is_array($template)){
					$template = implode("-",$template);
				}
				$tpl_file = \locate_template($template);
				if(!$tpl_file){
					throw new \Exception("The {$template} for the zone {$slug} does not exists");
				}
			}
		}
			
		if(!is_array($args)){
			throw new \Exception('$args must be an array');
		}
		
		//Save the zone
		$args = wp_parse_args($args,[
			'always_load' => false,
			'can_render_callback' => null,
		]);

		$this->zones[$slug] = [
			'slug' => $slug,
			'template' => $template,
			'actions_hook' => 'waboot/zones/'.$slug,
			'actions' => [],
			'options' => $args
		];
		return $this;
	}

	/**
	 * Set an attribute value (the options array) to a zone
	 *
	 * @param $zone_slug
	 * @param $zone_attr
	 * @param $attr_value
	 *
	 * @throws \Exception
	 */
	public function set_zone_attr($zone_slug,$zone_attr,$attr_value){
		try{
			$this->check_zone($zone_slug);
			$this->zones[$zone_slug]['options'][$zone_attr] = $attr_value;
		}catch(\Exception $e){}
	}

	/**
	 * Renders a template zone
	 *
	 * @param $slug
	 *
	 * @throws \Exception
	 */
	public function render_zone($slug){
		try {
			$this->check_zone( $slug );

			$zone = $this->zones[ $slug ];

			if ( $zone['options']['always_load'] || ! empty( $zone['actions'] ) ) { //Render the zone only if need to...
				$can_render = true;
				if ( isset( $zone['options']['can_render_callback'] ) && is_callable( $zone['options']['can_render_callback'] ) ) {
					$can_render = false;
					$can_render = call_user_func( $zone['options']['can_render_callback'] );
				}
				if ( $can_render ) {
					if ( is_string( $zone['template'] ) ) {
						get_template_part( $zone['template'] );
					} elseif ( is_array( $zone['template'] ) ) {
						list( $template, $part ) = $zone['template'];
						get_template_part( $template, $part );
					} elseif ( $zone['template'] instanceof View ) {
						$zone['template']->clean()->display( [
							"name" => $zone['slug']
						] );
					} else {
						//Here we do not have a template
						$this->do_zone_action( $slug );
					}
				}
			}
		}catch (\Exception $e){}
	}

	/**
	 * Checks if a zone can be rendered (is on "always_load" or has actions hooked.
	 *
	 * @param $slug
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function can_render_zone($slug){
		try{
			$this->check_zone($slug);
			$zone = $this->zones[$slug];
			return $zone['options']['always_load'] || !empty($zone['actions']);
		}catch (\Exception $e){
			return false;
		}
	}

	/**
	 * Renders wordpress theme hierarchy templates through our template system.
	 * 
	 * This function is a draft. For now we prefer to user WP native get_template_parts for bettere compatibility.
	 * 
	 * @param $template
	 */
	public function render_wp_template_content($template){
		switch($template){
			case "archive.php":
				$template_file = "templates/archive-content";
				\Waboot\functions\render_archives($template_file);
				break;
			case "page.php":
				break;
			case "single.php":
				break;
		}
	}

	/**
	 * Get the variables needed for render a specific Wordpress template.
	 * 
	 * We use this function to avoid conditionals and stuff in our templates.
	 * 
	 * @param $template
	 * 
	 * @return array
	 *
	 * @not-used
	 */
	public function get_wp_template_vars($template){
		$vars = [];
		switch($template){
			case "archive.php":
				$vars = \Waboot\functions\get_archives_vars();
				break;
			case "page.php":
				break;
			case "single.php":
				break;
		}
		return $vars;
	}

	/**
	 * Adds an action to the zone
	 * 
	 * @param string $slug
	 * @param Callable $callable
	 * @param integer $priority
	 * @param integer $accepted_args
	 *
	 * @throws \Exception
	 */
	public function add_zone_action($slug,$callable,$priority = 10,$accepted_args = 1){
		try{
			$this->check_zone($slug);

			$zone = $this->zones[$slug];

			$this->zones[$slug]['actions'][] = [
				"callable" =>  $callable,
				"priority" => $priority
			];

			add_action($zone['actions_hook'],$callable,$priority,$accepted_args);
		}catch (\Exception $e){}
	}

	/**
	 * Performs zone actions
	 * 
	 * @param string $slug
	 *
	 * @throws \Exception
	 */
	public function do_zone_action($slug){
		try{
			$this->check_zone($slug);

			$zone = $this->zones[$slug];

			do_action($zone['actions_hook']);
		}catch (\Exception $e){}
	}

	/**
	 * Get the available zones
	 *
	 * @return array
	 */
	public function getZones(){
		return $this->zones;
	}

	/**
	 * Checks whether a zone exists or not
	 * 
	 * @param string $slug
	 *
	 * @return bool
	 * @throws \Exception
	 */
	private function check_zone($slug){
		if(!is_string($slug)){
			throw new \Exception("Zone slug must be a string");
		}elseif(empty($slug)){
			throw new \Exception("Empty slug provided");
		}
		if(!isset($this->zones[$slug])){
			throw new \Exception("Zone {$slug} not found");
		}
		return true;
	}

	/**
	 * Update the current grid classes
	 *
	 * Called during 'init'.
	 */
	public function update_grid_classes(){
		$classes = apply_filters('waboot/layout/grid_classes',[]);
		$default_grid_classes = $this->grid_classes;
		$classes = wp_parse_args($classes,$default_grid_classes);;
		$this->grid_classes = $classes;
	}

	/**
	 * @param string $type
	 *
	 * @return mixed|string
	 */
	public function get_grid_class($type){
		if(array_key_exists($type,$this->grid_classes)){
			return $this->grid_classes[$type];
		}
		return '';
	}

	/**
	 * Return the container class based on $type
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function get_container_grid_class($type){
		if($type === 'container' || $type === 'wbcontainer' || $type === 'boxed' || $type === 'container-boxed'){
			$type = self::GRID_CLASS_CONTAINER;
		}elseif($type === 'container-fluid' || $type === 'wbcontainer-fluid' || $type === 'fluid'){
			$type = self::GRID_CLASS_CONTAINER_FLUID;
		}
		return $this->get_grid_class($type);
	}

	/**
	 * @return string
	 */
	public function get_col_grid_class(){
		return $this->get_grid_class(Layout::GRID_CLASS_COL_SUFFIX);
	}
	
	/*
	 * Utilities
	 */
	
	/**
	 * Removes "col-" string values from an array
	 * @param array $classes_array
	 */
	static function remove_cols_classes(array &$classes_array){
		foreach($classes_array as $k => $v){
			//if(preg_match("/".WabootLayout()->get_col_grid_class()."/",$v)){
			if(preg_match("/wbcol--/",$v)){
				unset($classes_array[$k]);
			}
		}
	}

	/**
	 * Convert size labels (1/3, 2/3, ect) into size integers (for using into wbcol-<x>)
	 * @param string $width the label
	 *
	 * @return int
	 */
	static function layout_width_to_int($width){
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
}