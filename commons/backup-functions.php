<?php

if(!function_exists("wbft_breadcrumb_trail")):
	/**
	 * Backward compatibility for wbf_breadcrumb_trail
	 * @param array $args
	 */
	function wbft_breadcrumb_trail( $args = array() ){
		if(function_exists("wbf_breadcrumb_trail")){
			wbf_breadcrumb_trail($args);
		}
	}
endif;

if(!function_exists("get_behavior")):
	/**
	 * Behaviors framework backup functions; handles the case in which the Behaviors are not loaded
	 *
	 * @param $name
	 * @param int $post_id
	 * @param string $return
	 *
	 * @return array|bool|mixed|string
	 */
	function get_behavior( $name, $post_id = 0, $return = "value" ) {
	    if (class_exists('\WBF\modules\behaviors\BehaviorsManager')) {
	        return \WBF\modules\behaviors\get_behavior( $name, $post_id, $return = "value" ); //call the behavior framework function
	    }elseif(class_exists("WBF") && function_exists("WBF::get_behavior")){
		    WBF::get_behavior( $name, $post_id, $return = "value" ); //call the backup function
	    }
	    else {
		    if ( $post_id == 0 ) {
			    global $post;
			    $post_id = $post->ID;
		    }
		    $b = get_post_meta( "_behavior_" . $post_id, $name, true );
		    if(!isset($b) || (is_bool($b) && $b == false)){
			    $config = get_option( 'optionsframework' );
			    $b = of_get_option( $config['id'] . "_behavior_" . $name );
		    }
		    return $b;
	    }
	}
endif;

if(!function_exists("of_get_option")):
	/**
	 * \WBF\modules\options\of_get_option wrapper function
	 * @param $name
	 * @param bool $default
	 * @return \WBF\modules\options\of_get_option output
	 */
	function of_get_option($name, $default = false){
		if(function_exists('\WBF\modules\options\of_get_option')){
			return \WBF\modules\options\of_get_option($name,$default);
		}else{
			return $default;
		}
	}
endif;

if(!function_exists("component_is_loaded")):
	/**
	 *
	 * @param $name
	 * @return bool
	 */
	function component_is_loaded($name){
		if(class_exists('\WBF\modules\components\ComponentsManager')) {
			return \WBF\modules\components\ComponentsManager::is_loaded_by_name($name);
		}
		return false;
	}
endif;

if(!function_exists("wbf_locate_template_uri")):
	/**
	 * Retrieve the URI of the highest priority template file that exists.
	 *
	 * Searches in the stylesheet directory before the template directory so themes
	 * which inherit from a parent theme can just override one file.
	 *
	 * @param string|array $template_names Template file(s) to search for, in order.
	 * @return string The URI of the file if one is located.
	 */
	function wbf_locate_template_uri($template_names){
		$located = '';
		foreach ((array)$template_names as $template_name) {
			if (!$template_name)
				continue;

			if (file_exists(get_stylesheet_directory() . '/' . $template_name)) {
				$located = get_stylesheet_directory_uri() . '/' . $template_name;
				break;
			} else if (file_exists(get_template_directory() . '/' . $template_name)) {
				$located = get_template_directory_uri() . '/' . $template_name;
				break;
			}
		}
		return $located;
	}
endif;