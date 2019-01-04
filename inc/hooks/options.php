<?php

namespace Waboot\hooks\options;

use Waboot\Layout;
use WBF\includes\GoogleFontsRetriever;
use WBF\modules\options\Organizer;

add_action( 'wbf/theme_options/register', __NAMESPACE__ . '\\register_options', 13);
add_filter( 'wbf/modules/behaviors/available', __NAMESPACE__ . "\\register_behaviors");

//Ordering filters:
add_filter( 'wbf/modules/options/organizer/sections', __NAMESPACE__ . "\\reorder_sections",10,2);
add_filter( 'wbf/modules/options/organizer/output', __NAMESPACE__ . "\\reorder_output",10,2);

//Filtering the Google Fonts API KEY
add_filter( 'wbf/google_fonts_api_key', function($apiKey){
	$wbApiKey = \Waboot\functions\get_option('google_fonts_api_key','');
	if( $wbApiKey !== '' && \is_string($wbApiKey) ){
		$apiKey = $wbApiKey;
	}
	return $apiKey;
} );

/**
 * Register standard theme options
 *
 * @param Organizer $orgzr
 */
function register_options($orgzr){
	$imagepath = get_template_directory_uri() . '/assets/images/options/';

	$layouts = \WBF\modules\options\of_add_default_key(_get_available_body_layouts());
	$final_layout = [];
	if(isset($layouts['values'][0]['thumb'])){
		$opt_type_for_layouts = 'images';
		foreach($layouts['values'] as $k => $v){
			$final_layout[$v['value']]['label'] = $v['name'];
			$final_layout[$v['value']]['value'] = isset($v['thumb']) ? $v['thumb'] : '';
		}
	}else{
		$opt_type_for_layouts = 'select';
		foreach($layouts['values'] as $k => $v){
			$final_layout[$v['value']]['label'] = $v['name'];
		}
	}

	$orgzr->set_group( 'std_options' );

	/**********************
	 * GLOBALS
	 **********************/

	$orgzr->add_section( 'global',_x( 'Global', 'Theme options', 'waboot'));

	$orgzr->add(array(
		'name' => __( 'Main logo', 'waboot' ),
		'desc' => __( 'Choose the website main logo', 'waboot' ),
		'id'   => 'desktop_logo',
		'std'  => get_template_directory_uri() . '/assets/images/default/waboot-color.png',
		'type' => 'upload'
	));

	$orgzr->add(array(
		'name' => __( 'Mobile logo', 'waboot' ),
		'desc' => __( 'Choose website mobile logo', 'waboot' ),
		'id'   => 'mobile_logo',
		'std'  => '',
		'type' => 'upload'
	));

	$orgzr->add([
		'name' => _x('Site title custom text', 'Theme options', 'waboot'),
		'desc' => _x('When logo is empty, the site title will be used instead. You can customize here the text that will be displayed', 'Theme options', 'waboot'),
		'id' => 'custom_site_title',
		'std' => get_bloginfo('name'),
		'type' => 'text',
	]);

	$orgzr->add([
		'name' => _x('Show site description', 'Theme options', 'waboot'),
		'desc' => _x('Choose visibility of site description', "Theme options", 'waboot'),
        'class' => 'half_option',
		'id'   => 'show_site_description',
		'std'  => '0',
		'type' => 'checkbox'
	]);

	$orgzr->add([
		'name' => _x( 'Load Waboot default styles', 'Theme options', 'waboot' ),
		'desc' => _x( 'Choose whether load waboot default styles', 'Theme options', 'waboot' ),
		'id' => 'load_waboot_styles',
		'std'  => '1',
		'type' => 'checkbox'
	]);

	/*
	 * CUSTOM CSS TAB
	 */

	$orgzr->add_section( 'custom_css',_x( 'Custom CSS', "Theme Options", 'waboot' ));

	$orgzr->set_section("custom_css");

	$orgzr->add(array(
		'name' => _x( 'Custom CSS', "Theme Options", 'waboot' ),
		'desc' => _x( 'Enter custom css to apply to the theme (press CTRL-SPACE on Windows, or CTRL-F on Mac for suggestions).', 'Theme Options', 'waboot' ),
		'id'   => 'custom_css',
		'type' => 'csseditor'
	));

	$orgzr->reset_group();
	$orgzr->reset_section();

	/**********************
	 * LAYOUT
	 **********************/

	$orgzr->add_section( 'layout',__( 'Layout', 'waboot' ));

	$orgzr->set_section( 'layout' );

	$orgzr->add(array(
		'name' => __('Page', 'waboot'),
		'desc' => __('Select page width. Fluid or Boxed?', 'waboot'),
		'id' => 'page_width',
		'std' => Layout::GRID_CLASS_CONTAINER,
		'type' => 'images',
		'options' => array(
			Layout::GRID_CLASS_CONTAINER_FLUID => array (
				'label' => 'Fluid',
				'value' => $imagepath . 'layout/page-fluid.png'
			),
			Layout::GRID_CLASS_CONTAINER => array (
				'label' => 'Boxed',
				'value' => $imagepath . 'layout/page-boxed.png'
			)
		)
	));

    /**********************
     * BLOG
     **********************/

    $orgzr->add_section( 'blog',__( 'Blog', 'waboot' ));

    $orgzr->set_section( 'blog' );

    $orgzr->add(array(
        'name' => __( 'Display Blog page title', 'waboot' ),
        'desc' => __( 'Check this box to show blog page title.', 'waboot' ),
        'class' => 'half_option',
        'id'   => 'blog_display_title',
        'std'  => '1',
        'type' => 'checkbox'
    ));

    $orgzr->add(array(
        'name' => __('Blog page title position', 'waboot'),
        'desc' => __('Select where to display page title of blog page', 'waboot'),
        'class' => 'half_option',
        'id' => 'blog_title_position',
        'std' => 'top',
        'type' => 'select',
        'options' => array('top' => __("Above primary",'waboot'), 'bottom' => __("Below primary",'waboot'))
    ));

	$orgzr->add(array(
		'name' => __('Index page and blog page layout', 'waboot'),
		'desc' => __('Select the layout that will be applied to main blog page (which can be the default index or a custom blog page)', 'waboot'),
		'id' => 'blog_layout',
		'std' => $layouts['default'],
		'type' => $opt_type_for_layouts,
		'options' => $final_layout
	));

	$orgzr->add(array(
		'name' => __( 'Primary Sidebar width', 'waboot' ),
		'desc' => __( 'Choose the primary sidebar width', 'waboot' ),
        'class' => 'half_option',
		'id' => 'blog_primary_sidebar_size',
		'std' => '1/4',
		'type' => 'select',
		'options' => [ '1/2' => '1/2', '1/3' => '1/3', '1/4' => '1/4', '1/6' => '1/6' ]
	));

	$orgzr->add( array(
		'name'    => __( 'Secondary Sidebar width', 'waboot' ),
		'desc'    => __( 'Choose the secondary sidebar width', 'waboot' ),
		'class'   => 'half_option',
		'id'      => 'blog_secondary_sidebar_size',
		'std'     => '1/4',
		'type'    => 'select',
		'options' => [ '1/2' => '1/2', '1/3' => '1/3', '1/4' => '1/4', '1/6' => '1/6' ]
	) );

    $orgzr->add(array(
        'name' => __( 'Show navigation above posts?', 'waboot' ),
        'desc' => __( 'Displays page navigation above category archives. Default is hide. Check this box to show content nav above posts.', 'waboot' ),
        'class' => 'half_option',
        'id'   => 'show_content_nav_above',
        'std'  => '0',
        'type' => 'checkbox'
    ));

    $orgzr->add(array(
        'name' => __( 'Show navigation below posts?', 'waboot' ),
        'desc' => __( 'Displays page navigation below category archives. Default is show. Uncheck this box to hide content nav above posts.', 'waboot' ),
        'class' => 'half_option',
        'id'   => 'show_content_nav_below',
        'std'  => '1',
        'type' => 'checkbox'
    ));

    $orgzr->add([
        'name' => __( 'Show post published date?', 'waboot' ),
        'desc' => __( 'Displays the date the article was posted. Default is show. Uncheck this box to hide post published date.', 'waboot' ),
        'class' => 'half_option',
        'id'   => 'show_post_date',
        'std'  => '1',
        'type' => 'checkbox'
    ]);

    $orgzr->add([
        'name' => __( 'Display published date using relative time format', 'waboot' ),
        'desc' => __( 'Displays the date the article was posted using relative format (eg: one hour ago). Default is to use absolute format.', 'waboot' ),
        'class' => 'half_option',
        'id'   => 'show_post_relative_time',
        'std'  => '0',
        'type' => 'checkbox'
    ]);

    $orgzr->add([
        'name' => __( 'Show post categories?', 'waboot' ),
        'desc' => __( 'Displays the categories in which a post was published. Default is show. Uncheck this box to hide post categories.', 'waboot' ),
        'class' => 'half_option',
        'id'   => 'show_post_categories',
        'std'  => '1',
        'type' => 'checkbox'
    ]);

    $orgzr->add([
        'name' => __( 'Show post tags?', 'waboot' ),
        'desc' => __( 'Displays the tags attached to a post. Default is show. Uncheck this box to hide post tags.', 'waboot' ),
        'class' => 'half_option',
        'id'   => 'show_post_tags',
        'std'  => '1',
        'type' => 'checkbox'
    ]);

    $orgzr->add([
        'name' => __( 'Show post author?', 'waboot' ),
        'desc' => __( 'Displays the post author. Default is show. Uncheck this box to hide the post author.', 'waboot' ),
        'class' => 'half_option',
        'id'   => 'show_post_author',
        'std'  => '1',
        'type' => 'checkbox'
    ]);

    $orgzr->add(array(
        'name' => __( 'Show link to comments?', 'waboot' ),
        'desc' => __( 'Displays the number of comments and/or a Leave a comment message on posts. Default is show. Uncheck this box to hide.' ,'waboot' ),
        'class' => 'half_option',
        'id'   => 'show_post_comments_link',
        'std'  => '1',
        'type' => 'checkbox'
    ));

	/**********************
	 * ARCHIVES
	 **********************/

	$taxonomies = \call_user_func(function(){
		$taxs = get_taxonomies([
			'public'   => true,
			'_builtin' => false
		],'objects');
		$taxs = array_filter($taxs,function($v){
			$itsok = true;
			$unwanted = ['nav_menu','post_tag','category','post_format'];
			if ( \in_array( $v->name, $unwanted ) ) {
				$itsok = false;
			}
			if ( \preg_match( '/^product_|pa_/', $v->name ) ) {
				$itsok = false;
			}
			return $itsok;
		});
		$taxs = apply_filters('waboot/options/archives_taxonomies',$taxs);
		return $taxs;
	});

	if(\is_array($taxonomies) && !empty($taxonomies)){
		$orgzr->add_section( 'archives',__( 'Archives', 'waboot' ));

		$orgzr->set_section( 'archives' );

		foreach ($taxonomies as $tax_slug => $taxonomy){
			//Post type heading
			$orgzr->add([
				'name' => $taxonomy->label.' ('.$taxonomy->name.')',
				'desc' => sprintf(__( 'Edit default options for "%s" archives', 'waboot' ),$taxonomy->label.' ('.$taxonomy->name.')'),
				'type' => 'info'
			]);

			$orgzr->add(array(
				'name' => __( 'Display archive page title', 'waboot' ),
				'desc' => __( 'Check this box to show blog page title.', 'waboot' ),
				'class' => 'half_option',
				'id'   => 'archive_'.$tax_slug.'_display_title',
				'std'  => '1',
				'type' => 'checkbox'
			));

			$orgzr->add(array(
				'name' => __('Archive page title position', 'waboot'),
				'desc' => __('Select where to display page title of the archive page', 'waboot'),
				'class' => 'half_option',
				'id' => 'archive_'.$tax_slug.'_title_position',
				'std' => 'top',
				'type' => 'select',
				'options' => array( 'top' => __( 'Above primary', 'waboot' ), 'bottom' => __( 'Below primary','waboot'))
			));

			$orgzr->add(array(
				'name' => __('Archive page layout', 'waboot'),
				'desc' => __('Select the layout that will be applied to the archive page', 'waboot'),
				'id' => 'archive_'.$tax_slug.'_layout',
				'std' => $layouts['default'],
				'type' => $opt_type_for_layouts,
				'options' => $final_layout
			));

			$orgzr->add(array(
				'name' => __( 'Primary Sidebar width','waboot'),
				'desc' => __( 'Choose the primary sidebar width','waboot'),
				'class' => 'half_option',
				'id' => 'archive_'.$tax_slug.'_primary_sidebar_size',
				'std' => '1/4',
				'type' => "select",
				'options' => [ "1/2" =>"1/2", "1/3" =>"1/3", "1/4" =>"1/4", "1/6" =>"1/6" ]
			));

			$orgzr->add(array(
				'name' => __("Secondary Sidebar width",'waboot'),
				'desc' => __("Choose the secondary sidebar width",'waboot'),
				'class' => 'half_option',
				'id' => 'archive_'.$tax_slug.'_secondary_sidebar_size',
				'std' => '1/4',
				'type' => "select",
				'options' => [ "1/2" =>"1/2", "1/3" =>"1/3", "1/4" =>"1/4", "1/6" =>"1/6" ]
			));
		}
	}

	$orgzr->reset_group();
	$orgzr->reset_section();
}

/**
 * Register Waboot Behaviors.
 *
 * Behaviors are registered as options by Behaviors WBF Module @ register_behaviors_as_theme_options()
 *
 * @param array $behaviors
 *
 * @return array
 */
function register_behaviors($behaviors){

	$imagepath = get_template_directory_uri() . '/assets/images/options';

	$behaviors[] = [
		"name" => "show-title",
		"title" => __("Display page title",'waboot'),
		"desc" => __("Default rendering value for page title",'waboot'),
        'class' => 'half_option',
		"options" => [
			[
				"name" => __("Yes"),
				"value" => 1
			],
			[
				"name" => __("No"),
				"value" => 0
			]
		],
		"type" => "select",
		"default" => 1,
		"valid" => ["page","post","-{blog}","{cpt}","-slideshow","-{ctag:\\Waboot\\woocommerce\\is_shop}"]
	];

	$behaviors[] = [
		"name" => "title-position",
		"title" => __("Title position",'waboot'),
		"desc" => __("Default title positioning in pages",'waboot'),
        'class' => 'half_option',
		"type" => "select",
		"options" => [
			[
				"name" => __("Above primary",'waboot'),
				"value" => "top"
			],
			[
				"name" => __("Below primary",'waboot'),
				"value" => "bottom"
			]
		],
		"default" => "top",
		"valid" => ["page","post","-{blog}","{cpt}","-slideshow","-{ctag:\\Waboot\\woocommerce\\is_shop}"]
	];

	$body_layouts = \WBF\modules\options\of_add_default_key(_get_available_body_layouts());
	$behaviors[] = array(
		"name" => "layout",
		"title" => __("Body layout",'waboot'),
		"desc" => __("Default body layout for posts and pages",'waboot'),
		"options" => $body_layouts['values'],
		"type" => "select",
		"default" => $body_layouts['default'],
		"valid" => ["page","post","-{blog}","{cpt}","-slideshow","-{ctag:\\Waboot\\woocommerce\\is_shop}"],
	);

	$behaviors[] = [
		'name' => 'content-width',
		'title' => __( 'Content Width', 'waboot' ),
		'desc' => __( 'Select page content wrapper width. Fluid or Boxed?', 'waboot' ),
		'type' => 'select',
		'options' => [
			[
				'name' => 'Boxed',
				'value' => Layout::GRID_CLASS_CONTAINER,
				'thumb' => $imagepath . '/layout/page-boxed.png'
			],
			[
				'name' => 'Fluid',
				'value' => Layout::GRID_CLASS_CONTAINER_FLUID,
				'thumb' => $imagepath . '/layout/page-fluid.png'
			],
		],
		'default' => Layout::GRID_CLASS_CONTAINER,
		"valid" => ["page","post","{cpt}"]
	];

	$behaviors[] = array(
		"name" => "show-content-nav-above",
		"title" => __( 'Show content navigation above posts?', 'waboot' ),
		"desc" => __( 'Displays links to next and previous posts above single post content. Default is hide. Check this box to show content nav above posts.', 'waboot' ),
		"type" => "checkbox",
		"default" => "0",
		"valid" => array("post","page","{cpt}")
	);

	$behaviors[] = array(
		"name" => "show-content-nav-below",
		"title" => __( 'Show content navigation below post?', 'waboot' ),
		"desc" => __( 'Displays links to next and previous posts below single post content. Default is show. Uncheck this box to hide content nav above posts.', 'waboot' ),
		"type" => "checkbox",
		"default" => "1",
		"valid" => array("post","page","{cpt}")
	);

	$behaviors[] = [
		'name' => 'primary-sidebar-size',
		'title' => __("Primary Sidebar width",'waboot'),
		'desc' => __("Choose the primary sidebar width",'waboot'),
        'class' => 'half_option',
		'type' => "select",
		'options' => [
			[
				"name" => __("1/2",'waboot'),
				"value" => "1/2"
			],
			[
				"name" => __("1/3",'waboot'),
				"value" => "1/3"
			],
			[
				"name" => __("1/4",'waboot'),
				"value" => "1/4"
			],
			[
				"name" => __("1/6",'waboot'),
				"value" => "1/6"
			]
		],
		"default" => "1/4",
		"valid" => ['*','-slideshow',"-{ctag:\\Waboot\\woocommerce\\is_shop}","-{blog}"]
	];

	$behaviors[] = [
		'name' => 'secondary-sidebar-size',
		'title' => __("Secondary Sidebar width",'waboot'),
		'desc' => __("Choose the secondary sidebar width",'waboot'),
        'class' => 'half_option',
		'type' => "select",
		'options' => [
			[
				"name" => __("1/2",'waboot'),
				"value" => "1/2"
			],
			[
				"name" => __("1/3",'waboot'),
				"value" => "1/3"
			],
			[
				"name" => __("1/4",'waboot'),
				"value" => "1/4"
			],
			[
				"name" => __("1/6",'waboot'),
				"value" => "1/6"
			]
		],
		"default" => "1/4",
		"valid" => ['*','-slideshow',"-{ctag:\\Waboot\\woocommerce\\is_shop}","-{blog}"]
	];

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

/**
 * Get options for available body layouts in behaviors and options
 *
 * @return mixed
 */
function _get_available_body_layouts(){

	$imagepath = get_template_directory_uri() . '/assets/images/options/';

	return apply_filters("waboot/layout/options/available_body_layouts",[
		//1
		[
			"name" => __("No sidebar",'waboot'),
			"value" => Layout::LAYOUT_FULL_WIDTH,
			"thumb"   => $imagepath . "behaviour/no-sidebar.png"
		],
		//2
		[
			"name" => __("Sidebar right",'waboot'),
			"value" => Layout::LAYOUT_PRIMARY_RIGHT,
			"thumb"   => $imagepath . "behaviour/sidebar-right.png"
		],
		//3
		[
			"name" => __("Sidebar left",'waboot'),
			"value" => Layout::LAYOUT_PRIMARY_LEFT,
			"thumb"   => $imagepath . "behaviour/sidebar-left.png"
		],
		//4
		[
			"name" => __("2 Sidebars",'waboot'),
			"value" => Layout::LAYOUT_TWO_SIDEBARS,
			"thumb"   => $imagepath . "behaviour/sidebar-left-right.png"
		],
		//5
		[
			"name" => __("2 Sidebars right",'waboot'),
			"value" => Layout::LAYOUT_TWO_SIDEBARS_RIGHT,
			"thumb"   => $imagepath . "behaviour/sidebar-right-2.png"
		],
		//6
		[
			"name" => __("2 Sidebars left",'waboot'),
			"value" => Layout::LAYOUT_TWO_SIDEBARS_LEFT,
			"thumb"   => $imagepath . "behaviour/sidebar-left-2.png"
		],
		'_default' => 'sidebar-right'
	]);
}

/**
 * Reorder theme options sections
 *
 * @param $sections
 * @param $orgzr
 *
 * @return mixed
 */
function reorder_sections($sections,$orgzr){
	$priorities = [
		'global' => 0,
		'default' => 1,
		'layout' => 2,
        'header' => 3,
        'navigation' => 4,
        'footer' => 5,
        'blog' => 6,
        'behaviors' => 90,
        'etc' => 95,
        'custom_css' => 99
	];

	uksort($sections,function($a,$b) use($priorities){
		if(preg_match("/_component/",$a)){
			return 1;
		}elseif(preg_match("/_component/",$b)){
			return -1;
		}else{
			if(array_key_exists($a,$priorities)){
				$a_val = $priorities[$a];
			}else{
				$a_val = $priorities['etc'];
			}
			if(array_key_exists($b,$priorities)){
				$b_val = $priorities[$b];
			}else{
				$b_val = $priorities['etc'];
			}
			$r = $a_val < $b_val ? -1 : 1;
			if($a_val == $b_val) $r = 1;
			return $r;
		}
	});
	return $sections;
}

/**
 * Reorder theme options output
 *
 * @param $options
 * @param $orgzr
 *
 * @return mixed
 */
function reorder_output($options,$orgzr){
	return $options;
}