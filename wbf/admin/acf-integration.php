<?php

add_filter('acf/settings/path', 'wbf_acf_settings_path');
add_filter('acf/settings/dir', 'wbf_acf_settings_dir');
add_filter('acf/settings/show_admin', '__return_false');
add_action('acf/include_field_types', 'wbf_include_field_types');

function wbf_acf_settings_path($path) {
    $path = get_template_directory() . '/wbf/vendor/acf/';
    return $path;
}

function wbf_acf_settings_dir($dir) {
    $dir = get_template_directory_uri() . '/wbf/vendor/acf/';
    return $dir;
}

function wbf_include_field_types(){
    //MultipleFileUpload:
    include_once("acfFields/MultipleFileUpload.php");
    new \WBF\admin\acfFields\MultipleFileUpload();
}