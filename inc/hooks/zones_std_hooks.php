<?php

namespace Waboot\hooks;

use WBF\includes\mvc\HTMLView;

function add_main_content(){
	get_template_part("templates/parts/main","blog");
}
\Waboot()->layout->add_zone_action("main",__NAMESPACE__."\\add_main_content");

function display_content_bottom_widget_area(){
	if(!is_active_sidebar("contentbottom")) return;
	get_template_part("templates/widget_areas/contentbottom");
}
\Waboot()->layout->add_zone_action("footer",__NAMESPACE__."\\display_content_bottom_widget_area");

function display_footer_widget_area(){
	if(!\Waboot\functions\count_widgets_in_area("footer") == 0) return;
	get_template_part("templates/widget_areas/footer");
}
\Waboot()->layout->add_zone_action("footer",__NAMESPACE__."\\display_footer_widget_area");

function display_footer_closure(){
	$default_footer_text = '&copy; ' . date('Y') . ' ' . get_bloginfo('name');
	$footer_text = \Waboot\functions\get_option('custom_footer_toggle') ? \Waboot\functions\get_option('waboot_custom_footer_text') : $default_footer_text; //todo: add this

	(new HTMLView("templates/footer-closure.php"))->clean()->display([
		'closure_width' => \Waboot\functions\get_option('closure_width','container'), //todo: add this
		'footer_text' => $footer_text,
		'display_socials' => \Waboot\functions\get_option("social_position_none") != 1 && \Waboot\functions\get_option('social_position') == "footer" //todo: tadd "social_position = footer" in some way
	]);
}
\Waboot()->layout->add_zone_action("footer",__NAMESPACE__."\\display_footer_closure");