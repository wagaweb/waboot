<?php
/*
 * Loads the Options Panel
 */
if ( !function_exists( 'optionsframework_init' ) ) {

	/* Set the file path based on whether we're in a child theme or parent theme */

	if ( STYLESHEETPATH == TEMPLATEPATH ) {
        define('OPTIONS_FRAMEWORK_URL', TEMPLATEPATH . '/wbf/vendor/options-framework/');
        define('OPTIONS_FRAMEWORK_DIRECTORY', get_bloginfo('template_directory') . '/wbf/vendor/options-framework/');
	} else {
        define('OPTIONS_FRAMEWORK_URL', STYLESHEETPATH . '/wbf/vendor/options-framework/');
        define('OPTIONS_FRAMEWORK_DIRECTORY', get_bloginfo('template_directory') . '/wbf/vendor/options-framework/');
	}

    require_once(get_template_directory() . '/wbf/admin/options-framework.php');
}
