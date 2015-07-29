<?php

function wbf_acf_admin_scripts(){
    if(WBF_ENV == "dev"){
        wp_register_script("wbf-acf-admin-mfu",WBF_URL."/sources/js/admin/acf-fields/acf-field-mfu.js",array("jquery","underscore"),false,true);
        wp_enqueue_script("wbf-acf-admin-mfu");
    }else{
        wp_register_script("wbf-acf-admin",WBF_URL."/admin/js/acf-fields.min.js",array("jquery","underscore"),false,true);
        wp_enqueue_script("wbf-acf-admin");
    }
}
add_action( 'admin_enqueue_scripts', 'wbf_acf_admin_scripts');

add_filter('acf/settings/path', 'wbf_acf_settings_path');
function wbf_acf_settings_path($path) {
    $path = get_template_directory() . '/wbf/vendor/acf/';
    return $path;
}

add_filter('acf/settings/dir', 'wbf_acf_settings_dir');
function wbf_acf_settings_dir($dir) {
    $dir = get_template_directory_uri() . '/wbf/vendor/acf/';
    return $dir;
}

//add_filter('acf/settings/show_admin', '__return_false');

function wbf_include_field_types(){
    //MultipleFileUpload:
    include_once("acfFields/MultipleFileUpload.php");
    new \WBF\admin\acfFields\MultipleFileUpload();
}
add_action('acf/include_field_types', 'wbf_include_field_types');