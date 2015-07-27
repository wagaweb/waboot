<?php

function wbf_admin_scripts(){
    if(WBF_ENV == "dev"){
	    wp_register_script("wbf-admin",WBF_URL."/sources/js/admin/admin.js",array("jquery"),false,true);
    }else{
        wp_register_script("wbf-admin",WBF_URL."/admin/js/admin.min.js",array("jquery"),false,true);
    }
	wp_enqueue_script("wbf-admin");
}
add_action( 'admin_enqueue_scripts', 'wbf_admin_scripts');