<?php
/**
Component Name: Footer Flex
Description: Footer with Widget Area display in Flexbox
Category: Layout
Tags: Footer, Flexbox
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
 */

if(!class_exists("\\Waboot\\Component")) return;

class Footer_Flex extends \Waboot\Component {

	var $default_zone = "footer";
	
	/**
	 * This method will be executed at Wordpress startup (every page load)
	 */
	public function setup(){
		parent::setup();
	}

    public function styles(){
        parent::styles();
        Waboot()->add_inline_style('footer_flex_style', $this->directory_uri . '/assets/dist/css/footerFlex.css');
    }

	public function run(){
		parent::run();
		$display_zone = $this->get_display_zone();
		$display_priority = $this->get_display_priority();
		WabootLayout()->add_zone_action($display_zone,[$this,"display_tpl"],intval($display_priority));
	}

	public function widgets() {
		add_filter("waboot/widget_areas/available",function($areas){
			$areas['footer-flex'] = [
				'name' => __('Footer Flex {{ n }} (Component)', 'waboot'),
				'description' => __( 'The widget areas registered by Footer Flex', 'waboot' ),
				'type' => 'multiple',
				'subareas' => 4
			];
			return $areas;
		});
	}

	public function display_tpl(){
        $vWrapper = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/footer-wrapper.php");
        $vClosure = new \WBF\components\mvc\HTMLView($this->theme_relative_path."/templates/closure-content.php");

        $vWrapper->clean()->display([
            "footer_width" => Waboot\functions\get_option("footer_flex_width"),
            "closure_content" => $vClosure->get([
                "closure_width" => Waboot\functions\get_option( "closure_width"),
                "footer_custom_toggle" => Waboot\functions\get_option( "footer_custom_toggle" ),
                "footer_text" => \Waboot\functions\get_option('footer_custom_toggle') ? \Waboot\functions\get_option('footer_custom_text') : '&copy; ' . date('Y') . ' ' . get_bloginfo('name')
            ])
        ]);
	}

	public function register_options() {
		parent::register_options();
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$imagepath = get_template_directory_uri()."/assets/images/options/";

		$orgzr->set_group($this->name."_component");

		$orgzr->add_section("layout",_x("Layout","Theme options section","waboot"));
		$orgzr->add_section("footer",_x( 'Footer',"Theme options section","waboot"));

		$orgzr->add([
			'name' => __( 'Show custom footer text?', 'waboot' ),
			'desc' => __( 'Default is disabled. Check this box to use custom footer text. Fill in your text below.', 'waboot' ),
			'id'   => 'footer_custom_toggle',
			'std'  => '1',
			'type' => 'checkbox'
		],"footer");

		$orgzr->add([
			'name' => __( 'Custom footer text', 'waboot' ),
			'desc' => __( 'Enter the text here that you would like displayed at the bottom of your site. This setting will be ignored if you do not enable "Show custom footer text" above.', 'waboot' ),
			'id'   => 'footer_custom_text',
			'std'  => '&copy; '.date("Y")." - you business name",
			'type' => 'textarea'
		],"footer");

        $orgzr->add([
            'name' => _x('Footer Background', 'Theme options', 'waboot'),
            'desc' => _x('Change the footer background color.', 'Theme options', 'waboot'),
            'id' => 'footer_flex_bgcolor',
            'type' => 'color',
            'std' => '#f6f6f6',
            'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
        ],"footer");

		$orgzr->add([
			'name' => __( 'Footer Width', 'waboot' ),
			'desc' => __( 'Select footer width. Fluid or Boxed?', 'waboot' ),
			'id' => 'footer_flex_width',
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

		$orgzr->reset_group();
		$orgzr->reset_section();
	}
}