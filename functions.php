<?php

/*****************************
 * FRAMEWORK INITIALIZATION
 *****************************/

if(!class_exists("WBF")):
	$wbfpath = locate_template('/wbf/wbf.php');

	if($wbfpath){
		require_once $wbfpath;
	}else{
		trigger_error(sprintf(__('Error locating %s for inclusion', 'waboot'), $file), E_USER_ERROR);
	}
endif;

/*****************************
 * WABOOT INITIALIZATION
 *****************************/

$waboot_includes = array(
	'inc/backup-functions.php',
	'inc/template-tags.php',
	'inc/init.php',
	'inc/hooks.php',
	'inc/shortcodes.php',
	'inc/widgets.php',
	'inc/stylesheets.php',
	'inc/scripts.php',
	'inc/jetpack.php',
	'inc/woocommerce.php',
);

foreach ($waboot_includes as $file) {
	if (!$filepath = locate_template($file)) {
		trigger_error(sprintf(__('Error locating %s for inclusion', 'waboot'), $file), E_USER_ERROR);
	}
	require_once $filepath;
}
unset($file, $filepath);

/*****************************
 * UTILS
 *****************************/

function wbft_wbf_in_use(){
	return class_exists("WBF");
}