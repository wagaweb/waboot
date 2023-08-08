<?php

namespace Waboot\addons;

/**
 * @param $addon
 * @return string
 */
function getAddonDirectory($addon){
    return get_template_directory().'/addons/packages/'.$addon;
}

/**
 * @param $addon
 * @return string
 */
function getAddonDirectoryURI($addon){
    return get_template_directory_uri().'/addons/packages/'.$addon;
}

/**
 * List all addons
 * return @array
 */
function getAddons(){
    $basedir = get_template_directory().'/addons/packages';
    $disabledAddons = getDisabledAddons();
    $addons = array_filter(scandir($basedir),function($filename) use($basedir, $disabledAddons){
        return is_dir($basedir.'/'.$filename) &&
            !\in_array($filename,['.','..']) &&
            !in_array($filename, $disabledAddons, true);
    });
    return $addons;
}

/**
 * Retrieve all disabled addons
 *
 * @return array
 */
function getDisabledAddons(): array{
    return apply_filters('waboot/addons/disabled',[]);
}

