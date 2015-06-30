<?php

namespace WBF\modules\components;


class Component {

    var $name;
    var $active;
    var $file;
    var $files;
    var $is_child_component;
    var $directory_uri;

    //Se il filtro è su * il componente viene caricato sempre, altrimenti solo nelle robe specificate
    var $filters = array(
      'post_type' => '*',
      'node_id' => '*'
    );

	var $filters_updated_flag = false;

    public function __construct($component){
        $this->name = $component['nicename'];
        $this->active = $component['enabled'];
        $this->file = $component['file'];
        $this->is_child_component = $component['child_component'];
        if($this->is_child_component){
            $this->directory_uri = get_child_components_directory_uri()."/".$this->name;
        }else{
            $this->directory_uri = get_root_components_directory_uri()."/".$this->name;
        }
    }

    /**
     * Register the component $filters
     *
     * DO NOT EVER, AND I MEAN EVER, PUT THIS INTO OBJECT CONSTRUCTOR, IT WILL BLOW THIGS UP!
     */
    public function detectFilters(){

	    if($this->filters_updated_flag) return; //the method was already called at least once

        //Detect the filters
        if(of_get_option($this->name."_selective_disable","0") == 1){
            $this->filters = array();
        }elseif(of_get_option($this->name."_enabled_for_all_pages","1") == 1){
            $this->filters = array(
              'post_type' => '*',
              'node_id' => '*'
            );
        }else{
            $this->filters = array(
              'post_type' => array(),
              'node_id' => array()
            );
            $allowed_post_types = of_get_option($this->name."_load_locations",array());
            if($allowed_post_types['front'] == 1){
                array_push($this->filters['node_id'],get_option("page_on_front"));
                unset($allowed_post_types['front']);
            }
            if($allowed_post_types['home'] == 1){
                array_push($this->filters['node_id'],get_option("page_for_posts"));
                unset($allowed_post_types['home']);
            }
            foreach($allowed_post_types as $k => $val){
                if($val == 1){
                    array_push($this->filters['post_type'],$k);
                }
            }
            $specific_ids = of_get_option($this->name."_load_locations_ids",array());
            if(!empty($specific_ids)){
                $specific_ids = explode(',',trim($specific_ids));
                foreach($specific_ids as $id){
                    $id = trim($id);
                    if(!in_array($id,$this->filters['node_id']))
                        array_push($this->filters['node_id'],$id);
                }
            }
        }
	    $this->filters_updated_flag = true;
    }

    /**
     * Method called on "init" action for each active components
     */
    public function setup(){}

    /**
     * Method called from &_optionsframework_options() by addRegisteredComponentOptions()
     */
    public function register_options(){
        add_filter("of_options",array($this,"theme_options"));
        add_filter("wbf_components_options",array($this,"theme_options"));
    }

    /**
     * Method called on "wp" action for each active components that is enabled for current displayed page
     * @deprecated, use run() instead
     */
    public function onInit(){}

    /**
     * Method called on "wp" action for each active components that is enabled for current displayed page
     */
    public function run(){}

    /**
     * Method called on "wp_enqueue_scripts" action for each active components that is enabled for current displayed page
     */
    public function scripts(){}

    /**
     * Method called on "wp_enqueue_scripts" action for each active components that is enabled for current displayed page
     */
    public function styles(){}

    /**
     * Method called on "widgets_init" action for each active components that is enabled for current displayed page
     */
    public function widgets(){}

    public function theme_options($options){
        $options[] = array(
          'name' => $this->name." Component",
          'type' => 'heading',
          'component' => true
        );

        $options[] = array(
          'name' => __( 'Enable on all pages', 'wbf' ),
          'desc' => __( 'Check this box to load the component in every page (load locations will be ignored).', 'wbf' ),
          'id'   => $this->name.'_enabled_for_all_pages',
          'std'  => '1',
          'type' => 'checkbox'
        );

        $filter_locs = array_merge(array("front"=>"Frontpage","home"=>"Blog"),wbf_get_filtered_post_types());

        $options[] = array(
          'id' => $this->name.'_load_locations',
          'name' => __('Load locations','wbf'),
          'desc' => __('You can load the component only into one ore more page types by selecting them from the list below', 'wbf'),
          'type' => 'multicheck',
          'options' => $filter_locs
        );

        $options[] = array(
          'id' => $this->name.'_load_locations_ids',
          'name' => __('Load locations by ID','wbf'),
          'desc' => __('You can load the component for specific pages by enter here the respective ids (comma separated)', 'wbf'),
          'type' => 'text'
        );

        return $options;
    }

    public function onActivate(){
        //echo "Attivato: $this->name";
        add_action( 'admin_notices', array($this,'activationNotice') );
        $this->register_options();
    }

    public function onDeactivate(){
        //echo "Disattivato: $this->name";
        add_action( 'admin_notices', array($this,'deactivationNotice') );
    }

    public function activationNotice(){
        ?>
        <div class="updated">
            <p><?php _e( sprintf("Activated: %s",$this->name), "wbf" ); ?></p>
        </div>
    <?php
    }

    public function deactivationNotice(){
        ?>
        <div class="updated">
            <p><?php _e( sprintf("Deactivated: %s",$this->name), "wbf" ); ?></p>
        </div>
    <?php
    }

    /**
     * Retrive a file from component directory
     * @param $filepath
     * @return string
     */
    public function file($filepath){
        if(is_child_theme()){
            $child_file = get_child_components_directory_uri().$this->name."/".$filepath;
            $child_file_path = url_to_path($child_file);
            if(is_file($child_file_path)){
                return $child_file;
            }
        }
        return $this->directory_uri."/".$filepath;
    }
}