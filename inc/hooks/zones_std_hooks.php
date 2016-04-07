<?php

namespace Waboot\hooks;

function add_main_content(){
	get_template_part("templates/parts/main","blog");
}
\Waboot()->layout->add_zone_action("main",__NAMESPACE__."\\add_main_content");