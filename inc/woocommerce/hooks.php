<?php

namespace Waboot\woocommerce;

use WBF\modules\options\Organizer;

function alter_entry_title($title, $current_title_position){
	if(is_product_category()){
		$title = \Waboot\functions\get_archive_page_title();
	}elseif(is_shop()){
		$title = get_shop_page_title();
	}
	return $title;
}
add_filter("waboot/entry/title", __NAMESPACE__."\\alter_entry_title", 10, 2);

function alter_entry_title_visibility($is_visible, $current_title_position){
	if(is_product_category()){
		if($current_title_position == "top"){
			$is_visible = \Waboot\functions\get_option("woocommerce_title_position") == "top" && (bool) \Waboot\functions\get_option("woocommerce_display_title");
		}
	}elseif(is_shop()){
		if($current_title_position == "top"){
			$is_visible = \Waboot\functions\get_option("woocommerce_title_position") == "top" && (bool) \Waboot\functions\get_option("blog_display_title");
		}
	}
	return $is_visible;
}
add_filter("waboot/entry/title", __NAMESPACE__."\\alter_entry_title", 10, 2);

/**
 * @param Organizer $orgzr
 */
function register_options($orgzr){
	$imagepath = get_template_directory_uri()."/assets/images/options/";

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

	$orgzr->set_group("wc_options");

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
		'id'   => 'woocommerce_display_title',
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
add_action("wbf/theme_options/register", __NAMESPACE__.'\\register_options', 14);