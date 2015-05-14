<?php

namespace WBF\modules\options;

class Framework extends \Options_Framework {
    /**
     * Wrapper for optionsframework_options()
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

            \WBF\modules\components\ComponentsManager::addRegisteredComponentOptions(); //todo: maybe use the filter instead?

            // Allow setting/manipulating options via filters
            $options = apply_filters( 'of_options', $options );
        }

        return $options;
    }

	static function set_option_value($id,$value){
		global $wp_settings_errors;
		$bak_settings_errors = get_settings_errors();

		$options = get_option(self::get_options_root_id());
		if(isset($options[$id])){
			$options[$id] = $value;
		}

		//Remove actions and settings errors
		remove_action( "updated_option", '\WBF\modules\options\of_options_save', 9999, 3 );
		$wp_settings_errors = array();

		$result = update_option(self::get_options_root_id(),$options); //update...

		//Readd...
		add_action( "updated_option", '\WBF\modules\options\of_options_save', 9999, 3 );
		$wp_settings_errors = $bak_settings_errors;

		return $result;
	}

	static function get_option_object($id){
		$all_options = self::_optionsframework_options();
		foreach($all_options as $opt){
			if(isset($opt['id']) && $opt['id'] == $id){
				return $opt;
			}
		}
		return false;
	}

	static function get_options_root_id(){
		$opt_name = get_option('optionsframework');
		if(isset($opt_name['id'])){
			return $opt_name['id'];
		}
		return false;
	}
}