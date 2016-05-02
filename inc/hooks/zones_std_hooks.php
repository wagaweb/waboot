<?php

namespace Waboot\hooks;

function add_main_content(){
	get_template_part("templates/parts/main","blog");
}
\Waboot()->layout->add_zone_action("main",__NAMESPACE__."\\add_main_content");

function display_content_bottom_widget_area(){
	if(!is_active_sidebar("contentbottom")) return;
	get_template_part("templates/widget_areas/contentbottom");
}
//\Waboot()->layout->add_zone_action("footer",__NAMESPACE__."\\display_content_bottom_widget_area");

function display_footer_widget_area(){
	if(!is_active_sidebar("footer")) return;
	get_template_part("templates/widget_areas/footer");
}
//\Waboot()->layout->add_zone_action("footer",__NAMESPACE__."\\display_content_bottom_widget_area");