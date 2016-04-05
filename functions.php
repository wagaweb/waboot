<?php

/*****************************
 * WABOOT INITIALIZATION
 *****************************/

if(!function_exists("waboot_init")):
	/**
	 * Requires the Waboot files.
	 */
	function waboot_init(){
		$waboot_includes = array(
			'commons/commons.php',
			'inc/template-functions.php',
			'inc/template-tags.php',
			'inc/behaviors.php',
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
	}
	waboot_init();
endif;