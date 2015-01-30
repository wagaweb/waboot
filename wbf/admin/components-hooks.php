<?php

function waboot_components_enqueue(){
    Waboot_ComponentsManager::enqueueRegisteredComponent('wp_enqueue_scripts');
}
add_action('wp_enqueue_scripts', 'waboot_components_enqueue');

function waboot_components_widgets(){
	Waboot_ComponentsManager::registerComponentsWidgets();
}
add_action('widgets_init', 'waboot_components_widgets');

function waboot_components_init(){
    Waboot_ComponentsManager::enqueueRegisteredComponent('wp');
}
add_action('wp', 'waboot_components_init');