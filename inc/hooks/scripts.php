<?php

namespace Waboot\hooks\scripts;
use function Waboot\functions\wbf_exists;
use Waboot\Theme;

/**
 * Loads frontend javascript
 */
function enqueue_js() {
	//Comment reply script
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ){
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action('wp_enqueue_scripts', __NAMESPACE__."\\enqueue_js");

/**
 * This fix prevent the wbData undefined for older waboot-child themes
 */
function backward_compatibility_fix(){
	?>
    <script type="text/javascript">
        if(typeof wbData === 'undefined'){
            var wbData = {
                'isAdmin': <?php if(is_admin()) echo 'true'; else echo 'false'; ?>
            };
        }
    </script>
	<?php
}
add_action('wp_enqueue_scripts', __NAMESPACE__."\\backward_compatibility_fix");

/**
 * Loads dashboard javascript
 */
function enqueue_dashboard_js(){
	//Main scripts:
    $dashboard_script_data = [
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
		    'download' => __('Download'),
		    'installing' => __( 'Installing...' ), //@see: script-loader.php
		    'activating' => _x( 'Activating...', 'Components Installer', 'waboot'),
		    'installFailedShort' => __( 'Install Failed!' ), //@see: script-loader.php
		    'activate' => __( 'Activate' ), //@see: class-wp-plugin-install-list-table.php
		    'active' => __( 'Active' )
	    ],
	    'wpurl' => get_bloginfo('wpurl'),
	    'isMobile' => class_exists("WBF") ? wb_is_mobile() : null,
	    'isAdmin' => is_admin(),
	    'isDebug' => defined("WP_DEBUG") && WP_DEBUG,
	    'wp_screen' => function_exists("get_current_screen") ? get_current_screen() : null,
    ];

	$dashboard_script_data = apply_filters( 'waboot/assets/dashboard/js/data',$dashboard_script_data);

	$deps = array('jquery','jquery-ui-core','jquery-ui-dialog','backbone','underscore');

	if( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ){
		wp_register_script( 'waboot-dashboard', get_template_directory_uri().'/assets/dist/js/waboot-dashboard.pkg.js', $deps, false, true);
	}else if(\is_file( get_template_directory() . '/assets/dist/js/waboot-dashboard.min.js' )){
		wp_register_script( 'waboot-dashboard', get_template_directory_uri(). '/assets/dist/js/waboot-dashboard.min.js', $deps, false, true);
    }else{
		wp_register_script( 'waboot-dashboard', get_template_directory_uri(). '/assets/dist/js/waboot-dashboard.pkg.js', $deps, false, true); //Load the source file if minified is not available
    }

	wp_localize_script( 'waboot-dashboard', 'wbData', $dashboard_script_data);
	wp_enqueue_script( 'waboot-dashboard');
}
add_action('admin_enqueue_scripts', __NAMESPACE__."\\enqueue_dashboard_js");

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
add_action( 'wp_head', __NAMESPACE__ . "\\add_ie_compatibility");