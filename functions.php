<?php

waboot_init();

/**
 * Initialize Waboot
 */
function waboot_init(){
	$waboot_includes = [
		'inc/template-functions.php',
		'inc/template-tags.php',
		'inc/postformat-helpers.php',
		'inc/terms-tags.php',
		'inc/template-rendering.php',
		'inc/Layout.php',
		'inc/Theme.php',
		'inc/woocommerce/bootstrap.php',
		'inc/hooks/stylesheets.php',
		'inc/hooks/scripts.php',
		'inc/hooks/generators.php',
	];

	//Require mandatory files
	foreach($waboot_includes as $file){
		$filepath = locate_template($file);
		if(!$filepath) {
			trigger_error(sprintf(__('Error locating %s for inclusion', 'waboot'), $file), E_USER_ERROR);
		}
		require_once $filepath;
	}
	unset($file, $filepath);

	if(!\Waboot\functions\wbf_exists()){
		add_action("init",function(){
			if(!is_admin()){
				_e( "Waboot theme requires WBF Framework to work properly, please install.", 'Waboot' );
			}
		});
		if(!\Waboot\Theme::is_wizard_done() || !\Waboot\Theme::is_wizard_skipped()){
			add_action('admin_notices',function(){
				$class = 'notice notice-error';
				$message = sprintf(
					__( "Waboot theme requires <a href='%s'>WBF Framework</a> plugin to work properly, please install.", 'Waboot' ),
					'http://update.waboot.org/resource/get/plugin/wbf'
				);
				printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
			});
		}
	}

	if(!class_exists("\\Waboot\\Theme") || !\Waboot\functions\wbf_exists()){
		if(!is_admin() && !wp_doing_ajax()){
			trigger_error("Waboot was not initialized. Missing WBF?", E_USER_NOTICE);
		}
		return; //Stop here if WBF is not present
	}

	//Init hooks
	$wb = Waboot()->load_hooks()->load_dependencies();

	//Build up the theme
	$wb->layout->create_zone("header",false,["always_load"=>true]);
	$wb->layout->create_zone("page-before",false);
	$wb->layout->create_zone("main-top",new \WBF\components\mvc\HTMLView("templates/main-top.php"));
	$wb->layout->create_zone("aside-primary",new \WBF\components\mvc\HTMLView("templates/aside.php"),["can_render_callback" => function(){
		//Callback called to decide whether print out the zone or not
		return \Waboot\functions\body_layout_has_sidebar();
	}]);
	$wb->layout->create_zone("content",false,["always_load"=>true]);
	$wb->layout->create_zone("aside-secondary",new \WBF\components\mvc\HTMLView("templates/aside.php"),["can_render_callback" => function(){
		//Callback called to decide whether print out the zone or not
		return \Waboot\functions\body_layout_has_two_sidebars();
	}]);
	$wb->layout->create_zone("main-bottom",new \WBF\components\mvc\HTMLView("templates/main-bottom.php"));
	$wb->layout->create_zone("page-after",false);
	$wb->layout->create_zone("footer",false,["always_load"=>true]);

	//Loads std hooks
	$zone_std_hooks_file = locate_template("inc/hooks/zones_std_hooks.php");
	if($zone_std_hooks_file){
		require_once $zone_std_hooks_file;
	}else{
		trigger_error(sprintf(__('Error locating %s for inclusion', 'waboot'), $zone_std_hooks_file), E_USER_ERROR);
	}
}

/**
 * Returns an instance of Theme
 *
 * @return \Waboot\Theme|boolean
 */
function Waboot(){
	if(class_exists("\\Waboot\\Theme")){
		return \Waboot\Theme::getInstance();
	}else{
		trigger_error("Unable to find \Waboot\Theme class", E_USER_NOTICE);
		return false;
	}
}