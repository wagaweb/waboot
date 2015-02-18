<?php

namespace WBF\modules\components;

function get_root_components_directory_uri(){
    return get_template_directory_uri()."/components/";
}

function get_child_components_directory_uri(){
    return get_stylesheet_directory_uri()."/components/";
}

function get_root_components_directory(){
    return get_template_directory()."/components/";
}

function get_child_components_directory(){
    return get_stylesheet_directory()."/components/";
}

function print_component_status($comp_data){
    if ( ComponentsManager::is_active( $comp_data ) ) {
        echo "active";
    } else {
        echo "inactive";
    }
}