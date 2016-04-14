<?php
/**
Component Name: Top Nav Wrapper
Description: Top Nav Wrapper Component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class TopNavWrapperComponent extends \Waboot\Component{

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
		$social_position = Waboot\functions\get_option('social_position');
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
			$display_zone = $this->get_display_zone();
			$display_priority = $this->get_display_priority();
			Waboot()->layout->add_zone_action($display_zone,[$this,"display_tpl"],intval($display_priority));
		}
	}
	
	public function display_tpl(){
		$v = new \WBF\includes\mvc\HTMLView($this->relative_path."/templates/tpl.php");
		
		$social_class = call_user_func(function(){
			$social_position = Waboot\functions\get_option('social_position');
			$class = "";
			if($social_position == "topnav-right"){
				$class = "pull-right";
			}elseif($social_position == "topnav-left"){
				$class = "pull-left";
			}
			return $class;
		});

		$topnav_class = call_user_func(function(){
			$social_position = Waboot\functions\get_option('topnavmenu_position');
			$class = "";
			if($social_position == "right"){
				$class = "pull-right";
			}elseif($social_position == "left"){
				$class = "pull-left";
			}
			return $class;
		});
		
		$v->clean()->display([
			'display_socials' => Waboot\functions\get_option("social_position_none") == 1 || $social_class == "" ? false : true,
			'display_topnav' => in_array(Waboot\functions\get_option("topnavmenu_position"),['left','right']) ? true : false,
			'social_position_class' => $social_class,
			'topnavmenu_position_class' => $topnav_class,
			'topnav-inner_class' => Waboot\functions\get_option('topnav_width','container-fluid')
		]);
	}

	public function register_options(){
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$imagepath = WBF()->resources->get_admin_assets_uri()."/images/";

		$orgzr->set_group($this->name."_component");

		$orgzr->add_section("layout",_x("Layout","Theme options section","waboot"));
		$orgzr->add_section("header",_x("Header","Theme options section","waboot"));
		$orgzr->add_section("social",_x("Socials","Theme options section","waboot"));

		$orgzr->add([
			'name' => __('Top Nav', 'waboot'),
			'desc' => __('Select Top Nav width. Fluid or Boxed?', 'waboot'),
			'id' => 'waboot_topnav_width',
			'std' => 'container',
			'type' => 'images',
			'options' => [
				'container-fluid' => [
					'label' => 'Fluid',
					'value' => $imagepath . 'layout/top-nav-fluid.png'
				],
				'container' => [
					'label' => 'Boxed',
					'value' => $imagepath . 'layout/top-nav-boxed.png'
				]
			]
		],"layout");

		$orgzr->add([
			'name' => __('Top Nav Menu Position', 'waboot'),
			'desc' => __('Select the Top Nav Menu position', 'waboot'),
			'id' => 'topnavmenu_position',
			'std' => 'left',
			'type' => 'images',
			'options' => [
				'left' => [
					'label' => 'Left',
					'value' => $imagepath . 'topnav/top-nav-left.png'
				],
				'right' => [
					'label' => 'Right',
					'value' => $imagepath . 'topnav/top-nav-right.png'
				]
			]
		],"header");

		$orgzr->add([
			'name' => __( 'Social Position', 'waboot' ),
			'desc' => __( 'Select one of the following positions for the social links', 'waboot' ),
			'id' => 'social_position',
			'type' => 'images',
			'std'  => 'navigation',
			'options' => [
				'footer' =>  [
					'label' => 'Footer',
					'value' => $imagepath . 'social/footer.png'
				],
				'header-right' =>  [
					'label' => 'Header Right',
					'value' => $imagepath . 'social/header-right.png'
				],
				'header-left' =>  [
					'label' => 'Header Left',
					'value' => $imagepath . 'social/header-left.png'
				],
				'topnav-right' =>  [
					'label' => 'Topnav Right',
					'value' => $imagepath . 'social/topnav-right.png'
				],
				'topnav-left' =>  [
					'label' => 'Topnav Left',
					'value' => $imagepath . 'social/topnav-left.png'
				],
				'navigation' =>  [
					'label' => 'Navigation',
					'value' => $imagepath . 'social/nav.png'
				]
			]
		],"social");

		$orgzr->add([
			'name' => __( 'Do not use any of the previous positions', 'waboot' ),
			'desc' => __( 'You can manually place the social links with the <strong>waboot social widget</strong> (even if one of the previous positions is selected)', 'waboot' ),
			'id'   => 'social_position_none',
			'std'  => '0',
			'type' => 'checkbox'
		],"social");

		$orgzr->reset_group();
		$orgzr->reset_section();
	}
}