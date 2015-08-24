<?php

namespace WBF\modules\options;

class CustomizerManager{

	static $setting_type = "wbf_theme_option";

	public static function init(){
		add_action( 'customize_register','\WBF\modules\options\CustomizerManager::register' );
		add_action( 'customize_update_wbf_theme_option', '\WBF\modules\options\CustomizerManager::update', 10, 2 );
		add_action( 'customize_preview_wbf_theme_option', '\WBF\modules\options\CustomizerManager::preview', 10, 2 );
	}
	public static function register(\WP_Customize_Manager $wp_customize){
		$options = Framework::get_registered_options();

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
					'default' => isset($opt['std']) ? $opt['std'] : "",
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
	public static function update(){

	}
	public static function preview(){

	}
}