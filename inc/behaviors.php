<?php

add_filter("waboot_add_behaviors","waboot_behaviors");
function waboot_behaviors($behaviors){

	$behaviors[] = array(
		"name" => "show-title",
        "title" => "Display page title",
        "desc" => "Default rendering value for page title",
        "options" => array(
            array(
	            "name" => "Yes",
                "value" => 1
            ),
            array(
	            "name" => "No",
                "value" => 0
            )
        ),
        "type" => "select",
        "default" => 1,
        "valid" => array("page")
	);

	$behaviors[] = array(
		"name" => "title-position",
        "title" => "Title position",
        "desc" => "Default title positioning in pages",
        "type" => "select",
        "options" => array(
            array(
	            "name" => "Top",
                "value" => "top"
			),
            array(
	            "name" => "Bottom",
                "value" => "bottom"
			)
		),
        "default" => "top",
        "valid" => array("page")
	);

	$behaviors[] = array(
		"name" => "layout",
        "title" => "Body layout",
        "desc" => "Default body layout for posts and pages",
        "options" => array(
            array(
	            "name" => "Full width. No sidebar.",
                "value" => "full-width"
            ),
            array(
	            "name" => "Sidebar right",
                "value" => "sidebar-right"
            ),
            array(
	            "name" => "Sidebar left",
                "value" => "sidebar-left"
			)
		),
        "type" => "select",
        "default" => "sidebar-right",
        "valid" => array("post","page")
	);

	$behaviors[] = array(
		"name" => "testinput",
        "title" => "Test Input",
        "desc" => "This is a test input",
        "type" => "text",
        "default" => "testme!",
        "valid" => array("post","page")
	);

	$behaviors[] = array(
		"name" => "testarea",
        "title" => "Test Input",
        "desc" => "This is a test textarea",
        "type" => "textarea",
        "default" => "testme!",
        "valid" => array("post","page")
	);

	return $behaviors;
}