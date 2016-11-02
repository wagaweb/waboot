<?php

namespace Waboot;

use WBF\components\utils\Utilities;

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
	 * @var array
	 */
	var $inline_styles;

	/**
	 * @var \WP_Styles
	 */
	var $custom_styles_handler;

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
		$this->custom_styles_handler = new \WP_Styles();
		add_action("waboot/head/end", [$this,"print_inline_styles"]);
	}

	/**
	 * Loads all theme hooks
	 */
	public function load_hooks(){
		$hooks_files = [
			'inc/hooks/init.php',
			'inc/hooks/hooks.php',
			'inc/hooks/layout.php',
			'inc/hooks/widget_areas.php',
			'inc/hooks/options.php',
			'inc/hooks/entry/entry.php',
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
	 * Adds a new inline style. Inline styles will be printed during "waboot/head/end" action.
	 *
	 * @param $handle
	 * @param $path
	 */
	public function add_inline_style($handle,$path){
		if(preg_match("/^https?/",$path)){
			$path = Utilities::url_to_path($path);
		}
		if(!file_exists($path)) return;
		$this->inline_styles[] = $handle;
		$this->custom_styles_handler->add($handle,$path);
	}

	/**
	 * Print registered inline styles
	 *
	 * @hooked "waboot/head/end"
	 */
	public function print_inline_styles(){
		$output = "";
		/*
		 * We enqueue the registered inline styles. We hope that those steps will resolve dependencies
		 */
		$this->custom_styles_handler->all_deps($this->inline_styles);
		$items = $this->custom_styles_handler->to_do;
		/*
		 * We cycle through the registered styles. We suppose that those styles are already ordered by dependency (it's the reason we used WP_Styles in the first place)
		 */
		foreach ($items as $style){
			if(isset($this->custom_styles_handler->registered[$style])){
				$current_style = $this->custom_styles_handler->registered[$style];
				//Read the file:
				$content = file_get_contents($current_style->src);
				//Append:
				if($content){
					$output .= $content."\n";
				}
			}
		}
		$output = sprintf( "<style id='%s-inline-css' type='text/css'>\n%s\n</style>\n", "components", $output );
		echo $output;
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