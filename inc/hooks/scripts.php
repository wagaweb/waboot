<?php

namespace Waboot\hooks\scripts;

/**
 * Loads javascript modules
 */
function enqueue_js() {
	//Comment reply script
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ){
		wp_enqueue_script( 'comment-reply' );
	}
	
	if(!is_admin()){
		//Bootstrap
		wp_enqueue_script( 'bootstrap.js', wbf_locate_template_uri( 'assets/dist/js/bootstrap.min.js' )."#asyncload", array( 'jquery' ), false, true );
	}

	//Main scripts:
	$wpData = apply_filters("wbft/js/localization",array(
			'ajaxurl' => admin_url('admin-ajax.php'),
			'wpurl' => get_bloginfo('wpurl'),
			'isMobile' => class_exists("WBF") ? wb_is_mobile() : null,
			'isAdmin' => is_admin(),
			'isDebug' => defined("WP_DEBUG") && WP_DEBUG,
			'wp_screen' => function_exists("get_current_screen") ? get_current_screen() : null,
			'components' => isset($GLOBALS['loaded_components']) ? $GLOBALS['loaded_components'] : null,
			'components_js' => call_user_func(function(){
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
			})
		)
	);

	$deps = array('jquery','jquery-ui-core','jquery-ui-dialog','backbone','underscore');

	if((defined('SCRIPT_DEBUG') && SCRIPT_DEBUG)){
		wp_register_script( 'waboot', wbf_locate_template_uri( 'assets/src/js/waboot.js' )."#asyncload", $deps, false, true);
	}else{
		if(is_file(get_template_directory()."/assets/dist/js/waboot.min.js")){
			wp_enqueue_script( 'waboot', wbf_locate_template_uri( 'assets/dist/js/waboot.min.js' )."#asyncload", $deps, false, true);
		}else{
			wp_enqueue_script( 'waboot', wbf_locate_template_uri( 'assets/src/js/waboot.js' )."#asyncload", $deps, false, true); //Load the source file if minified is not available
		}
	}

	wp_localize_script( 'waboot', 'wbData', $wpData);
	wp_enqueue_script( 'waboot');
}
add_action('wp_enqueue_scripts', __NAMESPACE__."\\enqueue_js", 90 );

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