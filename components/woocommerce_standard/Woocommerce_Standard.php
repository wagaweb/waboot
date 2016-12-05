<?php
/**
Component Name: WooCommerce Standard
Description: An initial customization for WooCommerce
Category: Utilities
Tags: Woocommerce
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
*/

if(!class_exists("\\WBF\\modules\\components\\Component")) return;

class Woocommerce_Standard extends \WBF\modules\components\Component{

    /**
     * This method will be executed at Wordpress startup (every page load)
     */
    public function setup(){
	    global $woocommerce;
        parent::setup();
	    if(!isset($woocommerce)) return;
	    $this->declare_hooks();
    }

    private function declare_hooks(){
		//Disable the default Woocommerce stylesheet
	    add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

		//Disabling actions
	    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	    remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	    remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

		//Enable the modification of woocommerce query and loop
	    add_filter("loop_shop_per_page", [$this,"alter_posts_per_page"], 20);
	    add_filter("post_class", [$this,"alter_post_class"], 20, 3);

		//Layout altering:
	    add_filter("waboot/entry/title/display_flag", [$this,"alter_entry_title_visibility"], 10, 2);
	    add_filter("waboot/layout/body_layout", [$this,"alter_body_layout"], 90);
	    add_filter("waboot/layout/get_cols_sizes", [$this,"alter_col_sizes"], 90);
	    add_action('init', [$this,"hidePriceAndCart"], 20);

	    //Behaviors
	    add_filter("wbf/modules/behaviors/get/primary-sidebar-size", [$this,"primary_sidebar_size_behavior"], 999);
	    add_filter("wbf/modules/behaviors/get/secondary-sidebar-size", [$this,"secondary_sidebar_size_behavior"], 999);

		// Theme Options

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
    	//wp_register_style("component-{$this->name}-style",$this->directory_uri . '/assets/dist/css/woocommerce-standard.min.css');
	    //wp_enqueue_style("component-{$this->name}-style");
	    Waboot()->add_inline_style("component-{$this->name}-style",$this->directory_uri . '/assets/dist/css/woocommerce-standard.min.css');
    }

	/**
	 * This is an action callback.
	 *
	 * Here you can use WBF Organizer to set component options
	 */
	public function register_options(){
		$orgzr = \WBF\modules\options\Organizer::getInstance();

		$layouts = \WBF\modules\options\of_add_default_key(\Waboot\hooks\options\_get_available_body_layouts());
		if(isset($layouts['values'][0]['thumb'])){
			$opt_type = "images";
			foreach($layouts['values'] as $k => $v){
				$final_layouts[$v['value']]['label'] = $v['name'];
				$final_layouts[$v['value']]['value'] = isset($v['thumb']) ? $v['thumb'] : "";
			}
		}else{
			$opt_type = "select";
			foreach($layouts['values'] as $k => $v){
				$final_layouts[$v['value']]['label'] = $v['name'];
			}
		}

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

		$orgzr->add(array(
			'name' => __('WooCommerce Shop Layout', 'waboot'),
			'desc' => __('Select WooCommerce shop page layout', 'waboot'),
			'id' => 'woocommerce_shop_sidebar_layout',
			'std' => $layouts['default'],
			'type' => $opt_type,
			'options' => $final_layouts
		));

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
			'id'   => 'woocommerce_shop_display_title',
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

		$orgzr->add(array(
			'name' => __('WooCommerce Archive Layout', 'waboot'),
			'desc' => __('Select Woocommerce archive layout', 'waboot'),
			'id' => 'woocommerce_sidebar_layout',
			'std' => $layouts['default'],
			'type' => $opt_type,
			'options' => $final_layouts
		));

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
			'id'   => 'woocommerce_archives_display_title',
			'std'  => '1',
			'type' => 'checkbox'
		));

		$orgzr->add(array(
			'name' => __('Title position', 'waboot'),
			'desc' => __('Select where to display page title', 'waboot'),
			'id' => 'woocommerce_archives_title_position',
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

	/**
	 * Adds conditions by title displaying
	 *
	 * @param $can_display_title
	 * @param $current_title_position
	 *
	 * @return bool
	 */
	public function alter_entry_title_visibility($can_display_title, $current_title_position){
		switch($current_title_position){
			//Print entry header INSIDE the entries:
			case "bottom":
				//PLEASE NOTE: in reality, we need the "top" condition ONLY. The bottom condition is handled in our archive-product.php
				if(\is_product_category()){
					$can_display_title = \Waboot\functions\get_option("woocommerce_archives_title_position") == "bottom" && (bool) \Waboot\functions\get_option("woocommerce_shop_archives_title");
				}elseif(\is_shop()){
					$can_display_title = \Waboot\functions\get_option("woocommerce_shop_title_position") == "bottom" && (bool) \Waboot\functions\get_option("woocommerce_shop_display_title");
				}
				break;
			//Print entry header OUTSIDE the single entry:
			case "top":
				if(\is_product_category()){
					$can_display_title = \Waboot\functions\get_option("woocommerce_archives_title_position") == "top" && (bool) \Waboot\functions\get_option("woocommerce_shop_archives_title");
				}elseif(\is_shop()){
					$can_display_title = \Waboot\functions\get_option("woocommerce_shop_title_position") == "top" && (bool) \Waboot\functions\get_option("woocommerce_shop_display_title");
				}
				break;
		}
		return $can_display_title;
	}

	/**
	 * Alter body layout for WooCommerce part of the site
	 *
	 * @hooked "waboot/layout/body_layout"
	 *
	 * @param $layout
	 *
	 * @return string
	 */
	public function alter_body_layout($layout){
		if(is_product_category()){
			$layout = \Waboot\functions\get_option('woocommerce_sidebar_layout');
		}elseif(is_shop()) {
			$layout = \Waboot\functions\get_option('woocommerce_shop_sidebar_layout');
		}
		return $layout;
	}

	/**
	 * Alter col sizes for WooCommerce part of the site
	 *
	 * @hooked 'waboot/layout/get_cols_sizes'
	 *
	 * @param $sizes
	 *
	 * @return array
	 */
	public function alter_col_sizes($sizes){
		if(!is_woocommerce()) return $sizes;

		global $post;
		$do_calc = false;
		if(is_shop()){
			$sizes = array("main"=>12);
			//Primary size
			$primary_sidebar_width = \Waboot\functions\get_option('woocommerce_shop_primary_sidebar_size');
			if(!$primary_sidebar_width){
				$primary_sidebar_width = 0;
			}
			//Secondary size
			$secondary_sidebar_width = \Waboot\functions\get_option('woocommerce_shop_secondary_sidebar_size');
			if(!$secondary_sidebar_width){
				$secondary_sidebar_width = 0;
			}
			$do_calc = true;
		}elseif(is_product_category()){
			$sizes = array("main"=>12);
			//Primary size
			$primary_sidebar_width = \Waboot\functions\get_option('woocommerce_primary_sidebar_size');
			if(!$primary_sidebar_width) $primary_sidebar_width = 0;
			//Secondary size
			$secondary_sidebar_width = \Waboot\functions\get_option('woocommerce_secondary_sidebar_size');
			if(!$secondary_sidebar_width){
				$secondary_sidebar_width = 0;
			}
			$do_calc = true;
		}

		if($do_calc){
			if (\Waboot\functions\body_layout_has_two_sidebars()) {
				//Main size
				$mainwrap_size = 12 - Waboot()->layout->layout_width_to_int($primary_sidebar_width) - Waboot()->layout->layout_width_to_int($secondary_sidebar_width);
				$sizes = array("main"=>$mainwrap_size,"primary"=>Waboot()->layout->layout_width_to_int($primary_sidebar_width),"secondary"=>Waboot()->layout->layout_width_to_int($secondary_sidebar_width));
			}else{
				if(\Waboot\functions\get_body_layout() != "full-width"){
					$mainwrap_size = 12 - Waboot()->layout->layout_width_to_int($primary_sidebar_width);
					$sizes = array("main"=>$mainwrap_size,"primary"=>Waboot()->layout->layout_width_to_int($primary_sidebar_width));
				}
			}
		}

		return $sizes;
	}

	/**
	 * @param \WBF\modules\behaviors\Behavior $b
	 *
	 * @return \WBF\modules\behaviors\Behavior
	 */
	public function primary_sidebar_size_behavior(\WBF\modules\behaviors\Behavior $b){
		if(!is_woocommerce()) return $b;

		if(is_shop()){
			$primary_sidebar_width = \Waboot\functions\get_option('woocommerce_shop_primary_sidebar_size');
			if(!$primary_sidebar_width){
				$primary_sidebar_width = 0;
			}
			$b->value = $primary_sidebar_width;
		}elseif(is_product_category()){
			$primary_sidebar_width = \Waboot\functions\get_option('woocommerce_primary_sidebar_size');
			if(!$primary_sidebar_width){
				$primary_sidebar_width = 0;
			}
			$b->value = $primary_sidebar_width;
		}

		return $b;
	}

	/**
	 * @param \WBF\modules\behaviors\Behavior $b
	 *
	 * @return \WBF\modules\behaviors\Behavior
	 */
	public function secondary_sidebar_size_behavior(\WBF\modules\behaviors\Behavior $b){
		if(!is_woocommerce()) return $b;

		if(is_shop()){
			$secondary_sidebar_width = \Waboot\functions\get_option('woocommerce_shop_secondary_sidebar_size');
			if(!$secondary_sidebar_width){
				$secondary_sidebar_width = 0;
			}
			$b->value = $secondary_sidebar_width;
		}elseif(is_product_category()){
			$secondary_sidebar_width = \Waboot\functions\get_option('woocommerce_secondary_sidebar_size');
			if(!$secondary_sidebar_width){
				$secondary_sidebar_width = 0;
			}
			$b->value = $secondary_sidebar_width;
		}

		return $b;
	}

	/**
	 * Alter product per page
	 *
	 * @hooked 'loop_shop_per_page'
	 *
	 * @param string|int $posts_per_page
	 *
	 * @return int
	 */
	public function alter_posts_per_page($posts_per_page){
		$n = intval(\Waboot\functions\get_option('woocommerce_products_per_page'));
		if(is_integer($n)){
			$posts_per_page = $n;
		}
		return $posts_per_page;
	}

	/**
	 * Alter post class to display a different number or product per row
	 *
	 * @param $classes
	 * @param string $class
	 * @param string $post_id
	 *
	 * @hooked 'post_class'
	 *
	 * @return array
	 */
	public function alter_post_class($classes,$class = '', $post_id = ''){
		if ( ! $post_id || 'product' !== get_post_type( $post_id ) ) {
			return $classes;
		}

		if(is_admin()) return $classes;

		$classes[] = of_get_option('woocommerce_cat_items', 'col-sm-3');

		return $classes;
	}

	/**
	 * Hides prices (in catalog) and add-to-cart button
	 *
	 * @hooked 'init'
	 */
	function hidePriceAndCart(){
		if((bool) \Waboot\functions\get_option("woocommerce_hide_price")) {
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
			remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
		}
		if((bool) \Waboot\functions\get_option("woocommerce_catalog")) {
			remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
			remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
		}
	}
}