<?php

function wbf_admin_scripts(){
    if(WBF_ENV == "dev"){
	    wp_register_script("wbf-admin",WBF_URL."/sources/js/admin/wbf-admin-bundle.js",array("jquery","backbone","underscore"),false,true);
    }else{
        wp_register_script("wbf-admin",WBF_URL."/admin/js/wbf-admin.min.js",array("jquery","backbone","underscore"),false,true);
    }
	$wbfData = apply_filters("wbf/js/admin/localization",[
		'ajaxurl' => admin_url('admin-ajax.php'),
		'wpurl' => get_bloginfo('wpurl'),
		'wp_screen' => function_exists("get_current_screen") ? get_current_screen() : null,
		'isAdmin' => is_admin()
	]);
	wp_localize_script("wbf-admin","wbfData",$wbfData);
	wp_enqueue_script("wbf-admin");
}
add_action( 'admin_enqueue_scripts', 'wbf_admin_scripts', 99);