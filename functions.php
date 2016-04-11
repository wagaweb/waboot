<?php

if(!class_exists("WBF")){
	add_action("init",function(){
		echo "This theme requires WBF Framework to work properly, please install";
	});
	return;
}else{
	waboot_init();
}

/**
 * Initialize Waboot
 */
function waboot_init(){
	$waboot_includes = [
		'inc/template-functions.php',
		'inc/template-tags.php',
		'inc/Layout.php',
		'inc/Theme.php'
	];

	//Require mandatory files
	foreach ($waboot_includes as $file) {
		if (!$filepath = locate_template($file)) {
			trigger_error(sprintf(__('Error locating %s for inclusion', 'waboot'), $file), E_USER_ERROR);
		}
		require_once $filepath;
	}
	unset($file, $filepath);

	//Init
	$wb = Waboot()->load_hooks();

	//Build up the theme
	$wb->layout->create_zone("header",new \WBF\includes\mvc\HTMLView("templates/header.php"),["always_load"=>true]);
	$wb->layout->create_zone("aside-primary",new \WBF\includes\mvc\HTMLView("templates/aside.php"));
	$wb->layout->create_zone("main",new \WBF\includes\mvc\HTMLView("templates/main.php"),["always_load"=>true]);
	$wb->layout->create_zone("aside-secondary",new \WBF\includes\mvc\HTMLView("templates/aside.php"));
	$wb->layout->create_zone("footer",new \WBF\includes\mvc\HTMLView("templates/footer.php"),["always_load"=>true]);

	//Loads std hooks
	$zone_std_hooks_file = locate_template("inc/hooks/zones_std_hooks.php");
	if($zone_std_hooks_file){
		require_once $zone_std_hooks_file;
	}else{
		trigger_error(sprintf(__('Error locating %s for inclusion', 'waboot'), $zone_std_hooks_file), E_USER_ERROR);
	}
}