<?php
/**
 * Options Framework WBF Edition
 */

namespace WBF\modules\options;

require_once "CustomizerManager.php";
require_once "functions.php";
require_once \WBF::get_path()."vendor/options-framework/class-options-sanitization.php";
require_once "sanitization.php";

define('OPTIONS_FRAMEWORK_URL', \WBF::prefix_url('vendor/options-framework/'));
define('OPTIONS_FRAMEWORK_DIRECTORY', \WBF::prefix_url('vendor/options-framework/'));

add_action( "wbf_init",'\WBF\modules\options\module_init', 11 );
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
	CustomizerManager::init();
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

	//If there are no options for current theme, then add the defaults
	/*$values = Framework::get_options_values();
	if(!$values || empty($values)){
		$defaults = Framework::get_default_values();
		of_options_save(Framework::get_options_root_id(),[],$defaults);
		Framework::update_theme_options($defaults);
	}*/
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

	if(!is_array($config)) $config = Framework::get_options_root_id();

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

    if(!isset($config) || !$config){
        return $default;
    }

    if(empty($options)) $options = get_option( $config );

    if ( isset( $options[$name] ) ) {
	    $value = $options[$name];
    }else{
	    $value = $default;
    }

	$value = apply_filters("wbf/theme_options/get/{$name}",$value);

    return $value;
}