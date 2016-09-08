<?php
/**
Component Name: Footer Classic
Description: Footer Classic Component
Category: Layout
Tags: jQuery, Colorbox
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
 */

class Footer_Classic extends \Waboot\Component {

	var $default_zone = "footer";
	
	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
	}

	public function run(){
		parent::run();
		$display_zone = $this->get_display_zone();
		$display_priority = $this->get_display_priority();
		Waboot()->layout->add_zone_action($display_zone,[$this,"display_tpl"],intval($display_priority));
	}

	public function widgets() {
		add_filter("waboot/widget_areas/available",function($areas){
			$areas['footer-classic'] = [
				'name' => __('Footer Classic {{ n }} (Component)', 'waboot'),
				'description' => __( 'The widget areas registered by Footer Classic', 'waboot' ),
				'type' => 'multiple',
				'subareas' => 4,
				'render_zone' => 'footer'
			];
			return $areas;
		});
	}

	public function display_tpl(){
		$v = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/footer-classic.php");

		$default_footer_text = '&copy; ' . date('Y') . ' ' . get_bloginfo('name');
		$footer_text = \Waboot\functions\get_option('custom_footer_toggle') ? \Waboot\functions\get_option('custom_footer_text') : $default_footer_text;

		$args = [
			'closure_width' => of_get_option( 'closure_width','container' ),
			'custom_footer_toggle' => of_get_option( 'custom_footer_toggle','container' ),
			'footer_text' => $footer_text,
			"social_position" => Waboot\functions\get_option('social_position'),
			'display_socials' => Waboot\functions\get_option("social_position_none") == 1 || Waboot\functions\get_option('social_position') != "navigation" ? false : true,
		];
		$v->clean()->display($args);
	}

	public function register_options() {
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$imagepath = get_template_directory_uri()."/assets/images/options/";

		$orgzr->set_group($this->name."_component");

		$orgzr->add_section("layout",_x("Layout","Theme options section","waboot"));
		$orgzr->add_section("social",_x("Socials","Theme options section","waboot"));
		$orgzr->add_section("footer",_x( 'Footer',"Theme options section","waboot"));

		$orgzr->add([
			'name' => __( 'Show custom footer text?', 'waboot' ),
			'desc' => __( 'Default is disabled. Check this box to use custom footer text. Fill in your text below.', 'waboot' ),
			'id'   => 'custom_footer_toggle',
			'std'  => '1',
			'type' => 'checkbox'
		],"footer");

		$orgzr->add([
			'name' => __( 'Custom footer text', 'waboot' ),
			'desc' => __( 'Enter the text here that you would like displayed at the bottom of your site. This setting will be ignored if you do not enable "Show custom footer text" above.', 'waboot' ),
			'id'   => 'custom_footer_text',
			'std'  => '&copy; '.date("Y")." - you business name",
			'type' => 'textarea'
		],"footer");

        $orgzr->add([
            'name' => _x('Footer Classic Background', 'Theme options', 'waboot'),
            'desc' => _x('Change the footer background color.', 'Theme options', 'waboot'),
            'id' => 'footer_classic_bgcolor',
            'type' => 'color',
            'std' => '#f6f6f6',
            'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
        ],"footer");

		$orgzr->add([
			'name' => __( 'Footer Classic Width', 'waboot' ),
			'desc' => __( 'Select footer width. Fluid or Boxed?', 'waboot' ),
			'id' => 'footer_classic_width',
			'std' => 'container',
			'type' => 'images',
			'options' => array(
				'container-fluid' => array (
					'label' => 'Fluid',
					'value' => $imagepath . 'layout/footer-fluid.png'
				),
				'container' => array (
					'label' => 'Boxed',
					'value' => $imagepath . 'layout/footer-boxed.png'
				)
			)
		],"footer");

        $orgzr->add([
            'name' => _x('Closure Background', 'Theme options', 'waboot'),
            'desc' => _x('Change the closure background color.', 'Theme options', 'waboot'),
            'id' => 'closure_bgcolor',
            'type' => 'color',
            'std' => '#f6f6f6',
            'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
        ],"footer");

		$orgzr->add([
			'name' => __( 'Closure', 'waboot' ),
			'desc' => __( 'Select closure width. Fluid or Boxed?', 'waboot' ),
			'id' => 'closure_width',
			'std' => 'container',
			'type' => 'images',
			'options' => array(
				'container-fluid' => array (
					'label' => 'Fluid',
					'value' => $imagepath . 'layout/closure-fluid.png'
				),
				'container' => array (
					'label' => 'Boxed',
					'value' => $imagepath . 'layout/closure-boxed.png'
				)
			)
		],"footer");

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
			'std'  => 'header-right',
			'options' => [
				'footer' =>  [
					'label' => 'Footer',
					'value' => $imagepath . 'social/footer.png'
				]
			]
		],"social");

		$orgzr->update("social_position_none",[
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