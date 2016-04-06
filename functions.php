<?php

if(!function_exists("waboot_init")):
	/**
	 * Initialize Waboot
	 */
	function waboot_init(){
		$waboot_includes = [
			'inc/template-functions.php',
			'inc/template-tags.php',
			'inc/Waboot.php'
		];

		//Require mandatory files
		foreach ($waboot_includes as $file) {
			if (!$filepath = locate_template($file)) {
				trigger_error(sprintf(__('Error locating %s for inclusion', 'waboot'), $file), E_USER_ERROR);
			}
			require_once $filepath;
		}
		unset($file, $filepath);

		//Init
		\Waboot\Theme::getInstance();
	}
	waboot_init();
endif;