<?php

if(!function_exists("waboot_wbf_customizations")):
	/**
	 * Applies some cusotmizations to WBF
	 */
	function waboot_wbf_customizations(){
		add_filter("wbf/modules/options/organizer/sections","waboot_reorder_theme_options",10,2);
		add_filter("wbf/admin_menu/label","waboot_wbf_admin_menu_label");
		add_filter("wbf/modules/options/theme_options_input_file_location/main","waboot_wbf_set_tof_loc");
		add_filter("wbf/modules/options/theme_options_output_file_name","waboot_wbf_set_tof_name", 10, 2);
	}
	add_action("after_setup_theme","waboot_wbf_customizations");
endif;

if(!function_exists( 'waboot_setup')):
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

		if(wbft_wbf_in_use()){
			init_style_compiler();
		}

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
 * Init styles compiler
 */
if(!function_exists('init_style_compiler') && wbft_wbf_in_use()) :
	function init_style_compiler(){
		$theme = waboot_get_compiled_stylesheet_name();
		$inputFileName = is_child_theme() ? "waboot-child" : "waboot";
		$output_dir = waboot_get_compiled_stylesheet_directory();
		$output_uri = waboot_get_compiled_stylesheet_uri();

		$compiler_args = [
			"sets" => [
				"theme_frontend" => [
					"input" => get_stylesheet_directory()."/assets/src/less/{$inputFileName}.less",
					"output" => $output_dir."/{$theme}.css",
					"map" => $output_dir."/{$theme}.css.map",
					"map_url" => $output_uri."/{$theme}.css.map",
					"cache" => get_stylesheet_directory()."/assets/cache",
					"import_url" => get_stylesheet_directory_uri(),
					"primary" => true
				]
			],
			"sources_path" => get_stylesheet_directory()."/assets/src/less/"
		];

		WBF()->set_styles_compiler($compiler_args);

		//Run a compilation if the styles file is not present
		global $wbf_styles_compiler;
		$sets = $wbf_styles_compiler->get_compile_sets();
		if(!is_file($sets['theme_frontend']['output'])){
			$wbf_styles_compiler->compile();
		}
	}
endif;

/**
 * Set the pagebuilder
 */
if(!function_exists("theme_get_pagebuilder")):
	function theme_get_pagebuilder(){
		return "bootstrap";
	}
endif;

/**
 * Reorder theme options
 *
 * @hooked 'wbf/modules/options/organizer/sections'
 *
 * @param array $sections
 * @param \WBF\modules\options\Organizer $organizer
 *
 * @return array
 */
function waboot_reorder_theme_options($sections,$organizer){
	if(isset($sections['behaviors'])){
		$bh = ["behaviors" => $sections['behaviors']];
		unset($sections['behaviors']);
		$sections = \WBF\includes\Utilities::associative_array_add_element_after($bh,"blog",$sections);
	}
	return $sections;
}

/**
 * Rename the default label of admin menu
 *
 * @param $label
 *
 * @hooked 'wbf/admin_menu/label'
 *
 * @return string
 */
function waboot_wbf_admin_menu_label($label){
	return "Waboot";
}

/**
 * Set the directory of _theme_options_generated.cmp file
 *
 * @hooked "wbf/modules/options/theme_options_input_file_location/main"
 *
 * @return string
 */
function waboot_wbf_set_tof_loc(){
	return get_template_directory()."/assets/src/less/_theme-options-generated.less.cmp";
}

/**
 * Set the name of theme options styles output file
 * @hooked "wbf/modules/options/theme_options_output_file_name"
 *
 * @return string
 */
function waboot_wbf_set_tof_name($filename,$extension){
	return "theme-options-generated";
}