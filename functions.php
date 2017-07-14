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
		'inc/woocommerce/bootstrap.php'
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

	if(!class_exists("WBF") && !defined('WBTEST_CURRENT_PATH')){
		add_action("init",function(){
			if(is_admin()){
				\Waboot\Theme::preload_generators_page();
			}else{
				_e( "Waboot theme requires WBF Framework to work properly, please install.", 'Waboot' );
			}
		});
		add_action("admin_notices", function(){
			\Waboot\Theme::preload_generators_page();
			if(!\Waboot\Theme::is_wizard_done()){
				$class = 'notice notice-error';
				$wizard_url = !class_exists("WBF") && !defined('WBTEST_CURRENT_PATH') ? admin_url('/tools.php?page=waboot_setup_wizard') : admin_url('/admin.php?page=waboot_setup_wizard');
				$message = sprintf(
					__( "Waboot theme is missing some requirements to work properly. You can run the <a href='%s'>Wizard</a> to take care of them.", 'Waboot' ),
					$wizard_url
				);
				printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
			}else{
				$class = 'notice notice-error';
				$message = sprintf(
					__( "Waboot theme requires <a href='%s'>WBF Framework</a> plugin to work properly, please install.", 'Waboot' ),
					'http://update.waboot.org/resource/get/plugin/wbf'
				);
				printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
			}
		});
	}

	//Init hooks
	$wb = Waboot()->load_hooks();

	if(!class_exists("\\Waboot\\Theme") || !class_exists('WBF')){
		if(!is_admin()){
			trigger_error("Waboot was not initialized. Missing WBF?", E_USER_NOTICE);
		}
		return;
	}

	locate_template('inc/Component.php',true);

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