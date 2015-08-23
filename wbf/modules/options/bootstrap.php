<?php
/**
 * Options Framework WBF Edition
 */

namespace WBF\modules\options;

require_once "functions.php";
require_once WBF_DIRECTORY."/vendor/options-framework/class-options-sanitization.php";
require_once "sanitization.php";

if ( STYLESHEETPATH == TEMPLATEPATH ) {
    define('OPTIONS_FRAMEWORK_URL', TEMPLATEPATH . '/wbf/vendor/options-framework/');
    define('OPTIONS_FRAMEWORK_DIRECTORY', get_bloginfo('template_directory') . '/wbf/vendor/options-framework/');
} else {
    define('OPTIONS_FRAMEWORK_URL', STYLESHEETPATH . '/wbf/vendor/options-framework/');
    define('OPTIONS_FRAMEWORK_DIRECTORY', get_bloginfo('template_directory') . '/wbf/vendor/options-framework/');
}

add_action("wbf_init",'\WBF\modules\options\module_init', 11);
add_action( "updated_option", '\WBF\modules\options\of_options_save', 9999, 3 );
add_action( "wbf/compiler/pre_compile", '\WBF\modules\options\of_generate_less_file', 9999, 3 );

/**
 * Font selector actions
 */
add_action("wp_ajax_gfontfetcher_getFonts",'\WBF\modules\options\FontSelector::getFonts');
add_action("wp_ajax_nopriv_gfontfetcher_getFonts",'\WBF\modules\options\FontSelector::getFonts');
add_action("wp_ajax_gfontfetcher_getFontInfo",'\WBF\modules\options\FontSelector::getFontInfo');
add_action("wp_ajax_nopriv_gfontfetcher_getFontInfo",'WBF\modules\options\FontSelector::getFontInfo');

/**
 * Sanitize functions
 */
add_filter( 'of_sanitize_csseditor', '\of_sanitize_textarea' );
add_filter( 'of_sanitize_typography', '\WBF\modules\options\of_sanitize_typography' );

/**
 * Allow "a", "embed" and "script" tags in theme options text boxes
 */
remove_filter( 'of_sanitize_text', 'sanitize_text_field' );
add_filter( 'of_sanitize_text', '\WBF\modules\options\custom_sanitize_text' );

function module_init(){
    add_action( 'init', '\WBF\modules\options\optionsframework_init', 20 );
	//Bind to Theme Customizer
	//add_action( 'customize_register','\WBF\modules\options\of_customizer_register' );
}

function optionsframework_init() {
    // Instantiate the main plugin class.
    $options_framework = new Framework;
    $options_framework->init();

    // Instantiate the options page.
    $options_framework_admin = new Admin;  //[WABOOT MOD]
    $options_framework_admin->init();

    // Instantiate the media uploader class
    $options_framework_media_uploader = new MediaUploader; //[WABOOT MOD]
    $options_framework_media_uploader->init();

    // Instantiate the code editor class [WABOOT MOD]
    $options_framework_waboot_code_editor = new CodeEditor;
    $options_framework_waboot_code_editor->init();

    // Instantiate the gfont selector class [WABOOT MOD]
    $options_framework_waboot_gfont_selector = new FontSelector;
    $options_framework_waboot_gfont_selector->init();
}

/**
 * Helper function to return the theme option value.
 * If no value has been saved, it returns $default.
 * Needed because options are saved as serialized strings.
 *
 * Not in a class to support backwards compatibility in themes.
 */
function of_get_option( $name, $default = false ) {
    static $config = '';
	static $options_in_file = array();
	static $options = array();

	if(!is_array($config)) $config = get_option( 'optionsframework' );

    //[WABOOT MOD] Tries to return the default value sets into $options array if $default is false
    if(!$default){
	    if(empty($options_in_file)) $options_in_file = Framework::get_registered_options();
        foreach($options_in_file as $opt){
            if(isset($opt['id']) && $opt['id'] == $name){
                if(isset($opt['std'])){
                    $default = $opt['std'];
                }
            }
        }
    }

    if(!isset($config['id'])){
        return $default;
    }

    if(empty($options)) $options = get_option( $config['id'] );

    if ( isset( $options[$name] ) ) {
        return $options[$name];
    }

    return $default;
}

function of_customizer_register($wp_customize){
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

			$unsupported_types = ['info','upload','typography','multicheck','csseditor'];
			$equivalent_types = [
				'color' => 'text',
				'images' => 'select'
			];

			if(in_array($opt['type'],$unsupported_types)) continue;

			$wp_customize->add_setting("theme_options[{$opt['id']}]",[
				'type' => 'theme_mod',
				'capability' => 'manage_options',
				'default' => isset($opt['std']) ? $opt['std'] : "",
				'transport' => 'refresh',
				'sanitize_callback' => '',
				'sanitize_js_callback' => ''
			]);

			//Detect control type and choices
			$type = "";
			$choices = [];
			switch($opt['type']){
				case "images":
					$type = $equivalent_types[$opt['type']];
					$choices = call_user_func(function() use($opt){
						$choices = [];
						foreach($opt['options'] as $k => $v){
							$choices[$k] = $v['label'];
						}
						return $choices;
					});
					break;
				case "color":
					$type = $equivalent_types[$opt['type']];
					break;
				case "select":
					$type = "select";
					$choices = $opt['options'];
					break;
				default:
					$type = $opt['type'];
					break;
			}

			$args = [
				'type' => $type,
				'priority' => 10,
				'section' => $current_section,
				'label' => $opt['name'],
				'description' => isset($opt['desc']) ? $opt['desc'] : "",
			];

			if(isset($choices) && !empty($choices)){
				$args['choices'] = $choices;
			}

			$wp_customize->add_control("theme_options[{$opt['id']}]",$args);
		}
	}
}