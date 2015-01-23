<?php

function wbf_admin_scripts(){
	wp_register_script("wbf-admin",WBF_URL."/admin/js/admin.js",array("jquery"),false,true);
	wp_enqueue_script("wbf-admin");
}
add_action( 'admin_enqueue_scripts', 'wbf_admin_scripts');