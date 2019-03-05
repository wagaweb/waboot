<?php

namespace Waboot;

use function Waboot\functions\components\install_remote_component;
use function Waboot\functions\safe_require_files;
use function Waboot\functions\wbf_exists;
use WBF\components\utils\Paths;
use WBF\components\utils\Utilities;
use WBF\modules\components\ComponentsManager;
use WBF\modules\options\Framework;

class Theme{
	/**
	 * @var Layout
	 */
	var $layout;
	/**
	 * @var array
	 */
	var $inline_styles;
	/**
	 * @var array
	 */
	var $components_styles;
	/**
	 * @var \WP_Styles
	 */
	var $custom_styles_handler;

	const GENERATOR_STEP_ALL = "ALL";
	const GENERATOR_STEP_COMPONENTS = "COMPONENTS";
	const GENERATOR_STEP_OPTIONS = "OPTIONS";
	const GENERATOR_STEP_PRE_ACTIONS = "PRE_ACTIONS";
	const GENERATOR_STEP_ACTIONS = "ACTIONS";
	const GENERATOR_ACTION_ALL = "ALL_ACTIONS";

	public function __construct(Layout $layout_handler, \WP_Dependencies $styles_handler){
		$this->layout = $layout_handler;
		$this->custom_styles_handler = $styles_handler;
	}

	/**
	 * Loads all theme hooks
	 */
	public function load_hooks(){
		add_action("waboot/head/end", [$this,"print_inline_styles"]);
		add_action('wbf/modules/components/after_components_options_saved',[$this,'build_components_style_file'],10,4);
		add_action('wp_enqueue_scripts', [$this,'enqueue_components_styles'],8); //7 is the priority of waboot styles

		$hooks_files = [
			'inc/hooks/init.php',
			'inc/hooks/hooks.php',
			'inc/hooks/layout.php',
			'inc/hooks/widget_areas.php',
			'inc/hooks/options.php',
			'inc/hooks/posts_and_pages.php',
			'inc/hooks/generators.php',
			'inc/hooks/components_installer.php',
			'inc/hooks/components_updater.php',
			'inc/hooks/stylesheets.php',
			'inc/hooks/scripts.php'
		];

		safe_require_files($hooks_files);

		do_action_ref_array('waboot/hooks_loaded',array(&$this));

		return $this;
	}

	/**
	 * Loads theme support functionality and extensions for vendor parts
	 */
	public function load_extensions(){
		$ext_files = [
			'inc/woocommerce/bootstrap.php',
		];

		safe_require_files($ext_files);

		do_action_ref_array('waboot/extensions_loaded',array(&$this));

		return $this;
	}

	/**
	 * A version of WP locate_template() that returns only the template name of located files
	 *
	 * @param array|string $template_names
	 * @param string $extension if the extension of the template files to look for
	 *
	 * @return string
	 */
	public function locate_template($template_names,$extension = '.php'){
		foreach ( (array) $template_names as $template_name ) {
			if ( !$template_name )
				continue;

			if(!preg_match('/'.$extension.'$/',$template_name)){
				$template_filename = $template_name.$extension;
			}else{
				$template_filename = $template_name;
			}

			if ( file_exists(STYLESHEETPATH . '/' . $template_filename)) {
				return $template_name;
				break;
			} elseif ( file_exists(TEMPLATEPATH . '/' . $template_filename) ) {
				return $template_name;
				break;
			} elseif ( file_exists( ABSPATH . WPINC . '/theme-compat/' . $template_filename ) ) {
				return $template_name;
				break;
			}
		}

		return '';
	}

	/**
	 * Adds a new inline style. Inline styles will be printed during "waboot/head/end" action.
	 *
	 * @use add_custom_type_style()
	 *
	 * @param $handle
	 * @param $src
	 * @param array $deps
	 * @param bool $ver
	 * @param string $media
	 */
	public function add_inline_style($handle,$src, $deps = array(), $ver = false, $media = 'all'){
		if(preg_match("/^https?/",$src)){
			$src = Utilities::url_to_path($src);
		}
		if(!file_exists($src)) return;
		$this->inline_styles[$handle] = [
			'handle' => $handle,
			'src' => $src,
			'deps' => $deps,
			'ver' => $ver,
			'media' => $media
		];
		$this->custom_styles_handler->add($handle,$src, $deps, $ver);
	}

	/**
	 * Print registered inline styles
	 *
	 * @hooked "waboot/head/end"
	 */
	public function print_inline_styles(){
		$output = $this->get_inline_styles_content();
		$output = sprintf( "<style id='%s-inline-css' type='text/css'>\n%s\n</style>\n", "components", $output );
		echo $output;
	}

	/**
	 * Retrieve a string that is the the result of all custom style type files merged.
	 *
	 * @return string
	 */
	private function get_inline_styles_content(){
		$output = "";
		$items = $this->collect_inline_styles();
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
		return $output;
	}

	/**
	 * Collect all inline styles
	 *
	 * @return array
	 */
	private function collect_inline_styles(){
		$parsed_handlers = [];
		$required_styles = $this->inline_styles;

		if(!\is_array($required_styles) || empty($required_styles)){
			return [];
		}

		foreach($required_styles as $style_handle => $style_params){
			$parsed_handlers[] = $style_handle;
			$this->custom_styles_handler->add($style_params['handle'],$style_params['src'], $style_params['deps'], $style_params['ver']);
		}

		/*
		 * We enqueue the registered inline styles. We hope that those steps will resolve dependencies
		 */
		$this->custom_styles_handler->all_deps($parsed_handlers);
		$items = $this->custom_styles_handler->to_do;
		return $items;
	}

	/**
	 * @return array
	 */
	public function get_components_styles(){
		if(isset($this->components_styles) && is_array($this->components_styles) && !empty($this->components_styles)){
			return $this->components_styles;
		}
		return [];
	}

	/**
	 * Set a style file to be merged
	 *
	 * @param $handle
	 * @param $src
	 * @param array $deps
	 * @param bool $ver
	 * @param string $media
	 *
	 */
	public function add_component_style($handle,$src, $deps = array(), $ver = false, $media = 'all'){
		$this->components_styles[$handle] = [
			'src' => $src,
			'deps' => $deps,
			'ver' => $ver,
			'media' => $media
		];
	}

	/**
	 * Create a new style file from all the merged-type custom styles
	 *
	 * @hooked 'wbf/modules/components/after_components_options_saved'
	 */
	public function build_components_style_file($registered_components,$categorized_registered_components,$compiled_components_options,$options_updated_flag){
		/*$registered_components = ComponentsManager::getAllComponents();
		$styles = [];

		foreach ($registered_components as $component_name => $component){
			if($component instanceof Component){
				$current_styles = $component->get_registered_styles();
				if(!empty($current_styles)){
					$styles = array_merge($styles,$current_styles);
				}
			}
		}*/

		$output = "";

		if(is_array($this->components_styles) && !empty($this->components_styles)){
			foreach($this->components_styles as $handler => $style){
				$current_style_src = Paths::url_to_path($style['src']);
				if(is_file($current_style_src)){
					//Read the file:
					$content = file_get_contents($current_style_src);
					//Append:
					if($content){
						$output .= "/********* $handler **********/\n";
						$output .= $content."\n";
					}
				}
			}
		}

		$filename = $this->get_components_style_file_name();

		if(is_file($filename)){
			unlink($filename);
		}

		if(!empty($output)){
			file_put_contents($filename,$output);
		}
	}

	/**
	 * @return string
	 */
	private function get_components_style_file_name(){
		$filename = WBF()->get_working_directory();
		$filename .= '/current-active-components-style.css';
		return $filename;
	}

	/**
	 * Enqueue the components style file
	 */
	public function enqueue_components_styles(){
		$filename = $this->get_components_style_file_name();
		if(!is_file($filename)) return;
		$ver = filemtime($filename);
		wp_enqueue_style('components-merged-style-file',Paths::path_to_url($filename),[],$ver);
	}

	/**
	 * Get theme generators
	 *
	 * @return array
	 */
	public static function get_generators(){
		$parent_dirpath = get_template_directory();
		$child_dirpath = get_stylesheet_directory();

		$generators_directories = [
			$child_dirpath."/inc/generators",
			$parent_dirpath."/inc/generators",
		];

		$generators_directories = array_unique(apply_filters("waboot/generators/directories",$generators_directories));

		$generators_files = [];

		$generators = [];

		foreach($generators_directories as $directory){
			$files = glob($directory."/*.json");
			if(is_array($files) && count($files) !== 0){
				$files = array_filter($files,function($el){
					if(basename($el) == "generator-template.json") return false;
					return true;
				});
				if(count($files) !== 0){
					$generators_files = array_merge($generators_files,$files);
				}
			}
		}

		foreach ($generators_files as $generators_file){
			$content = file_get_contents($generators_file);
			$parsed = json_decode($content);
			if(!is_null($parsed)){
				$pathinfo = pathinfo($generators_file);
				$slug = $pathinfo['filename'];
				$parsed->file = $generators_file;
				$parsed->slug = $slug;

				//Preview actions
				if(isset($parsed->preview)){
					if(preg_match('|'.$child_dirpath.'|',$generators_file)){
						$baseuri = get_stylesheet_directory_uri();
					}elseif(preg_match('|'.$parent_dirpath.'|',$generators_file)){
						$baseuri = get_template_directory_uri();
					}else{
						$baseuri = Paths::path_to_url(dirname($generators_file));
					}
					$parsed->preview_basepath = $baseuri;
				}

				$generators[$slug] = $parsed;
			}
		}

		return $generators;
	}

	/*
	public function read_generator_steps($generator_slug){
		$generators = Theme::get_generators();
		$steps = [];
		if(!array_key_exists($generator_slug,$generators)){
			return $steps;
		}
		$selected_generator = $generators[$generator_slug];
		try{
			$generator_instance = $this->get_generator_instance($generator_slug,$selected_generator);
			if(isset($selected_generator->pre_actions) && \is_array($selected_generator->pre_actions) && !empty($selected_generator->pre_actions)){
				$methods = $this->get_generator_methods($selected_generator,$generator_instance,self::GENERATOR_STEP_PRE_ACTIONS);
				foreach ($methods as $method_key => $method_name){
					$steps = [
						'context' => self::GENERATOR_STEP_PRE_ACTIONS,
						'type' => 'method',
						'action' => 'execute',
						'action_name' => $method_name,
						'action_subject' => $selected_generator
					];
				}
			}
		}catch(\Exception $e){
			return $steps;
		}
	}*/


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

			if($step == self::GENERATOR_STEP_ALL || $step == self::GENERATOR_STEP_PRE_ACTIONS){
				//Do actions before
				if(!isset($selected_generator->pre_actions) || !is_array($selected_generator->pre_actions) || empty($selected_generator->pre_actions)){
					return wp_parse_args([
						'next_step' => self::GENERATOR_STEP_COMPONENTS,
						'status' => 'success',
					],$default_return);
				}
				$generator_instance = $this->get_generator_instance($generator_slug,$selected_generator);
				$method_name = $action;
				$methods = $this->get_generator_methods($selected_generator,$generator_instance,self::GENERATOR_STEP_PRE_ACTIONS);
				$method_key = $this->execute_generator_method($method_name,$selected_generator,$generator_instance,self::GENERATOR_STEP_PRE_ACTIONS);
				if($method_key == count($methods)-1){
					//This is the last method
					return wp_parse_args([
						'next_step' => self::GENERATOR_STEP_COMPONENTS,
						'status' => 'success'
					],$default_return);
				}else{
					//This is not the last method
					return wp_parse_args([
						'next_step' => $step,
						'next_action' => $methods[$method_key+1],
						'status' => 'success'
					],$default_return);
				}
			}

			if($step == self::GENERATOR_STEP_ALL || $step == self::GENERATOR_STEP_COMPONENTS){
				if(!wbf_exists()) throw new \Exception("WBF not detected");
				//Toggle components
				$registered_components = ComponentsManager::getAllComponents();
				$child_components = [];
				foreach ($registered_components as $component_name => $component_data){ //Disable all components
					if($component_data->is_child_component){
						$child_components[] = $component_name;
					}
					try{
						ComponentsManager::disable($component_name,$component_data->is_child_component);
					}catch(\Exception $e){}
				}
				unset($component_name);
				unset($component_data);
				if(isset($selected_generator->components) && is_array($selected_generator->components) && !empty($selected_generator->components)){
					foreach ($selected_generator->components as $component_to_enable){
						if(!ComponentsManager::is_present($component_to_enable)){
							$component_installed = install_remote_component($component_to_enable);
							ComponentsManager::detect_components(true);
							if(is_child_theme()){
								//The new component has been installed in the child
								if(!\in_array($component_to_enable,$child_components)){
									$child_components[] = $component_to_enable;
								}
							}
						}else{
							$component_installed = true;
						}
						if($component_installed){
							if(\in_array($component_to_enable,$child_components)){
								ComponentsManager::enable($component_to_enable, true); //Selectively enable components
							}else{
								ComponentsManager::enable($component_to_enable); //Selectively enable components
							}
						}
					}
				}
				if($step == self::GENERATOR_STEP_COMPONENTS){
					return wp_parse_args([
						'next_step' => self::GENERATOR_STEP_OPTIONS,
						'status' => 'success'
					],$default_return);
				}
			}

			if($step == self::GENERATOR_STEP_ALL || $step == self::GENERATOR_STEP_OPTIONS){
				if(!wbf_exists()) throw new \Exception("WBF not detected");
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
						'next_step' => self::GENERATOR_STEP_ACTIONS,
						'status' => 'success'
					],$default_return);
				}
			}

			if($step == self::GENERATOR_STEP_ALL || $step == self::GENERATOR_STEP_ACTIONS){
				if(!wbf_exists()) throw new \Exception("WBF not detected");
				//Do actions
				if(!isset($selected_generator->actions) || !is_array($selected_generator->actions) || empty($selected_generator->actions)){
					return wp_parse_args([
						'next_step' => false,
						'next_action' => false,
						'status' => 'success',
						'complete' => true
					],$default_return);
				}
				$generator_instance = $this->get_generator_instance($generator_slug,$selected_generator);
				if($action == self::GENERATOR_ACTION_ALL){
					foreach ($selected_generator->actions as $method_name){
						if(method_exists($generator_instance,$method_name)){
							call_user_func([$generator_instance,$method_name]);
						}
					}
				}else{
					$method_name = $action;
					$methods = $this->get_generator_methods($selected_generator,$generator_instance,self::GENERATOR_STEP_ACTIONS);
					$method_key = $this->execute_generator_method($method_name,$selected_generator,$generator_instance,self::GENERATOR_STEP_ACTIONS);
					if($method_key == count($methods)-1){
						//This is the last method
						return wp_parse_args([
							'next_step' => false,
							'next_action' => false,
							'status' => 'success',
							'complete' => true
						],$default_return);
					}else{
						//This is not the last method
						return wp_parse_args([
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
				'status' => 'failed',
				'message' => $e->getMessage()
			],$default_return);
		}
	}

	/**
	 * @param $generator_slug
	 * @param $generator_params
	 *
	 * @return object
	 * @throws \Exception
	 */
	private function get_generator_instance($generator_slug,$generator_params){
		$generator_filename = dirname($generator_params->file)."/".$generator_slug.".php";
		if(!file_exists($generator_filename)){
			throw new \Exception("Generator file not found.");
		}
		require_once $generator_filename; //Require the generator php file
		if(!isset($generator_params->classname) || !class_exists($generator_params->classname)){
			throw new \Exception("Cannot instantiate $generator_params->classname.");
		}
		$generator_instance = new $generator_params->classname;
		return $generator_instance;
	}

	/**
	 * @param $generator_params
	 * @param $generator_instance
	 * @param $context
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function get_generator_methods($generator_params,$generator_instance,$context){
		if($context === self::GENERATOR_STEP_PRE_ACTIONS){
			$methods = $generator_params->pre_actions;
		}elseif($context === self::GENERATOR_STEP_ACTIONS){
			$methods = $generator_params->actions;
		}else{
			$methods = [];
		}
		$real_methods = get_class_methods($generator_instance);
		$methods = array_filter($methods,function($var) use($real_methods){
			return in_array($var,$real_methods);
		});
		if(!is_array($methods) || empty($methods)){
			throw new \Exception("No methods found on generator instance.");
		}
		return $methods;
	}

	/**
	 * @param $method_name
	 * @param $generator_instance
	 *
	 * @return int
	 * @throws \Exception
	 */
	private function execute_generator_method($method_name,$generator_params,$generator_instance,$context){
		$methods = $this->get_generator_methods($generator_params,$generator_instance,$context);
		if(!$method_name || $method_name === '' || $method_name === 'false'){
			$method_name = $methods[0];
		}
		$method_key = array_search($method_name,$methods);
		call_user_func([$generator_instance,$method_name]);
		return $method_key;
	}

	/**
	 * Loads the hooks for displaying the generators page only
	 */
	public static function preload_generators_page(){
		locate_template('inc/hooks/stylesheets.php', true);
		locate_template('inc/hooks/scripts.php', true);
		locate_template('inc/hooks/generators.php', true);
	}

	/**
	 * Checks if the wizard (aka: the generators page) han been run once
	 *
	 * @return bool
	 */
	public static function is_wizard_done(){
		return (bool) get_option('waboot-done-wizard',false);
	}

	public static function is_wizard_skipped(){
		return (bool) get_option('waboot-skipped-wizard',false);
	}

	/**
	 * Set the wizard (aka: the the generators page) as run
	 */
	public static function set_wizard_as_done(){
		update_option('waboot-done-wizard',true);
	}

	/**
	 * Set the wizard (aka: the the generators page) as run
	 */
	public static function set_wizard_as_skipped(){
		update_option('waboot-skipped-wizard',true);
	}

	/**
	 * Reset wizard options
	 */
	public static function reset_wizard(){
		delete_option('waboot-done-wizard');
		delete_option('waboot-skipped-wizard');
	}
}