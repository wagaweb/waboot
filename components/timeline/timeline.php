<?php
/**
Component Name: Timeline
Description: Timeline component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class TimelineComponent extends Waboot_Component{

    public function scripts(){
        wp_enqueue_script('timeline-script',$this->directory_uri . '/timeline.js',array('jquery'),false,false);
    }
    
    public function styles(){
        wp_enqueue_style('timeline-style',$this->directory_uri . '/timeline.css');
    }

}