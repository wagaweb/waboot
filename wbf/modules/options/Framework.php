<?php

namespace WBF\modules\options;

class Framework extends \Options_Framework {

	/**
	 * Initialize the framework.
	 */
	public function init(){
		Framework::set_theme_option_default_root_id();
	}

	/**
	 * Sets defaults theme options root id
	 */
	static function set_theme_option_default_root_id() {
		// Load current theme
		$current_theme_name = wp_get_theme()->get_stylesheet();
		$current_theme_name = preg_replace("/\W/", "_", strtolower($current_theme_name));
		self::set_options_root_id($current_theme_name);
	}

	/**
	 * Get current registered theme options.
	 *
	 * @alias-of Framework::_optionsframework_options()
	 * @return array
	 */
	static function &get_registered_options(){
		return self::_optionsframework_options();
	}

    /**
     * Get current registered theme options.
     * The functions use the filter "options_framework_location" to determine options file existance and location, then try to call the function "optionsframework_options()".
     * At the end it calls the action "wbf/theme_options/register" and the filter "of_options" (with the current $options as parameter)
     *
     * Allows for manipulating or setting options via 'of_options' filter
     * For example:
     *
     * <code>
     * add_filter( 'of_options', function( $options ) {
     *     $options[] = array(
     *         'name' => 'Input Text Mini',
     *         'desc' => 'A mini text input field.',
     *         'id' => 'example_text_mini',
     *         'std' => 'Default',
     *         'class' => 'mini',
     *         'type' => 'text'
     *     );
     *
     *     return $options;
     * });
     * </code>
     *
     * Also allows for setting options via a return statement in the
     * options.php file.  For example (in options.php):
     *
     * <code>
     * return array(...);
     * </code>
     *
     * @return array (by reference)
     */
    static function &_optionsframework_options() {
        static $options = null;

        if ( !$options ) {
            // Load options from options.php file (if it exists)
            $location = apply_filters( 'options_framework_location', array('options.php') );
            if ( $optionsfile = locate_template( $location ) ) {
                $maybe_options = require_once $optionsfile;
                if ( is_array( $maybe_options ) ) {
                    $options = $maybe_options;
                } else if ( function_exists( 'optionsframework_options' ) ) {
                    $options = optionsframework_options();
                }
            }

	        do_action("wbf/theme_options/register"); //This action can hook different functions to of_options filter (is used by Component Manager for example)

            // Allow setting/manipulating options via filters
            $options = apply_filters( 'of_options', $options );
        }

        return $options;
    }

	/**
	 * Update theme options with new values (this will erase current theme options)
	 * @param $values
	 *
	 * @return bool
	 */
	static function update_theme_options($values){
		$id = self::get_options_root_id();
		return update_option($id,$values);
	}

	/**
	 * Set a new value for a specific theme option
	 * @param $id
	 * @param $value
	 *
	 * @return bool
	 */
	static function set_option_value($id,$value){
		global $wp_settings_errors;
		$bak_settings_errors = get_settings_errors();

		$options = Framework::get_options_values();
		if(isset($options[$id])){
			$options[$id] = $value;
		}else{
			$defaults = Framework::get_default_values();
			if(isset($defaults[$id])){
				$options[$id] = $value;
			}
		}

		//Remove actions and settings errors
		remove_action( "updated_option", '\WBF\modules\options\of_options_save', 9999);
		$wp_settings_errors = array();

		$result = update_option(self::get_options_root_id(),$options); //update...

		//Read...
		add_action( "updated_option", '\WBF\modules\options\of_options_save', 9999, 3 );
		$wp_settings_errors = $bak_settings_errors;

		return $result;
	}

	/**
	 * Get the option entity for the specified ID
	 * @param string $id
	 *
	 * @return array|false
	 */
	static function get_option_object($id){
		$all_options = self::get_registered_options();
		foreach($all_options as $opt){
			if(isset($opt['id']) && $opt['id'] == $id){
				return $opt;
			}
		}
		return false;
	}

	/**
	 * Get the "type" of the specified option ID
	 * @param string $id
	 *
	 * @return bool
	 */
	static function get_option_type($id){
		$option = self::get_option_object($id);
		if(isset($option['type']))
			return $option['type'];
		else
			return false;
	}

	/**
	 * Get if the specified option must recompile styles or not
	 * @param $id
	 *
	 * @return bool
	 */
	static function option_must_recompile_styles($id){
		$option = self::get_option_object($id);
		return isset($option['recompile_styles']) && $option['recompile_styles'];
	}

	/**
	 * Get the current options root id (the name of the option that contains the current valid options. Default to the current theme name)
	 * @return string|false
	 */
	static function get_options_root_id(){
		$opt_root = get_option('optionsframework');
		if(isset($opt_root['id'])){
			return $opt_root['id'];
		}
		return false;
	}

	static function set_options_root_id($id){
		$opt_root = get_option('optionsframework');
		if(!is_array($opt_root)) $opt_root = [];
		$opt_root['id'] = $id;
		update_option('optionsframework', $opt_root);
	}

	/**
	 * Get all currently valid options
	 * @return array|false
	 */
	static function get_options_values(){
		$opt_id = self::get_options_root_id();
		if($opt_id){
			$values = get_option($opt_id);
		}

		if(!isset($values) || !$values || empty($values)){
			//Returns the defaults
			$values = self::get_default_values();
		}

		if(is_array($values) && !empty($values)){
			return $values;
		}

		return false;
	}

	static function get_options_values_filtered(){
		$options = self::get_options_values();
		foreach($options as $k => $v){
			$options[$k] = apply_filters("wbf/theme_options/get/{$k}",$v);
		}
		return $options;
	}

	/**
	 * Get the default values for all the theme options
	 *
	 * Get an array of all default values as set in
	 * options.php. The 'id','std' and 'type' keys need
	 * to be defined in the configuration array. In the
	 * event that these keys are not present the option
	 * will not be included in this function's output.
	 *
	 * @return array Re-keyed options configuration array.
	 *
	 */
	static function get_default_values() {
		$output = array();
		$config = Framework::get_registered_options();
		foreach ( (array) $config as $option ) {
			if ( ! isset( $option['id'] ) ) {
				continue;
			}
			if ( ! isset( $option['std'] ) ) {
				continue;
			}
			if ( ! isset( $option['type'] ) ) {
				continue;
			}
			if ( has_filter( 'of_sanitize_' . $option['type'] ) ) {
				$output[$option['id']] = apply_filters( 'of_sanitize_' . $option['type'], $option['std'], $option );
			}
		}
		return $output;
	}

	/**
	 * Returns all theme options values of options with specified $suffix
	 * @param $suffix
	 *
	 * @return array
	 */
	static function get_options_values_by_suffix($suffix){
		$options = self::get_options_values();
		$results = [];
		foreach($options as $k => $v){
			if(preg_match("/^({$suffix})/",$k)){
				$results[$k] = $v;
			}
		}
		return $results;
	}
}