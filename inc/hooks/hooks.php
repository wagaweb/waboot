<?php

namespace Waboot\hooks;

/**
 * Add header metas
 */
function add_header_metas(){
	get_template_part("templates/parts/meta");
}
add_action("waboot/head/start",__NAMESPACE__."add_header_meta");