<?php

namespace WBF\modules\options;

use WBF\includes\compiler\Styles_Compiler;

class CustomizerManager{

	static $setting_type = "wbf_theme_option";

	public static function init(){
		global $wbf_styles_compiler;
		add_action( 'customize_register','\WBF\modules\options\CustomizerManager::register' );
		add_action( 'customize_update_wbf_theme_option', '\WBF\modules\options\CustomizerManager::update', 10, 2 );
		add_action( 'customize_save_after', '\WBF\modules\options\CustomizerManager::after_customizer_save', 10, 2 );
		add_action( 'customize_preview_wbf_theme_option', '\WBF\modules\options\CustomizerManager::preview', 10, 2 );
		add_action( 'wbf/compiler/pre_compile/customizer_preview', '\WBF\modules\options\CustomizerManager::styles_preview_pre_callback', 10);
		add_action( 'wbf/compiler/post_compile/customizer_preview', '\WBF\modules\options\CustomizerManager::styles_preview_post_callback', 10, 2 );
		//Add a new compile set to styles compiler
		if(isset($wbf_styles_compiler) && $wbf_styles_compiler){
			$customizer_style = $wbf_styles_compiler->get_primary_set();
			$customizer_style['output'] = null;
			$customizer_style['exclude_from_global_compile'] = true;
			$customizer_style['primary'] = false;
			$wbf_styles_compiler->base_compiler->add_set("customizer_preview",$customizer_style);
		}
	}
	public static function register(\WP_Customize_Manager $wp_customize){
		$options = Framework::get_registered_options();
		$options_values = Framework::get_options_values();

		$wp_customize->add_panel('wbf_theme_options',[
			'title' => __("Theme Options","wbf"),
			'description' => __("WBF Managed settings","wbf")
		]);

		$current_section = "";
		foreach($options as $opt){
			if($opt['type'] == "heading"){
				$wp_customize->add_section($opt['name'],[
					'title' => $opt['name'],
					'panel' => 'wbf_theme_options'
				]);
				$current_section = $opt['name'];
			}else{

				$unsupported_types = ['info','typography','multicheck','csseditor'];
				$equivalent_types = [
					'images' => 'select'
				];

				if(in_array($opt['type'],$unsupported_types)) continue;

				$setting_id = "theme_options[{$opt['id']}]";

				$wp_customize->add_setting($setting_id,[
					'type' => self::$setting_type,
					'capability' => 'manage_options',
					'default' => call_user_func(function() use($options_values,$opt){
						if(isset($options_values[$opt['id']])){
							return $options_values[$opt['id']];
						}else{
							if(isset($opt['std'])){
								return $opt['std'];
							}
						}
						return "";
					}),
					'transport' => 'refresh',
					'sanitize_callback' => '',
					'sanitize_js_callback' => ''
				]);

				//Detect control type and choices
				$args = [];
				$custom_control = false;
				switch($opt['type']){
					case "color":
						$custom_control = "\WP_Customize_Color_Control";
						break;
					case "upload":
						$custom_control = "\WP_Customize_Upload_Control";
						break;
					case "images":
						$args['type'] = $equivalent_types[$opt['type']];
						$args['choices'] = call_user_func(function() use($opt){
							$choices = [];
							foreach($opt['options'] as $k => $v){
								$choices[$k] = $v['label'];
							}
							return $choices;
						});
						break;
					case "select":
						$args['type'] = "select";
						$args['choices'] = $opt['options'];
						break;
					default:
						$args['type'] = $opt['type'];
						break;
				}

				$args = array_merge($args,[
					'priority' => 10,
					'section' => $current_section,
					'label' => $opt['name'],
					'description' => isset($opt['desc']) ? $opt['desc'] : "",
				]);

				if(!$custom_control){
					$wp_customize->add_control($setting_id,$args);
				}else{
					$custom_control = new $custom_control($wp_customize,$setting_id,$args);
					$wp_customize->add_control($custom_control);
				}
			}
		}
	}

	public static function update($value, \WP_Customize_Setting $setting){
		$name = call_user_func(function() use($setting){
			$match = preg_match("/\[([\w_]+)\]/",$setting->id,$matches);
			if($match) return $matches[1];
			else return false;
		});
		Framework::set_option_value($name,$value);
	}

	public static function after_customizer_save(\WP_Customize_Manager $wp_customize){
		//Recompile the styles
		$values = Framework::get_options_values();
		of_recompile_styles($values,true); //compile and release
		delete_transient("wbf_customizer_generated_preview_css");
	}

	/**
	 * Handles the preview of the modified theme options into the wordpress customizer. Temporary add a filter that changes the of_get_option retrieved value
	 * @param \WP_Customize_Setting $setting
	 */
	public static function preview(\WP_Customize_Setting $setting){
;		$name = call_user_func(function() use($setting){
			$match = preg_match("/\[([\w_]+)\]/",$setting->id,$matches);
			if($match) return $matches[1];
			else return false;
		});
		if($name){
			add_filter("wbf/theme_options/get/{$name}",function($value) use($setting){
				$new_value = $setting->post_value();
				if(!$new_value){
					return $value;
				}else{
					return $new_value;
				}
			});
			if(!has_action("wp_head",'\WBF\modules\options\CustomizerManager::styles_preview')){
				add_action("wp_head",'\WBF\modules\options\CustomizerManager::styles_preview',999);
			}
		}
	}

	/**
	 * Action performed for generate the styles preview into the customizer. This will print out the theme style into wp_head.
	 */
	public static function styles_preview(){
		global $wbf_styles_compiler;
		$post_values = json_decode( wp_unslash( $_POST['customized'] ), true );
		if(!empty($post_values)){
			//Get only the updated post values (the $_POST['customized'] is updated every time an option being changed):
			$cached_post_values = get_transient("wbf_customizer_post_values");
			if(!$cached_post_values) $cached_post_values = [];
			$new_post_values = array_diff_assoc($post_values,$cached_post_values);
			set_transient("wbf_customizer_post_values",$post_values);

			$recompile_flag = false;
			$cached_generated_css = get_transient("wbf_customizer_generated_preview_css");
			//Detect if we must recompile or not:
			foreach($new_post_values as $opt_name => $opt_value){
				$opt_id = call_user_func(function() use($opt_name){
					$match = preg_match("/\[([\w_]+)\]/",$opt_name,$matches);
					if($match) return $matches[1];
					else return false;
				});
				if(Framework::option_must_recompile_styles($opt_id)){
					$recompile_flag = true;
				}
			}
			if(!$recompile_flag){
				if(!$cached_generated_css) $cached_generated_css = "";
				$generated_css = $cached_generated_css;
			}else{
				$generated_css = $wbf_styles_compiler->compile("customizer_preview");
				set_transient("wbf_customizer_generated_preview_css",$generated_css);
			}
			//Add the css to the head:
			$output_string = "<style data-customizer-preview>".$generated_css."</style>";
			echo $output_string;
		}
	}

	/**
	 * Action performed into "wbf/compiler/pre_compile" action
	 * @param $args
	 */
	public static function styles_preview_pre_callback($args){
		//Create the file with the new values for the preview
		of_generate_less_file(Framework::get_options_values_filtered());
	}

	/**
	 * Action performed into "wbf/compiler/post_compile" action
	 * @param $args
	 * @param $css
	 */
	public static function styles_preview_post_callback($args,$css){
		//Restore the file
		of_generate_less_file();
	}
}