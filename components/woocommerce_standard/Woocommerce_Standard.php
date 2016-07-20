<?php
/**
Component Name: Sample 01
Description: Sample component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

class Woocoommerce_Standard extends \WBF\modules\components\Component{

    /**
     * This method will be executed at Wordpress startup (every page load)
     */
    public function setup(){
        parent::setup();
        //Do stuff...
    }

    /**
     * This method will be executed on the "wp" action in pages where the component must be loaded
     */
    public function run(){
        parent::run();
        //Do stuff...
    }

	/**
	 * Register component scripts (called automatically)
	 */
    public function scripts(){
	    wp_register_script("component-{$this->name}-script",$this->directory_uri . '/assets/dist/js/woocommerce-standard.js', ['jquery'], false, false);
	    wp_enqueue_script("component-{$this->name}-script");
    }

	/**
	 * Register component styles (called automatically)
	 */
    public function styles(){
    	wp_register_style("component-{$this->name}-style",$this->directory_uri . '/assets/dist/css/woocommerce-standard.min.css');
	    wp_enqueue_style("component-{$this->name}-style");
    }

	/**
	 * This is an action callback.
	 *
	 * Here you can use WBF Organizer to set component options
	 */
	public function register_options(){
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		/*
		 * Standard group:
		 */

		$orgzr->set_group("components");

		$section_name = $this->name."_component";
		$additional_params = [
			'component' => true,
			'component_name' => $this->name
		];

		$orgzr->add_section($section_name,$this->name." Component",null,$additional_params);

		$orgzr->set_section($section_name);

		$orgzr->add([
			'type' => 'info',
			'name' => 'This component needs no administration options.',
			'desc' => 'Check <strong>theme options</strong> for additional settings'
		]);

		$orgzr->reset_group();
		$orgzr->reset_section();

		if(!function_exists("is_woocommerce")) return;

		/*
		 * WOOCOMMERCE PAGE TAB
		 */

		$orgzr->add_section("woocommerce",__( 'WooCommerce', 'waboot' ));

		$orgzr->set_section("woocommerce");

		$orgzr->add(array(
			'name' => __( 'WooCommerce Shop Page', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'type' => 'info'
		));

		/*$orgzr->add(array(
			'name' => __('WooCommerce Shop Layout', 'waboot'),
			'desc' => __('Select WooCommerce shop page layout', 'waboot'),
			'id' => 'woocommerce_shop_sidebar_layout',
			'std' => $sidebar_layouts['default'],
			'type' => $opt_type,
			'options' => $final_sidebar_layouts
		));*/

		$orgzr->add(array(
			'name' => __("Primary Sidebar width","waboot"),
			'desc' => __("Choose the primary sidebar width","waboot"),
			'id' => 'woocommerce_shop_primary_sidebar_size',
			'std' => '1/4',
			'type' => "select",
			'options' => array("1/2"=>"1/2","1/3"=>"1/3","1/4"=>"1/4","1/6"=>"1/6")
		));

		$orgzr->add(array(
			'name' => __("Secondary Sidebar width","waboot"),
			'desc' => __("Choose the secondary sidebar width","waboot"),
			'id' => 'woocommerce_shop_secondary_sidebar_size',
			'std' => '1/4',
			'type' => "select",
			'options' => array("1/2"=>"1/2","1/3"=>"1/3","1/4"=>"1/4","1/6"=>"1/6")
		));

		$orgzr->add(array(
			'name' => __( 'Display WooCommerce page title', 'waboot' ),
			'desc' => __( 'Check this box to show page title.', 'waboot' ),
			'id'   => 'woocommerce_shop_displaytitle',
			'std'  => '1',
			'type' => 'checkbox'
		));

		$orgzr->add(array(
			'name' => __('Title position', 'waboot'),
			'desc' => __('Select where to display page title', 'waboot'),
			'id' => 'woocommerce_shop_title_position',
			'std' => 'top',
			'type' => 'select',
			'options' => array('top' => __("Above primary","waboot"), 'bottom' => __("Below primary","waboot"))
		));

		$orgzr->add(array(
			'name' => __( 'WooCommerce Archives and Categories', 'waboot' ),
			'desc' => __( '', 'waboot' ),
			'type' => 'info'
		));

		/*$orgzr->add(array(
			'name' => __('WooCommerce Archive Layout', 'waboot'),
			'desc' => __('Select Woocommerce archive layout', 'waboot'),
			'id' => 'woocommerce_sidebar_layout',
			'std' => $sidebar_layouts['default'],
			'type' => $opt_type,
			'options' => $final_sidebar_layouts
		));*/

		$orgzr->add(array(
			'name' => __("Primary Sidebar width","waboot"),
			'desc' => __("Choose the primary sidebar width","waboot"),
			'id' => 'woocommerce_primary_sidebar_size',
			'std' => '1/4',
			'type' => "select",
			'options' => array("1/2"=>"1/2","1/3"=>"1/3","1/4"=>"1/4","1/6"=>"1/6")
		));

		$orgzr->add(array(
			'name' => __("Secondary Sidebar width","waboot"),
			'desc' => __("Choose the secondary sidebar width","waboot"),
			'id' => 'woocommerce_secondary_sidebar_size',
			'std' => '1/4',
			'type' => "select",
			'options' => array("1/2"=>"1/2","1/3"=>"1/3","1/4"=>"1/4","1/6"=>"1/6")
		));

		$orgzr->add(array(
			'name' => __( 'Display WooCommerce page title', 'waboot' ),
			'desc' => __( 'Check this box to show page title.', 'waboot' ),
			'id'   => 'waboot_woocommerce_displaytitle',
			'std'  => '1',
			'type' => 'checkbox'
		));

		$orgzr->add(array(
			'name' => __('Title position', 'waboot'),
			'desc' => __('Select where to display page title', 'waboot'),
			'id' => 'woocommerce_title_position',
			'std' => 'top',
			'type' => 'select',
			'options' => array('top' => __("Above primary","waboot"), 'bottom' => __("Below primary","waboot"))
		));

		$orgzr->add(array(
			'name' => __('Items for Row', 'waboot'),
			'desc' => __('How many items display for row', 'waboot'),
			'id' => 'woocommerce_cat_items',
			'std' => 'col-sm-3',
			'type' => 'select',
			'options' => array('col-sm-3' => '4', 'col-sm-4' => '3')
		));

		$orgzr->add(array(
			'name' => __('Products per page', 'waboot'),
			'desc' => __('How many products display per page', 'waboot'),
			'id' => 'woocommerce_products_per_page',
			'std' => '10',
			'type' => 'text'
		));

		$orgzr->add(array(
			'name' => __( 'Catalog Mode', 'waboot' ),
			'desc' => __( 'Hide add to cart button', 'waboot' ),
			'id'   => 'woocommerce_catalog',
			'std'  => '0',
			'type' => 'checkbox'
		));

		$orgzr->add(array(
			'name' => __( 'Hide Price', 'waboot' ),
			'desc' => __( 'Hide price in catalog', 'waboot' ),
			'id'   => 'woocommerce_hide_price',
			'std'  => '0',
			'type' => 'checkbox'
		));

		$orgzr->reset_group();
		$orgzr->reset_section();
	}
}

/*
class sampleWidget extends WP_Widget{
	...
}
*/