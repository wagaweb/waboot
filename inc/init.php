<?php

if ( ! function_exists( 'waboot_setup' ) ):
	function waboot_setup() {
		//Make theme available for translation.
		load_theme_textdomain( 'waboot', get_template_directory() . '/languages' );

		// Switch default core markup for search form, comment form, and comments to output valid HTML5.
		add_theme_support( 'html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption') );

		// Add default posts and comments RSS feed links to head
		add_theme_support( 'automatic-feed-links' );

		// Add support for custom backgrounds
		add_theme_support( 'custom-background', array('default-color' => 'ffffff') );

		// Add support for post-thumbnails
		add_theme_support( 'post-thumbnails' );

		// Add support for post formats. To be styled in later release.
		add_theme_support( 'post-formats', array( 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' ) );

		/**
		 * Register the navigation menus. This theme uses wp_nav_menu() in three locations.
		 */
		register_nav_menus( array(
			'top'           => __( 'Top Menu', 'waboot' ),
			'main'          => __( 'Main Menu', 'waboot' ),
			'bottom'        => __( 'Bottom Menu', 'waboot' )
		) );

		init_style_compiler();

		//Install the contact form table
		call_user_func(function(){
			global $wpdb;

			$table_name = $wpdb->prefix . "wbp_mails";
			$charset_collate = $wpdb->get_charset_collate();

			$sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix."wb_mails"."`(
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`subject` text NOT NULL,
				`content` text NOT NULL,
				`recipient` varchar(255) NOT NULL,
				`sender_mail` varchar(255) NOT NULL,
				`sender_info` text NOT NULL,
				`sourceid` int(11) NOT NULL,
				`date_created` date NOT NULL,
				`status` int(1) NOT NULL,
				PRIMARY KEY id (`id`)
			)$charset_collate;";

			$wpdb->query($sql);
		});
	}
endif;
add_action('after_setup_theme', 'waboot_setup', 11);

/**
 * INIT STYLES COMPILER
 */
if ( ! function_exists( 'init_style_compiler' ) ) :
	function init_style_compiler(){
		$theme = waboot_get_compiled_stylesheet_name();
		$inputFileName = is_child_theme() ? "waboot-child" : "waboot";

		WBF::set_styles_compiler([
			"sets" => [
				"theme_frontend" => [
					"input" => get_stylesheet_directory()."/sources/less/{$inputFileName}.less",
					"output" => get_stylesheet_directory()."/assets/css/{$theme}.css",
					"map" => get_stylesheet_directory()."/assets/css/{$theme}.css.map",
					"map_url" => get_stylesheet_directory_uri()."/assets/css/{$theme}.css.map",
					"cache" => get_stylesheet_directory()."/assets/cache",
					"import_url" => get_stylesheet_directory_uri()
				]
			],
			"sources_path" => get_stylesheet_directory()."/sources/less/"
		]);

		//Run a compilation if the styles file is not present
		global $wbf_styles_compiler;
		$sets = $wbf_styles_compiler->get_compile_sets();
		if(!is_file($sets['theme_frontend']['output'])){
			$wbf_styles_compiler->compile();
		}
	}
endif;

/**
 * Set update server
 */
if(class_exists('\WBF\includes\Theme_Update_Checker')){
	$GLOBALS['WBFThemeUpdateChecker'] = new \WBF\includes\Theme_Update_Checker(
		'waboot', //Theme slug. Usually the same as the name of its directory.
		'http://update.waboot.org/?action=get_metadata&slug=waboot' //Metadata URL.
	);
}

/**
 * Set the pagebuilder
 */
if(!function_exists("theme_get_pagebuilder")):
	function theme_get_pagebuilder(){
		return "bootstrap";
	}
endif;