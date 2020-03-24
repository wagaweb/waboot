<?php

namespace Waboot\inc\hooks;

use function Waboot\inc\getWidgetAreas;
use function Waboot\inc\renderWidgetArea;

/**
 * Register widget areas
 */
function registerWidgetAreas(){
    $areas = getWidgetAreas();

    foreach($areas as $area_id => $area_args){
        $args = [
            'name' => $area_args['name'],
            'description' => $area_args['description'] ?? '',
            'id' => $area_id,
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget' => '</div>',
            'before_title' => '<h3 class="widget__title">',
            'after_title' => '</h3>',
        ];
        $args = wp_parse_args($area_args, $args);
        register_sidebar($args);
    }
}
add_action('widgets_init',__NAMESPACE__."\\registerWidgetAreas", 12);

/**
 * Add each widget area to a "zone"
 */
function addWidgetAreasToZones(){
    $areas = getWidgetAreas();

    foreach($areas as $areaId => $areaArgs){
        //Add Widget Area to zone
        if(!isset($areaArgs['render_zone'])){
            continue;
        }
        $priority = isset($areaArgs['render_priority']) ? (int) $areaArgs['render_priority'] : 50;
        if(!is_active_sidebar($areaId)){
            continue;
        }
        //Adds an action to the "render_zone" to display the widget area.
        add_action('waboot/layout/'.$areaArgs['render_zone'], function() use($areaId){
            renderWidgetArea($areaId);
        },$priority);
    }
}
add_action('wp',__NAMESPACE__."\\addWidgetAreasToZones");
