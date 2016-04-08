<?php

/**
 * Returns an instance of Theme
 * 
 * @return \Waboot\Theme
 */
function Waboot(){
	return \Waboot\Theme::getInstance();
}

if(!function_exists("wb_get_option") && function_exists('\WBF\modules\options\of_get_option')):
	/**
	 * Wrapper for \WBF\modules\options\of_get_option
	 *
	 * @param $name
	 * @param bool $default
	 *
	 * @return bool|mixed
	 */
	function wb_get_option($name, $default = false){
		if(class_exists("WBF")){
			return \WBF\modules\options\of_get_option($name,$default);
		}else{
			return $default;
		}
	}
endif;