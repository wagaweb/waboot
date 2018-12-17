<?php

define('WBF_MIN_VER', '1.1.2');

waboot_init();

/**
 * Initialize Waboot
 */
function waboot_init(){
	add_theme_support('wbf');

	try{
		require_once 'inc/template-functions.php';

		//Loads dependencies
		$main_includes = [
			'inc/WBFNotFoundException.php',
			'inc/WBFVersionException.php',
			'inc/template-tags.php',
			'inc/Theme.php',
			'inc/Layout.php',
			'inc/hooks/wbf_installer.php',
		];

		\Waboot\functions\safe_require_files($main_includes);

		//Check prerequisites
		\Waboot\functions\check_prerequisites();

		//Loads dependencies
		$waboot_includes = [
			'inc/WBFNotFoundException.php',
			'inc/WBFVersionException.php',
			'inc/migrations/migration-2.3.3-3.0.0.php',
			'inc/components-functions.php',
			'inc/postformat-helpers.php',
			'inc/terms-tags.php',
			'inc/template-rendering.php',
			'inc/Component.php'
		];

		\Waboot\functions\safe_require_files($waboot_includes);

		//Init hooks
		Waboot()->load_hooks()->load_extensions();

		//Build up the theme
		WabootLayout()->create_zone("header",false,["always_load"=>false]);
		WabootLayout()->create_zone("page-before",false);
		WabootLayout()->create_zone("main-top",new \WBF\components\mvc\HTMLView("templates/zones/main-top.php"));
		WabootLayout()->create_zone("aside-primary",new \WBF\components\mvc\HTMLView("templates/zones/aside.php"),["can_render_callback" => function(){
			//Callback called to decide whether print out the zone or not
			return \Waboot\functions\body_layout_has_sidebar();
		}]);
		WabootLayout()->create_zone("content",false,["always_load"=>true]);
		WabootLayout()->create_zone("aside-secondary",new \WBF\components\mvc\HTMLView("templates/zones/aside.php"),["can_render_callback" => function(){
			//Callback called to decide whether print out the zone or not
			return \Waboot\functions\body_layout_has_two_sidebars();
		}]);
		WabootLayout()->create_zone("main-bottom",new \WBF\components\mvc\HTMLView("templates/zones/main-bottom.php"));
		WabootLayout()->create_zone("page-after",false);
		WabootLayout()->create_zone("footer",false,["always_load"=>false]);

		//Loads standard hooks
		$zone_std_hooks_file = locate_template("inc/hooks/zones_std_hooks.php");
		if($zone_std_hooks_file){
			require_once $zone_std_hooks_file;
		}else{
			throw new \Exception(sprintf(__('Error locating %s for inclusion', 'waboot'), $zone_std_hooks_file));
		}
	} catch ( \Exception $e ) {
		if($e instanceof \Waboot\exceptions\WBFNotFoundException || $e instanceof \Waboot\exception\WBFVersionException){
			global $pagenow;
			if(!is_admin() && $pagenow !== 'wp-login.php'){
				add_action("init",function() use($e){
					echo $e->getMessage();
				});
			}
			$e->setup_wbf_installer();
			return;
		}else{
			trigger_error($e->getMessage(), E_USER_ERROR);
		}
	}
}

/**
 * Returns an instance of Theme
 *
 * @return \Waboot\Theme|boolean
 */
function Waboot(){
	static $waboot = null;
	if(isset($waboot) && $waboot instanceof \Waboot\Theme) return $waboot;
	if(class_exists(\Waboot\Theme::class) && class_exists(\Waboot\Layout::class) && class_exists(WP_Styles::class)){
		$waboot = new Waboot\Theme(new \Waboot\Layout(),new \WP_Styles());
		return $waboot;
	}else{
		trigger_error("Unable to find \Waboot\Theme class", E_USER_NOTICE);
		return false;
	}
}

/**
 * Returns Theme Layout() instance
 *
 * @return bool|\Waboot\Layout
 */
function WabootLayout(){
	$waboot = Waboot();
	if($waboot instanceof \Waboot\Theme){
		return $waboot->layout;
	}else{
		trigger_error("Unable to find \Waboot\Theme class", E_USER_NOTICE);
		return false;
	}
}