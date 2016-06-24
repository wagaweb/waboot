<?php

namespace Waboot\hooks;

use WBF\components\mvc\HTMLView;
use WBF\components\utils\Utilities;

/**
 * Renders main content into the "main" zone.
 */
function add_main_content(){
	/**
	 * @var \WP_Query
	 */
	global $wp_query;

	$page_type = Utilities::get_current_page_type();

	switch($page_type){
		case Utilities::PAGE_TYPE_DEFAULT_HOME:
			get_template_part("templates/wordpress/blog","content");
			break;
		case Utilities::PAGE_TYPE_STATIC_HOME:
			get_template_part("templates/wordpress/page","content");
			break;
		case Utilities::PAGE_TYPE_BLOG_PAGE:
			get_template_part("templates/wordpress/blog","content");
			break;
		case Utilities::PAGE_TYPE_COMMON:
			if($wp_query->is_single()){
				get_template_part("templates/wordpress/single","content");
			}elseif($wp_query->is_page()){
				get_template_part("templates/wordpress/page","content");
			}elseif($wp_query->is_author()){
				get_template_part("templates/wordpress/author","content");
			}elseif($wp_query->is_search()){
				get_template_part("templates/wordpress/search","content");
			}elseif($wp_query->is_archive()){
				get_template_part("templates/wordpress/archive","content");
			}elseif($wp_query->is_404()){
				get_template_part("templates/wordpress/404","content");
			}else{
				throw new \Exception("Unrecognized content type");
			}
			break;
		default:
			throw new \Exception("Unrecognized page type");
			break;
	}
}
\Waboot()->layout->add_zone_action("main",__NAMESPACE__."\\add_main_content");

/**
 * Renders the "contentbottom" sidebar into "footer" zone.
 */
function display_content_bottom_widget_area(){
	if(!is_active_sidebar("contentbottom")) return;
	get_template_part("templates/widget_areas/contentbottom");
}
\Waboot()->layout->add_zone_action("footer",__NAMESPACE__."\\display_content_bottom_widget_area");

/**
 * Renders "footer" sidebar into "footer" zone.
 */
function display_footer_widget_area(){
	if(!\Waboot\functions\count_widgets_in_area("footer") == 0) return;
	get_template_part("templates/widget_areas/footer");
}
\Waboot()->layout->add_zone_action("footer",__NAMESPACE__."\\display_footer_widget_area");

/**
 * Display closure into "footer" zone.
 */
function display_footer_closure(){
	$default_footer_text = '&copy; ' . date('Y') . ' ' . get_bloginfo('name');
	$footer_text = \Waboot\functions\get_option('custom_footer_toggle') ? \Waboot\functions\get_option('waboot_custom_footer_text') : $default_footer_text; //todo: add this

	(new HTMLView("templates/view-parts/footer-closure.php"))->clean()->display([
		'closure_width' => \Waboot\functions\get_option('closure_width','container'), //todo: add this
		'footer_text' => $footer_text,
		'display_socials' => \Waboot\functions\get_option("social_position_none") != 1 && \Waboot\functions\get_option('social_position') == "footer" //todo: tadd "social_position = footer" in some way
	]);
}
\Waboot()->layout->add_zone_action("footer",__NAMESPACE__."\\display_footer_closure");

/**
 * Adds aside actions to display sidebars
 */
function display_sidebars(){
	if(!\Waboot\functions\body_layout_is_full_width()){
		\Waboot()->layout->add_zone_action("aside-primary",__NAMESPACE__."\\display_primary_sidebar");
		if(\Waboot\functions\body_layout_has_two_sidebars()){
			\Waboot()->layout->add_zone_action("aside-secondary",__NAMESPACE__."\\display_secondary_sidebar");
		}
	}
}
add_action("after_setup_theme",__NAMESPACE__."\\display_sidebars", 11);

/**
 * Display the primary sidebar
 */
function display_primary_sidebar(){
	do_action("waboot/sidebar/primary/widgets/before");
	get_template_part("templates/widget_areas/aside-primary");
	do_action("waboot/sidebar/primary/widgets/after");
}

/**
 * Display the secondary sidebar
 */
function display_secondary_sidebar(){
	do_action("waboot/sidebar/secondary/widgets/before");
	get_template_part("templates/widget_areas/aside-secondary");
	do_action("waboot/sidebar/secondary/widgets/after");
}