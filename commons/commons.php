<?php

/*****************************
 * COMMONS INITIALIZATION
 *****************************/

call_user_func(function(){
	$wbft_includes = array(
		'utils.php',
		'backup-functions.php',
		'template-functions/terms-helpers.php',
		'template-functions.php',
		'template-tags.php',
		'hooks.php'
	);

	foreach ($wbft_includes as $file) {
		if (!$filepath = locate_template("commons/".$file)) {
			trigger_error(sprintf(__('Error locating %s for inclusion', 'waboot'), $file), E_USER_ERROR);
		}
		require_once $filepath;
	}
	unset($file, $filepath);
});