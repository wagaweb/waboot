<?php

namespace Waboot;

use WBF\includes\mvc\HTMLView;
use wbf\includes\mvc\View;

class Layout{
	/**
	 * @var Layout
	 */
	private static $instance;
	/**
	 * @var array
	 */
	private $zones = [];
	
	/**
	 * Returns the *Singleton* instance.
	 *
	 * @return Layout The *Singleton* instance.
	 */
	public static function getInstance(){
		if (null === static::$instance) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Creates a new template zone
	 *
	 * @param $slug
	 * @param $template
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function create_zone($slug,$template){
		//Checks for valid template
		if(!is_string($template) && !is_array($template) && !$template instanceof View){
			throw new \Exception("You cannot create a zone thats is neither a string, an array or a View instance");
		}
		//Check template existence
		if(is_string($template) || is_array($template)){
			if(is_array($template)){
				$template = implode("-",$template);
			}
			$tpl_file = locate_template($template);
			if(!$tpl_file){
				throw new \Exception("The {$template} for the zone {$slug} does not exists");
			}
		}
		//Save the zone
		$this->zones[$slug] = [
			'slug' => $slug,
			'template' => $template
		];
		return $this;
	}

	/**
	 * Renders a template zone
	 *
	 * @param $slug
	 *
	 * @throws \Exception
	 */
	public function render_zone($slug){
		if(!isset($this->zones[$slug])) throw new \Exception("Zone {$slug} not found");

		$zone = $this->zones[$slug];

		if(is_string($zone['template'])){
			get_template_part($zone['template']);
		}elseif(is_array($zone['template'])){
			list($template,$part) = $zone['template'];
			get_template_part($template,$part);
		}else{
			//Here we have a View instance
			$zone['template']->clean()->display([
				"name" => $zone['slug']
			]);
		}
	}

	/**
	 * Private clone method to prevent cloning of the instance of the *Singleton* instance.
	 */
	private function __clone(){}

	/**
	 * Private unserialize method to prevent unserializing of the *Singleton* instance.
	 */
	private function __wakeup(){}
}