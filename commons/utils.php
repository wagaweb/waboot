<?php

/**
 * Checks if WBF is loaded
 *
 * @return bool
 */
function wbft_wbf_in_use(){
	return class_exists("WBF");
}

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