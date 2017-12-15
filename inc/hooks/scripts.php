<?php

namespace Waboot\hooks\scripts;
use function Waboot\functions\wbf_exists;
use Waboot\Theme;

/**
 * Loads javascript modules
 */
function enqueue_js() {
	//Comment reply script
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ){
		wp_enqueue_script( 'comment-reply' );
	}
	
	if(!is_admin() && wbf_exists()){
		//Bootstrap
		wp_enqueue_script( 'bootstrap.js', wbf_locate_template_uri( 'assets/dist/js/bootstrap.min.js' )."#asyncload", array( 'jquery' ), false, true );
	}

	//Main scripts:
	$wpData = apply_filters("wbft/js/localization",array(
			//Std
			'ajaxurl' => admin_url('admin-ajax.php'),
			'generators_action' => 'handle_generator',
			'generators_first_step_slug' => Theme::GENERATOR_STEP_PRE_ACTIONS,
			'generators_steps' => [
                Theme::GENERATOR_STEP_PRE_ACTIONS,
                Theme::GENERATOR_STEP_COMPONENTS,
                Theme::GENERATOR_STEP_OPTIONS,
                Theme::GENERATOR_STEP_ACTIONS
            ],
			'generators_labels' => [
                'processing' => _x('Processing...','Generators','waboot'),
                'completed' => sprintf(_x('Wizard completed successfully!','Generators','waboot'),admin_url('admin.php?page=wbf_options')),
                'rerun_wizard' => _x('Run again','Generators', 'waboot')
            ],
			'components_installer_labels' => [
				'installing' => __( 'Installing...' ), //@see: script-loader.php
				'installFailedShort' => __( 'Install Failed!' ), //@see: script-loader.php
				'activate' => __( 'Activate' ) //@see: class-wp-plugin-install-list-table.php
            ],
			'wpurl' => get_bloginfo('wpurl'),
			'isMobile' => class_exists("WBF") ? wb_is_mobile() : null,
			'isAdmin' => is_admin(),
			'isDebug' => defined("WP_DEBUG") && WP_DEBUG,
			'wp_screen' => function_exists("get_current_screen") ? get_current_screen() : null,
			//WooCommerce
			'has_woocommerce' => function_exists("is_woocommerce"),
			'is_woocommerce' => function_exists("is_woocommerce") && is_woocommerce(),
			'is_cart' => function_exists("is_cart") && is_cart(),
			'is_checkout' => function_exists("is_checkout") && is_checkout(),
		)
	);

	if(wbf_exists()){
		//Components
		$wpData['components'] = isset($GLOBALS['loaded_components']) ? $GLOBALS['loaded_components'] : null;
        $wpData['components_js'] = call_user_func(function(){
			global $loaded_components;
			if(!isset($loaded_components)) return null;
			foreach($loaded_components as $c){
				$dir = dirname($c->file);
				$js = $dir."/".$c->name."-module.js";
				if(!file_exists($js)) $js = $dir."/assets/js/".$c->name."-module.js";
				if(!file_exists($js)) $js = $dir."/js/".$c->name."-module.js";
				if(!file_exists($js)) continue;
				$components_js[] = $js;
			}
			if(empty($components_js)) return null;
			return $components_js;
		});
    }

	$deps = array('jquery','jquery-ui-core','jquery-ui-dialog','backbone','underscore');

	if((defined('SCRIPT_DEBUG') && SCRIPT_DEBUG)){
		wp_register_script( 'waboot', get_template_directory_uri().'/assets/dist/js/waboot.js', $deps, false, true);
	}else{
		if(is_file(get_template_directory()."/assets/dist/js/waboot.min.js")){
			wp_enqueue_script( 'waboot', get_template_directory_uri(). '/assets/dist/js/waboot.min.js', $deps, false, true);
		}else{
			wp_enqueue_script( 'waboot', get_template_directory_uri(). '/assets/dist/js/waboot.js', $deps, false, true); //Load the source file if minified is not available
		}
	}

	wp_localize_script( 'waboot', 'wbData', $wpData);
	wp_enqueue_script( 'waboot');
}
add_action('wp_enqueue_scripts', __NAMESPACE__."\\enqueue_js");
add_action('admin_enqueue_scripts', __NAMESPACE__."\\enqueue_js");

/**
 * Add IE compatibility scripts
 */
function add_ie_compatibility(){
    ?>
    <!--[if lt IE 9]>
        <script src="<?php echo get_template_directory_uri(); ?>/assets/dist/js/html5shiv.min.js" type="text/javascript"></script>
        <script src="<?php echo get_template_directory_uri(); ?>/assets/dist/js/respond.min.js" type="text/javascript"></script>
    <![endif]-->
    <?php
}
add_action("wp_head",__NAMESPACE__."\\add_ie_compatibility");