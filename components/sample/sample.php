<?php
/**
Component Name: Sample 01
Description: Sample component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class SampleComponent extends Waboot_Component{

    public function setup(){
        parent::setup();
        //Do stuff...
    }

    public function scripts(){
        //Enqueue scripts
    }

    public function styles(){
        //Enqueue styles
    }

    public function theme_options($options){
        $options = parent::theme_options($options);
        //Do stuff...
        $options[] = array(
            'name' => __( 'Sample Info Box', 'waboot' ),
            'desc' => __( 'This a sample infobox', 'waboot' ),
            'type' => 'info'
        );
        return $options;
    }

    public function onActivate(){
        parent::onActivate();
        //Do stuff...
    }

    public function onDeactivate(){
        parent::onDeactivate();
        //Do stuff...
    }
}