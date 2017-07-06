<?php

namespace Waboot;

use WBF\components\utils\Utilities;
use WBF\modules\components\ComponentsManager;
use WBF\modules\options\Framework;

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

	const GENERATOR_STEP_ALL = "ALL";
	const GENERATOR_STEP_COMPONENTS = "COMPONENTS";
	const GENERATOR_STEP_OPTIONS = "OPTIONS";
	const GENERATOR_STEP_ACTIONS = "ACTIONS";
	const GENERATOR_ACTION_ALL = "ALL_ACTIONS";

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
			'inc/hooks/generators.php',
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
	public function add_inline_style($handle,$src, $deps = array(), $ver = false, $media = 'all'){
		if(preg_match("/^https?/",$src)){
			$src = Utilities::url_to_path($src);
		}
		if(!file_exists($src)) return;
		$this->inline_styles[] = $handle;
		$this->custom_styles_handler->add($handle,$src, $deps, $ver);
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
					$output .= "/********* $current_style->handle **********/\n";
					$output .= $content."\n";
				}
			}
		}
		$output = sprintf( "<style id='%s-inline-css' type='text/css'>\n%s\n</style>\n", "components", $output );
		echo $output;
	}

	/**
	 * Get theme generators
	 *
	 * @return array
	 */
	public static function get_generators(){
		$generators_directories = [
			get_stylesheet_directory()."/inc/generators",
			get_template_directory()."/inc/generators",
		];

		$generators_directories = array_unique(apply_filters("waboot/generators/directories",$generators_directories));

		$generators_files = [];

		$generators = [];

		foreach($generators_directories as $directory){
			$files = glob($directory."/*.json");
			if(is_array($files)){
				$files = array_filter($files,function($el){
					if(basename($el) == "generator-template.json") return false;
					return true;
				});
				if(!empty($files)){
					$generators_files = array_merge($generators_files,$files);
				}
			}
		}

		foreach ($generators_files as $generators_file){
			$content = file_get_contents($generators_file);
			$parsed = json_decode($content);
			if(!is_null($parsed)){
				$slug = rtrim(basename($generators_file),".json");
				$parsed->file = $generators_file;
				$parsed->slug = $slug;
				$generators[$slug] = $parsed;
			}
		}

		return $generators;
	}

	/**
	 * Handle a generator
	 *
	 * @param $generator_slug
	 * @param string $step
	 * @param string $action
	 *
	 * @return array
	 */
	public function handle_generator($generator_slug, $step = self::GENERATOR_STEP_ALL, $action = self::GENERATOR_ACTION_ALL){
		$generators = Theme::get_generators();
		$default_return = [
			'generator' => $generator_slug,
			'step' => $step,
			'next_step' => false,
			'action' => $action,
			'next_action' => false,
			'status' => false,
			'message' => '',
			'complete' => false
		];
		if(!array_key_exists($generator_slug,$generators)){
			return wp_parse_args([
				'step' => $step,
				'status' => 'failed',
				'message' => 'No generator found'
			],$default_return);
		}
		try{
			$selected_generator = $generators[$generator_slug];

			if($step == self::GENERATOR_STEP_ALL || $step == self::GENERATOR_STEP_COMPONENTS){
				//Toggle components
				$registered_components = ComponentsManager::getAllComponents();
				foreach ($registered_components as $component_name => $component_data){ //Disable all components
					ComponentsManager::disable($component_name);
				}
				if(isset($selected_generator->components) && is_array($selected_generator->components) && !empty($selected_generator->components)){
					foreach ($selected_generator->components as $component_to_enable){
						ComponentsManager::enable($component_to_enable); //Selectively enable components
					}
				}
				if($step == self::GENERATOR_STEP_COMPONENTS){
					return wp_parse_args([
						'step' => 'components',
						'next_step' => self::GENERATOR_STEP_OPTIONS,
						'status' => 'success'
					],$default_return);
				}
			}

			if($step == self::GENERATOR_STEP_ALL || $step == self::GENERATOR_STEP_OPTIONS){
				//Setup options
				if(isset($selected_generator->options)){
					$options = json_decode(json_encode($selected_generator->options), true); //stdClass to array
					if(is_array($options) && !empty($options)){
						$options_to_set = [];
						foreach ($selected_generator->options as $option_name => $option_value){
							$current_option = Framework::get_option_object($option_name);
							if(is_array($current_option)){
								$options_to_set[$option_name] = $option_value;
								Framework::set_option_value($option_name,$option_value);
							}
						}
					}
				}
				if($step == self::GENERATOR_STEP_OPTIONS){
					return wp_parse_args([
						'step' => 'options',
						'next_step' => self::GENERATOR_STEP_ACTIONS,
						'status' => 'success'
					],$default_return);
				}
			}

			if($step == self::GENERATOR_STEP_ALL || $step == self::GENERATOR_STEP_ACTIONS){
				//Do actions
				if(!isset($selected_generator->actions) || !is_array($selected_generator->actions) || empty($selected_generator->actions)){
					throw new \Exception("No action available or found.");
				}
				$generator_filename = dirname($selected_generator->file)."/".$generator_slug.".php";
				if(!file_exists($generator_filename)){
					throw new \Exception("Generator file not found.");
				}
				//Require the generator php file
				$generator_filename = dirname($selected_generator->file)."/".$generator_slug.".php";
				require_once $generator_filename;
				if(!isset($selected_generator->classname) || !class_exists($selected_generator->classname)){
					throw new \Exception("Cannot instantiate $selected_generator->classname.");
				}
				$generator_instance = new $selected_generator->classname;
				if($action == self::GENERATOR_ACTION_ALL){
					foreach ($selected_generator->actions as $method_name){
						if(method_exists($generator_instance,$method_name)){
							call_user_func([$generator_instance,$method_name]);
						}
					}
				}else{
					$method_name = $action;
					if(!method_exists($generator_instance,$method_name)){
						throw new \Exception("$method_name does not exists on generator instance.");
					}
					$methods = get_class_methods($generator_instance);
					$method_key = array_search($method_name,$methods);
					call_user_func([$generator_instance,$method_name]);
					if($method_key == count($methods)-1){
						//This is the last method
						return wp_parse_args([
							'step' => $step,
							'next_step' => false,
							'next_action' => false,
							'status' => 'success',
							'complete' => true
						],$default_return);
					}else{
						//This is not the last method
						return wp_parse_args([
							'step' => $step,
							'next_step' => $step,
							'next_action' => $methods[$method_key+1],
							'status' => 'success'
						],$default_return);
					}
				}
			}

			return wp_parse_args([
				'status' => 'success',
				'complete' => true
			],$default_return);
		}catch (\Exception $e){
			return wp_parse_args([
				'step' => $step,
				'action' => $action,
				'status' => 'failed',
				'message' => $e->getMessage()
			],$default_return);
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