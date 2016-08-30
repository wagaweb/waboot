<?php

if(!class_exists("WBF") && !defined('WBTEST_CURRENT_PATH')){
	add_action("init",function(){
		if(is_admin()) return;
		_e( "Waboot theme requires WBF Framework to work properly, please install", 'Waboot' );
	});
	add_action("admin_notices", function(){
		$class = 'notice notice-error';
		$message = __( "Waboot theme requires WBF Framework to work properly, please install", 'Waboot' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
	});
	return;
}else{
	waboot_init();
}

/**
 * Initialize Waboot
 */
function waboot_init(){
	$waboot_includes = [
		'inc/template-functions.php',
		'inc/template-tags.php',
		'inc/terms-tags.php',
		'inc/template-rendering.php',
		'inc/Layout.php',
		'inc/Theme.php',
		'inc/Component.php'
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

	//Init
	$wb = Waboot()->load_hooks();

	//Build up the theme
	$wb->layout->create_zone("header",false,["always_load"=>true]);
	$wb->layout->create_zone("main-top",new \WBF\components\mvc\HTMLView("templates/main-top.php"));
	$wb->layout->create_zone("aside-primary",new \WBF\components\mvc\HTMLView("templates/aside.php"),["can_render_callback" => function(){
		$body_layout = \Waboot\functions\get_body_layout();
		if($body_layout == \Waboot\Layout::LAYOUT_PRIMARY_LEFT || \Waboot\functions\body_layout_has_two_sidebars()){
			return true;
		}
		return false;
	}]);
	$wb->layout->create_zone("content",new \WBF\components\mvc\HTMLView("templates/content.php"),["always_load"=>true]);
	$wb->layout->create_zone("aside-secondary",new \WBF\components\mvc\HTMLView("templates/aside.php"),["can_render_callback" => function(){
		$body_layout = \Waboot\functions\get_body_layout();
		if($body_layout == \Waboot\Layout::LAYOUT_PRIMARY_RIGHT || \Waboot\functions\body_layout_has_two_sidebars()){
			return true;
		}
		return false;
	}]);
	$wb->layout->create_zone("main-bottom",new \WBF\components\mvc\HTMLView("templates/main-bottom.php"));
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
 * @return \Waboot\Theme
 */
function Waboot(){
	return \Waboot\Theme::getInstance();
}