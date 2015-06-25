<?php

add_filter("wbf_add_behaviors","waboot_behaviors");
function waboot_behaviors($behaviors){

	$imagepath = get_template_directory_uri() . '/wbf/admin/images/';

	$behaviors[] = array(
		"name" => "show-title",
        "title" => __("Display page title","waboot"),
        "desc" => __("Default rendering value for page title","waboot"),
        "options" => array(
            array(
	            "name" => __("Yes"),
                "value" => 1
            ),
            array(
	            "name" => __("No"),
                "value" => 0
            )
        ),
        "type" => "select",
        "default" => 1,
        "valid" => array("page","post","-{home}","{cpt}","-slideshow")
	);

	$behaviors[] = array(
		"name" => "title-position",
        "title" => __("Title position","waboot"),
        "desc" => __("Default title positioning in pages","waboot"),
        "type" => "select",
        "options" => array(
            array(
	            "name" => __("Above primary","waboot"),
                "value" => "top"
			),
            array(
	            "name" => __("Below primary","waboot"),
                "value" => "bottom"
			)
		),
        "default" => "top",
        "valid" => array("page","post","-{home}","{cpt}","-slideshow")
	);

    $body_layouts = \WBF\modules\options\of_add_default_key(waboot_get_available_body_layouts());
	$behaviors[] = array(
		"name" => "layout",
        "title" => __("Body layout","waboot"),
        "desc" => __("Default body layout for posts and pages","waboot"),
        "options" => $body_layouts['values'],
        "type" => "select",
        "default" => $body_layouts['default'],
        "valid" => array("page","post","-{home}","{cpt}","-slideshow"),
	);

    $behaviors[] = array(
        'name' => 'primary-sidebar-size',
        'title' => __("Primary Sidebar width","waboot"),
        'desc' => __("Choose the primary sidebar width","waboot"),
        'type' => "select",
        'options' => array(
            array(
                "name" => __("1/2","waboot"),
                "value" => "1/2"
            ),
            array(
                "name" => __("1/3","waboot"),
                "value" => "1/3"
            ),
            array(
                "name" => __("1/4","waboot"),
                "value" => "1/4"
            ),
            array(
                "name" => __("1/6","waboot"),
                "value" => "1/6"
            )
        ),
        "default" => "1/4",
        "valid" => array('*','-slideshow')
    );

	$behaviors[] = array(
		'name' => 'secondary-sidebar-size',
		'title' => __("Secondary Sidebar width","waboot"),
		'desc' => __("Choose the secondary sidebar width","waboot"),
		'type' => "select",
		'options' => array(
			array(
				"name" => __("1/2","waboot"),
				"value" => "1/2"
			),
			array(
				"name" => __("1/3","waboot"),
				"value" => "1/3"
			),
			array(
				"name" => __("1/4","waboot"),
				"value" => "1/4"
			),
			array(
				"name" => __("1/6","waboot"),
				"value" => "1/6"
			)
		),
		"default" => "1/4",
		"valid" => array('*','-slideshow')
	);

    /***********************************************
     ***************** SAMPLES *********************
     ***********************************************/

    /**
     * SINGLE CHECKBOX
     */
    /*$behaviors[] = array(
        "name" => "testcheck",
        "title" => "Test Checkboxes",
        "desc" => "This is a test checkbox",
        "type" => "checkbox",
        "default" => "1",
        "valid" => array("post","page")
    );*/

    /**
     * MULTIPLE CHECKBOX
     */
    /*$behaviors[] = array(
        "name" => "testmulticheck",
        "title" => "Test Checkboxes",
        "desc" => "This is a test checkbox",
        "type" => "checkbox",
        "options" => array(
            array(
                "name" => "test1",
                "value" => "test1"
            ),
            array(
                "name" => "test2",
                "value" => "test2"
            ),
        ),
        "default" => "test1",
        "valid" => array("post","page")
    );*/

    /**
     * RADIO
     */
    /*$behaviors[] = array(
		"name" => "testradio",
        "title" => "Test Radio",
        "desc" => "This is a test radio",
        "type" => "radio",
        "options" => array(
            array(
                "name" => "test1",
                "value" => "test1"
            ),
            array(
                "name" => "test2",
                "value" => "test2"
            ),
        ),
        "default" => "test2",
        "valid" => array("post","page")
	);*/

    /**
     * TEXT
     */
	/*$behaviors[] = array(
		"name" => "testinput",
        "title" => "Test Input",
        "desc" => "This is a test input",
        "type" => "text",
        "default" => "testme!",
        "valid" => array("post","page")
	);*/

    /**
     * TEXTAREA
     */
	/*$behaviors[] = array(
		"name" => "testarea",
        "title" => "Test Input",
        "desc" => "This is a test textarea",
        "type" => "textarea",
        "default" => "testme!",
        "valid" => array("post","page")
	);*/

	return $behaviors;
}