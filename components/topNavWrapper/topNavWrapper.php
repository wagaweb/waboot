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
		$can_display = true;
		if($can_display){
			$display_zone = call_user_func(function(){
				$zone = "header";
				if(function_exists("wb_get_option")){
					$zone_opt = wb_get_option(strtolower($this->name)."_display_zone");
					if($zone_opt){
						$zone = $zone_opt;
					}
				}
				return $zone;
			});
			$display_priority = call_user_func(function(){
				$p = "10";
				if(function_exists("wb_get_option")){
					$p_opt = wb_get_option(strtolower($this->name)."_display_priority");
					if($p_opt){
						$p = $p_opt;
					}
				}
				return $p;
			});
			Waboot()->layout->add_zone_action($display_zone,[$this,"display_tpl"],intval($display_priority));
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

	/*public function register_options(){
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();
	}*/

	public function theme_options($options){
		$options = parent::theme_options($options);

		if(!function_exists("Waboot")) return $options;
		$zones = Waboot()->layout->getZones();
		if(empty($zones) || !isset($zones['header'])) return $options;

		$zone_options = call_user_func(function() use($zones){
			$opts = [];
			foreach($zones as $k => $v){
				$opts[$k] = $v['slug'];
			}
			return $opts;
		});

        $options[] = array(
            'name' => _x( 'Zone Settings', 'component settings', 'waboot' ),
            'desc' => _x( 'Choose zone settings for this component', 'component_settings', 'waboot' ),
            'type' => 'info'
        );
        $options[] = array(
            'name' => _x( 'Position', 'component settings', 'waboot' ),
            'desc' => _x( 'Choose in which zone you want to display', 'component_settings', 'waboot' ),
            'id'   => strtolower($this->name).'_display_zone',
            'std'  => 'header',
			'options' => $zone_options,
            'type' => 'select'
        );
		$options[] = array(
			'name' => _x( 'Priority', 'component settings', 'waboot' ),
			'desc' => _x( 'Choose the display priority', 'component_settings', 'waboot' ),
			'id'   => strtolower($this->name).'_display_priority',
			'std'  => '10',
			'type' => 'text'
		);

        return $options;
    }
}