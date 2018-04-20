<?php

define('WBF_MIN_VER', '1.1.2');

waboot_init();

/**
 * Initialize Waboot
 */
function waboot_init(){
	try{
		$waboot_includes = [
			'inc/WBFNotFoundException.php',
			'inc/WBFVersionException.php',
			'inc/migrations/migration-2.3.2-3.0.0.php',
			'inc/template-functions.php',
			'inc/components-functions.php',
			'inc/template-tags.php',
			'inc/postformat-helpers.php',
			'inc/terms-tags.php',
			'inc/template-rendering.php',
			'inc/Layout.php',
			'inc/Theme.php',
			'inc/woocommerce/bootstrap.php',
			'inc/hooks/wbf_installer.php'
		];

		//Require mandatory files
		foreach($waboot_includes as $file){
			$filepath = locate_template($file);
			if(!$filepath) {
				throw new \Exception(sprintf(__('Error locating %s for inclusion', 'waboot'), $file));
			}
			require_once $filepath;
		}
		unset($file, $filepath);

		//Check prerequisites
		\Waboot\functions\check_prerequisites();

		//Init hooks
		$wb = Waboot()->load_hooks()->load_dependencies();

		//Build up the theme
		$wb->layout->create_zone("header",false,["always_load"=>false]);
		$wb->layout->create_zone("page-before",false);
		$wb->layout->create_zone("main-top",new \WBF\components\mvc\HTMLView("templates/zones/main-top.php"));
		$wb->layout->create_zone("aside-primary",new \WBF\components\mvc\HTMLView("templates/zones/aside.php"),["can_render_callback" => function(){
			//Callback called to decide whether print out the zone or not
			return \Waboot\functions\body_layout_has_sidebar();
		}]);
		$wb->layout->create_zone("content",false,["always_load"=>true]);
		$wb->layout->create_zone("aside-secondary",new \WBF\components\mvc\HTMLView("templates/zones/aside.php"),["can_render_callback" => function(){
			//Callback called to decide whether print out the zone or not
			return \Waboot\functions\body_layout_has_two_sidebars();
		}]);
		$wb->layout->create_zone("main-bottom",new \WBF\components\mvc\HTMLView("templates/zones/main-bottom.php"));
		$wb->layout->create_zone("page-after",false);
		$wb->layout->create_zone("footer",false,["always_load"=>false]);


		//Loads std hooks
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
				add_action("init",function(){
					$message = sprintf(
						__( "Waboot theme requires <a href='%s'>WBF Framework</a> plugin at least at v%s to work properly. You can <a href='%s'>download it manually</a> or <a href='%s'>go to the dashboard</a> for the auto-installer.", 'Waboot' ),
						'https://www.waboot.io',
						WBF_MIN_VER,
						'http://update.waboot.org/resource/get/plugin/wbf',
						admin_url()
					);
					echo $message;
				});
			}
			add_action( 'admin_init' , function(){
				\Waboot\hooks\wbf_installer\install_wbf_wp_update_hooks();
			});
			if(!\Waboot\Theme::is_wizard_done() || !\Waboot\Theme::is_wizard_skipped()){
				\Waboot\hooks\wbf_installer\notice_install_requirements();
			}
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
	if(class_exists("\\Waboot\\Theme")){
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