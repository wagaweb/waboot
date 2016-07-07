<?php

namespace Waboot;

class Theme{
	/**
	 * @var Theme
	 */
	private static $instance;

	/**
	 * @var Layout
	 */
	var $layout;

	/**
	 * Returns the *Singleton* instance.
	 *
	 * @return Theme The *Singleton* instance.
	 */
	public static function getInstance(){
		if (null === static::$instance) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	protected function __construct(){
		$this->layout = Layout::getInstance();
	}

	/**
	 * Loads all theme hooks
	 */
	public function load_hooks(){
		$hooks_files = [
			'inc/hooks/init.php',
			'inc/hooks/hooks.php',
			'inc/hooks/widget_areas.php',
			'inc/hooks/options.php',
			'inc/hooks/entry/entry-header.php',
			'inc/hooks/stylesheets.php',
			'inc/hooks/scripts.php',
		];
		foreach($hooks_files as $file){
			if (!$filepath = locate_template($file)) {
				trigger_error(sprintf(__('Error locating %s for inclusion', 'waboot'), $file), E_USER_ERROR);
			}
			require_once $filepath;
		}
		return $this;
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