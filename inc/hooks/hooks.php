<?php

namespace Waboot\hooks;

/**
 * Add header metas
 */
function add_header_metas(){
	get_template_part("templates/parts/meta");
}
add_action("waboot/head/start",__NAMESPACE__."add_header_meta");

function add_apple_touch_icon(){
	?>
	<link rel="apple-touch-icon" href="<?php apply_filters("waboot/assets/apple-touch-icon-path","apple-touch-icon.png"); ?>">
	<?php
}
add_action("waboot/head/meta",__NAMESPACE__."add_apple_touch_icon");