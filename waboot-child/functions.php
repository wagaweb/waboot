<?php

/*------------------------------------------------------------------------------------------*/
/* Start Custom Functions - Please edit this section only if you know what you'are doing :) */
/*------------------------------------------------------------------------------------------*/

/**
 * Theme init function. PLEASE EDIT "mytheme" SUFFIX both here and in add_action below.
 */
function mytheme_init(){
	/**
	 * Make theme available for translation
	 * Translations can be filed in the /languages/ directory
	 * If you're building a theme based on waboot, use a find and replace
	 * to change 'waboot' to the name of your theme in all the template files
	 */
	load_child_theme_textdomain( 'mytheme', get_stylesheet_directory() . '/languages' );
}
add_action("after_setup_theme","mytheme_init");

//For older themes:
//require_once "inc/legacy_compatibility.php";