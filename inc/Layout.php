<?php

namespace Waboot;

use WBF\includes\mvc\HTMLView;

class Layout{
	/**
	 * @var Layout
	 */
	private static $instance;
	
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
	 * Renders the main
	 */
	public function main(){
		(new HTMLView("templates/main.php"))->clean()->display([]);
	}
	
	/**
	 * Renders an aside
	 * 
	 * @param $name
	 */
	public function aside($name){
		(new HTMLView("templates/aside.php"))->clean()->display([
			'name' => $name
		]);
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