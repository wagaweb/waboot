<?php

add_filter('acf/settings/path', 'wbf_acf_settings_path');
function wbf_acf_settings_path($path)
{
    $path = get_stylesheet_directory() . '/acf/';
    return $path;
}

add_filter('acf/settings/dir', 'wbf_acf_settings_dir');
function wbf_acf_settings_dir($dir)
{
    $dir = get_stylesheet_directory_uri() . '/acf/';
    return $dir;
}

//add_filter('acf/settings/show_admin', '__return_false');