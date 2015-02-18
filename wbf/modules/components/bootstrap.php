<?php
/**
 * WABOOT COMPONENT FRAMEWORK
 */

namespace WBF\modules\components;

require_once "functions.php";

$GLOBALS['loaded_components'] = array();
$GLOBALS['registered_components'] = array();

function module_init(){
    ComponentsManager::init();
    ComponentsManager::toggle_components(); //enable or disable components if necessary (manage the disable\enable actions sent by admin page)
}
add_action("wbf_after_setup_theme",'\WBF\modules\components\module_init');

function setup_components(){
    ComponentsManager::setupComponentsFilters();
    ComponentsManager::setupRegisteredComponents(); //Loads setup() methods of components
}
add_action("wbf_init",'\WBF\modules\components\setup_components', 12);

/**
 * WP HOOKS
 */

add_action( 'admin_menu', '\WBF\modules\components\ComponentsManager::add_menu', 11 );
add_action( 'admin_enqueue_scripts', '\WBF\modules\components\ComponentsManager::scripts' );

function components_enqueue(){
    ComponentsManager::enqueueRegisteredComponent('wp_enqueue_scripts');
}
add_action('wp_enqueue_scripts', '\WBF\modules\components\components_enqueue');

function components_widgets(){
    ComponentsManager::registerComponentsWidgets();
}
add_action('widgets_init', '\WBF\modules\components\components_widgets');

function components_init(){
    ComponentsManager::enqueueRegisteredComponent('wp');
}
add_action('wp', '\WBF\modules\components\components_init');