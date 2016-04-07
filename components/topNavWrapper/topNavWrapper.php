<?php
/**
Component Name: Top Nav Wrapper
Description: Top Nav Wrapper Component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class TopNavWrapperComponent extends \WBF\modules\components\Component{

    /**
     * This method will be executed at Wordpress startup (every page load)
     */
    public function setup(){
        parent::setup();
    }

	/**
	 * This method will be executed on the "wp" action in pages where the component must be loaded
	 */
	public function run(){
		parent::run();
		$social_position = wb_get_option('social_position');
		$can_display = call_user_func(function() use($social_position){
			$can_display = false;
			if(
				is_active_sidebar( 'topbar' ) ||
				($social_position == 'topnav-right' || $social_position == 'topnav-left' ) ||
				has_nav_menu( 'top' )
			){
				$can_display = true;
			}
			return $can_display;
		});
		if($can_display){
			Waboot()->layout->add_zone_action("header",[$this,"display_tpl"]);
		}
	}
	
	public function display_tpl(){
		$v = new \WBF\includes\mvc\HTMLView($this->relative_path."/templates/tpl.php");
		
		$social_class = call_user_func(function(){
			$social_position = wb_get_option('social_position');
			$class = "";
			if($social_position == "topnav-right"){
				$class = "pull-right";
			}elseif($social_position == "topnav-left"){
				$class = "pull-left";
			}
			return $class;
		});

		$topnav_class = call_user_func(function(){
			$social_position = wb_get_option('topnavmenu_position');
			$class = "";
			if($social_position == "right"){
				$class = "pull-right";
			}elseif($social_position == "left"){
				$class = "pull-left";
			}
			return $class;
		});
		
		$v->clean()->display([
			'display_socials' => wb_get_option("social_position_none") == 1 || $social_class == "" ? false : true,
			'display_topnav' => in_array(wb_get_option("topnavmenu_position"),['left','right']) ? true : false,
			'social_position_class' => $social_class,
			'topnavmenu_position_class' => $topnav_class,
			'topnav-inner_class' => wb_get_option('topnav_width','container-fluid')
		]);
	}

    public function theme_options($options){
        $options = parent::theme_options($options);
        //Do stuff...
        $options[] = array(
            'name' => __( 'Sample Info Box', 'waboot' ),
            'desc' => __( 'This is a sample infobox', 'waboot' ),
            'type' => 'info'
        );
        $options[] = array(
            'name' => __( 'Sample check box', 'waboot' ),
            'desc' => __( 'This is a sample checkbox.', 'waboot' ),
            'id'   => $this->name.'_sample_checkbox',
            'std'  => '0', //not enabled by default
            'type' => 'checkbox'
        );
        return $options;
    }
}