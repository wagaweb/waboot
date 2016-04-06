<?php

if(!function_exists("wbft_wbf_in_use")):
	/**
	 * Checks if WBF is loaded
	 *
	 * @return bool
	 */
	function wbft_wbf_in_use(){
		return class_exists("WBF");
	}
endif;

if(!function_exists("wbft_ajax_out")):
	/**
	 * Prints out a var in ajax environment
	 *
	 * @param $var
	 */
	function wbft_ajax_out($var){
		if(!defined("DOING_AJAX") || !DOING_AJAX) return;
		if(is_array($var)){
			echo json_encode($var);
		}else{
			echo $var;
		}
		die;
	}
endif;

if(!function_exists("wbft_associative_array_search")):
	/**
	 * Search $array for the $key=>$value pair.
	 *
	 * @param array $array the target array
	 * @param mixed $key the key to find
	 * @param mixed $value the value to find into the $key
	 *
	 * @return array with the found pairs, or empty.
	 */
	function wbft_associative_array_search($array,$key,$value){
		$search_r = function($array, $key, $value, &$results, $subarray_key = null) use(&$search_r){
			if (!is_array($array)) {
				return;
			}

			if (isset($array[$key]) && $array[$key] == $value) {
				if(isset($subarray_key))
					$results[$subarray_key] = $array;
				else
					$results[] = $array;
			}

			foreach ($array as $k => $subarray) {
				$search_r($subarray, $key, $value, $results, $k);
			}
		};
		$results = array();
		$search_r($array, $key, $value, $results);
		return $results;
	}
endif;