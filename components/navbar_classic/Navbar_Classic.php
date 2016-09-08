<?php
/**
Component Name: Navbar Classic
Description: Navbar Classic Component
Category: Layout
Tags: Navbar
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class Navbar_Classic extends \Waboot\Component{
	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
		add_filter("waboot/navigation/main/class",[$this,"set_main_navigation_classes"]);
	}

	public function run(){
		parent::run();
		$display_zone = $this->get_display_zone();
		$display_priority = $this->get_display_priority();
		Waboot()->layout->add_zone_action($display_zone,[$this,"display_tpl"],intval($display_priority));
	}

	public function display_tpl(){
		$wrapper = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/navbar_wrapper.php");
		$content = (new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/navbar_content.php"))->clean()->get([
			'show_mobile_nav' => Waboot\functions\get_option('mobilenav_style') == "offcavas",
			'display_socials' => Waboot\functions\get_option("social_position_none") == 1 || Waboot\functions\get_option('social_position') != "navigation" ? false : true,
			'display_searchbar' => Waboot\functions\get_option("display_search_bar_in_header") == 1
		]);
		$header_layout = call_user_func(function(){
			if(\WBF\modules\components\ComponentsManager::is_active("header_classic")){
				return "header_classic";
			}elseif(\WBF\modules\components\ComponentsManager::is_active("navbar_classic")){
				return "navbar_classic";
			}elseif(\WBF\modules\components\ComponentsManager::is_active("navbar_logo")){
				return "navbar_logo";
			}
			return false;
		});
		$wrapper->clean()->display([
			"navbar_width" => Waboot\functions\get_option("navbar_width"),
			"navbar_class" => $header_layout ? "nav-".$header_layout : "nav",
			'content' => $content,
		]);
	}

	/**
	 * Set the classes to the main navigation
	 *
	 * @param $class
	 *
	 * @return mixed
	 */
	public function set_main_navigation_classes($class){
		$classes = [$class,"nav"];
		$options = \Waboot\functions\get_option('navbar_align'); //todo: add this
		if(is_array($options) && !empty($options)){
			$classes = array_merge($classes,$options);
		}
		return implode(' ', $classes);
	}

	public function register_options() {
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$imagepath = get_template_directory_uri()."/assets/images/options/";

		$orgzr->set_group($this->name."_component");

		$orgzr->add_section("header",_x("Header","Theme options section","waboot"));
		$orgzr->add_section("layout",_x("Layout","Theme options section","waboot"));
		$orgzr->add_section("social",_x("Socials","Theme options section","waboot"));

		$orgzr->update('mobilenav_style',[
			'name' => __( 'Mobile Nav Style', 'waboot' ),
			'desc' => __( 'Select your mobile nav style' ,'waboot' ),
			'id'   => 'mobilenav_style',
			'std' => 'offcanvas',
			'type' => 'images',
			'options' => array(
				'inline' => array(
					'label' => _x('Inline',"mobilenav_style","waboot"),
					'value' => $imagepath . 'mobile/nav-bootstrap.png'
				),
				'offcanvas' => array(
					'label' => _x('OffCanvas',"mobilenav_style","waboot"),
					'value' => $imagepath . 'mobile/nav-offcanvas.png'
				)
			)
		],"header");

		$orgzr->update('search_bar',[
			'name' => __( 'Show search bar in Header?', 'waboot' ),
			'desc' => __( 'Default is enabled. Uncheck this box to turn it off.', 'waboot' ),
			'id'   => 'display_search_bar_in_header',
			'std'  => '0',
			'type' => 'checkbox'
		],'header');

		$orgzr->update("navbar_width", [
			'name' => __( 'Navbar', 'waboot' ),
			'desc' => __( 'Select navbar width. Fluid or Boxed?', 'waboot' ),
			'id' => 'navbar_width',
			'std' => 'container',
			'type' => 'images',
			'options' => array(
				'container-fluid' => array (
					'label' => 'Fluid',
					'value' => $imagepath . 'layout/header-fluid.png'
				),
				'container' => array (
					'label' => 'Boxed',
					'value' => $imagepath . 'layout/header-boxed.png'
				)
			)
		],'layout');

		$socials = \Waboot\functions\get_available_socials();

		foreach($socials as $k => $s){
			$opt_id = 'social_'.$k;
			$orgzr->update($opt_id,[
				'name' => $s['name'],
				'desc' => $s['theme_options_desc'],
				'id'   => $opt_id,
				'type' => 'text',
				'std'  => ''
			],"social");
		}
		
		$orgzr->update("social_position",[
			'name' => __( 'Social Position', 'waboot' ),
			'desc' => __( 'Select one of the following positions for the social links', 'waboot' ),
			'id' => 'social_position',
			'type' => 'images',
			'std'  => 'navigation',
			'options' => [
				'navigation' =>  [
					'label' => _x('Navigation',"social_position","waboot"),
					'value' => $imagepath . 'social/nav.png'
				]
			]
		],"social");

		$orgzr->reset_group();
		$orgzr->reset_section();
	}
}