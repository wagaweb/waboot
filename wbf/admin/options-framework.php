<?php
/**
 * Options Framework WBF Edition
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) die;

if ( STYLESHEETPATH == TEMPLATEPATH ) {
	define('OPTIONS_FRAMEWORK_URL', TEMPLATEPATH . '/wbf/vendor/options-framework/');
	define('OPTIONS_FRAMEWORK_DIRECTORY', get_bloginfo('template_directory') . '/wbf/vendor/options-framework/');
} else {
	define('OPTIONS_FRAMEWORK_URL', STYLESHEETPATH . '/wbf/vendor/options-framework/');
	define('OPTIONS_FRAMEWORK_DIRECTORY', get_bloginfo('template_directory') . '/wbf/vendor/options-framework/');
}

add_action( 'init', 'optionsframework_init', 20 );
add_action( "updated_option", "of_options_save", 9999, 3 );

function optionsframework_init() {
	require WBF_DIRECTORY . '/vendor/options-framework/class-options-sanitization.php';
	require "waboot-options-sanitization.php";

	// Instantiate the main plugin class.
	$options_framework = new Waboot_Options_Framework;
	$options_framework->init();

	// Instantiate the options page.
	$options_framework_admin = new Waboot_Options_Framework_Admin;  //[WABOOT MOD]
	$options_framework_admin->init();

	// Instantiate the media uploader class
    $options_framework_media_uploader = new Waboot_Options_Media_Uploader; //[WABOOT MOD]
	$options_framework_media_uploader->init();

	// Instantiate the code editor class [WABOOT MOD]
    $options_framework_waboot_code_editor = new Waboot_Options_Code_Editor;
	$options_framework_waboot_code_editor->init();

	// Instantiate the gfont selector class [WABOOT MOD]
	$options_framework_waboot_gfont_selector = new Waboot_Options_Font_Selector;
	$options_framework_waboot_gfont_selector->init();
}

if ( ! function_exists( 'of_get_option' ) ) :
	/**
	 * Helper function to return the theme option value.
	 * If no value has been saved, it returns $default.
	 * Needed because options are saved as serialized strings.
	 *
	 * Not in a class to support backwards compatibility in themes.
	 */
	function of_get_option( $name, $default = false ) {
		$config = get_option( 'optionsframework' );

		//[WABOOT MOD] Tries to return the default value sets into $options array if $default is false
		if(!$default){
			$options = Waboot_Options_Framework::_optionsframework_options();
			foreach($options as $opt){
				if(isset($opt['id']) && $opt['id'] == $name){
					if(isset($opt['std'])){
						$default = $opt['std'];
					}
				}
			}
		}

		if ( ! isset( $config['id'] ) ) {
			return $default;
		}

		$options = get_option( $config['id'] );

		if ( isset( $options[$name] ) ) {
			return $options[$name];
		}

		return $default;
	}
endif;

function of_options_save($option, $old_value, $value){
	$config = get_option( 'optionsframework' );
	if($option == $config['id']){
		$must_recompile_flag = false;
		$deps_to_achieve = array();
		$all_options = Waboot_Options_Framework::_optionsframework_options();
		$diff = array_diff_assoc($old_value,$value);

		//Doing actions with modified options
		foreach($all_options as $k => $opt_data){
			if(isset($opt_data['id']) && array_key_exists($opt_data['id'],$diff)){ //True if the current option has been modified
				if(isset($opt_data['recompile_styles']) && $opt_data['recompile_styles']){
					$must_recompile_flag = true;
				}
				/*if(isset($opt_data['deps'])){
					if(isset($opt_data['deps']['_global'])){
						if($opt_data['deps']['_global']['components'])
							$deps_to_achieve['components'][] = $opt_data['deps']['_global']['components'];
					}
				}*/
			}
		}

		if($must_recompile_flag){
			_of_generate_less_file($value); //Create a _theme-options-generated.less file
			//Then, compile less
			if(isset($GLOBALS['waboot_styles_compiler'])){
				global $waboot_styles_compiler;
				$waboot_styles_compiler->compile();
			}
		}
	}
}

/**
 * Replace {of_get_option} and {of_get_font} tags in _theme-options-generated.less.cmp; It is called during "update_option" and only for of theme options.
 * @param $value values of the options
 */
function _of_generate_less_file($value){
	$tmpFile = new SplFileInfo(get_stylesheet_directory()."/sources/less/_theme-options-generated.less.cmp");
	if(!$tmpFile->isFile() || !$tmpFile->isWritable()){
		$tmpFile = new SplFileInfo(get_template_directory()."/sources/less/_theme-options-generated.less.cmp");
	}
	$parsedFile = new SplFileInfo(get_stylesheet_directory()."/sources/less/theme-options-generated.less");
	if($tmpFile->isFile() && $tmpFile->isWritable()) {
		$genericOptionfindRegExp = "~//{of_get_option\('([a-zA-Z0-9\-_]+)'\)}~";
		$fontOptionfindRegExp    = "~//{of_get_font\('([a-zA-Z0-9\-_]+)'\)}~";

		$tmpFileObj    = $tmpFile->openFile( "r" );
		$parsedFileObj = $parsedFile->openFile( "w+" );

		while ( ! $tmpFileObj->eof() ) {
			$line = $tmpFileObj->fgets();
			//Replace a generic of option
			if ( preg_match( $genericOptionfindRegExp, $line, $matches ) ) {
				if ( array_key_exists( $matches[1], $value ) ) {
					if ( $value[ $matches[1] ] != "" ) {
						$line = preg_replace( $genericOptionfindRegExp, $value[ $matches[1] ], $line );
					} else {
						$line = "//{$matches[1]} is empty\n";
					}
				} else {
					$line = "//{$matches[1]} not found\n";
				}
			}
			//Replace a font option
			if ( preg_match( $fontOptionfindRegExp, $line, $matches ) ) {
				$line = "//{$matches[1]} is empty\n";
				if ( array_key_exists( $matches[1], $value ) ) {
					if ( $value[ $matches[1] ] != "" ) {
						$attr       = $value[ $matches[1] ];
						$fontString = "font-family: '" . $attr['family'] . "', " . $attr['category'] . ";";
						/*if(preg_match("/([0-9]+)([a-z]+)/",$attr['style'],$style_matches)){
							if($style_matches[1] == 'regular') $style_matches[1] = "normal";
							$fontString .= "font-weight: ".$style_matches[1].";";
							$fontString .= "font-style: ".$style_matches[2].";";
						}else{
							if($attr['style'] == 'regular') $attr['style'] = "normal";
							$fontString .= "font-weight: ".$attr['style'].";";
						}*/
						$fontString .= "color: " . $attr['color'] . ";";
						$line = $fontString;
					} else {
						$line = "//{$matches[1]} is empty\n";
					}
				} else {
					$line = "//{$matches[1]} not found\n";
				}
			}
			$parsedFileObj->fwrite( $line );
		}
	}
}

/**
 * Check if current admin page is the options framework page
 * @param $hook
 * @return bool
 */
function of_is_admin_framework_page($hook){
	$menu = Waboot_Options_Framework_Admin::menu_settings();
	if ( $hook == 'waboot_page_' . $menu['old_menu_slug'] || $hook == 'toplevel_page_' . $menu['menu_slug']) {
		return true;
	}
	return false;
}

/**
 * Takes an array of options and returns the values themselves and the default value
 * @usage
 *
 * A typical array should be like this:
 *
 * array(
 *       array(
 *           "name" => __("Full width. No sidebar.","waboot"),
 *           "value" => "full-width"
 *       ),
 *       array(
 *           "name" => __("Sidebar right","waboot"),
 *           "value" => "sidebar-right"
 *       ),
 *       array(
 *           "name" => __("Sidebar left","waboot"),
 *           "value" => "sidebar-left"
 *       ),
 *       '_default' => 'sidebar-right'
 * )
 *
 * OR (more general):
 *
 * array(
 *       'opt1'
 *       'opt2,
 *       'opt2,
 *       '_default' => 'opt1'
 * )
 *
 * IF '_default' is not set or does not exists in the array, the function returns the first value (ore the 'value' field of the first key)
 *
 * @param $values
 * @return array
 */
function of_add_default_key($values){
    $default = false;

    if(isset($values['_default'])){
        if(array_key_exists($values['_default'],$values)){
            $default = $values['_default'];
        }else{
            foreach($values as $v){
                if(is_array($v)){
                    if($v['value'] == $values['_default']){
                        $default = $values['_default'];
                    }
                }
            }
        }
    }
    if(!isset($values['_default']) || $default == false){
        reset($values);
        $default = key($values);
        if(is_array($values[$default])){
            $default = $values[$default]['value'];
        }
    }
    if(isset($values['_default'])) unset($values['_default']);

    return array(
        'values' => $values,
        'default' => $default
    );
}