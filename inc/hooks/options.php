<?php

namespace Waboot\hooks\options;

use Waboot\Layout;
use WBF\modules\options\Organizer;

add_action("wbf/theme_options/register", __NAMESPACE__.'\\register_options', 13);
add_filter("wbf/modules/behaviors/available", __NAMESPACE__."\\register_behaviors");

//Ordering filters:
add_filter("wbf/modules/options/organizer/sections",__NAMESPACE__."\\reorder_sections",10,2);
add_filter("wbf/modules/options/organizer/output",__NAMESPACE__."\\reorder_output",10,2);

/**
 * Register standard theme options
 *
 * @param Organizer $orgzr
 */
function register_options($orgzr){
	$imagepath = get_template_directory_uri()."/assets/images/options/";

	$orgzr->set_group("std_options");

	/**********************
	 * GLOBALS
	 **********************/

	$orgzr->add_section("global",_x("Global", "Theme options","waboot"));

	$orgzr->add(array(
		'name' => __( 'Main logo', 'waboot' ),
		'desc' => __( 'Choose the website main logo', 'waboot' ),
		'id'   => 'desktop_logo',
		'std'  => get_template_directory_uri()."/assets/images/default/waboot-color.png",
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
		'name' => _x('Site title custom text', "Theme options", 'waboot'),
		'desc' => _x('When logo is empty, the site title will be used instead. You can customize here the text that will be displayed', "Theme options", 'waboot'),
		'id' => 'custom_site_title',
		'std' => get_bloginfo('name'),
		'type' => 'text',
	]);

	$orgzr->add([
		'name' => _x('Show site description', "Theme options", 'waboot'),
		'desc' => _x('Choose visibility of site description', "Theme options", 'waboot'),
        'class' => 'half_option',
		'id'   => 'show_site_description',
		'std'  => '0',
		'type' => 'checkbox'
	]);

	$orgzr->add(array(
		'name' => __( 'Show content navigation above posts?', 'waboot' ),
		'desc' => __( 'Displays links to next and previous posts above the current post and above the posts on the index page. Default is hide. Check this box to show content nav above posts.', 'waboot' ),
        'class' => 'half_option',
        'id'   => 'show_content_nav_above',
		'std'  => '0',
		'type' => 'checkbox'
	));

	$orgzr->add(array(
		'name' => __( 'Show content navigation below posts?', 'waboot' ),
		'desc' => __( 'Displays links to next and previous posts below the current post and below the posts on the index page. Default is show. Uncheck this box to hide content nav above posts.', 'waboot' ),
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

	/*
	 * BACKGROUNDS
	 */

	$orgzr->add([
		'name' => _x( 'Backgrounds', 'Theme options', 'waboot' ),
		'desc' => _x( 'Settings about page backgrounds', 'waboot' ),
		'type' => 'info'
	]);

	$orgzr->set_group("css_injection");

	$orgzr->add([
		'name' => _x('Background Page', 'Theme options', 'Theme options', 'waboot'),
		'desc' => _x('Change the page background color.', 'Theme options', 'waboot'),
		'id' => 'page_bgcolor',
		'type' => 'color',
		'std' => '#ffffff',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	]);

	$orgzr->add([
		'name' => _x('Background Content', 'Theme options', 'Theme options', 'waboot'),
		'desc' => _x('Change the content background color.', 'Theme options', 'waboot'),
		'id' => 'content_bgcolor',
		'type' => 'color',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	]);
	
	$orgzr->add([
		'name' => _x( 'Body Background Image', 'Theme options', 'waboot' ),
		'desc' => _x( 'Upload a background image, or specify the image address of your image. (http://yoursite.com/image.png)', 'Theme options', 'waboot' ),
		'id' => 'body_bgimage',
		'std' => '',
		'type' => 'upload',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	]);

	$orgzr->add([
		'name' => _x('Body Background Color', 'Theme options', 'waboot'),
		'desc' => _x('Change the body background color.', 'Theme options', 'waboot'),
		'id' => 'body_bgcolor',
		'std' => "#ededed",
		'type' => 'color',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	]);

	$orgzr->add([
		'name' => _x( 'Body Background Image Repeat', 'Theme options', 'waboot' ),
		'desc' => _x( 'Select how you want your background image to display.', 'waboot' ),
		'id' => 'body_bgrepeat',
		'type' => 'select',
		'options' => array( 'no-repeat' => 'No Repeat', 'repeat' => 'Repeat','repeat-x' => 'Repeat Horizontally', 'repeat-y' => 'Repeat Vertically' ),
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	]);

	$orgzr->add([
		'name' => _x( 'Body Background image position', 'Theme options', 'waboot' ),
		'desc' => _x( 'Select how you would like to position the background', 'waboot' ),
		'id' => 'body_bgpos',
		'std' => 'top left',
		'type' => 'select',
		'options' => array(
			'top left' => 'top left', 'top center' => 'top center', 'top right' => 'top right',
			'center left' => 'center left', 'center center' => 'center center', 'center right' => 'center right',
			'bottom left' => 'bottom left', 'bottom center' => 'bottom center', 'bottom right' => 'bottom right'
		),
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	]);

	$orgzr->add([
		'name' => _x( 'Body Background Attachment', 'Theme options', 'waboot' ),
		'desc' => _x( 'Select whether the background should be fixed or move when the user scrolls', 'Theme options', 'waboot' ),
		'id' => 'body_bgattach',
		'std' => 'scroll',
		'type' => 'select',
		'options' => array( 'scroll' => 'scroll','fixed' => 'fixed' ),
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	]);

	$orgzr->reset_group();
	$orgzr->set_group("std_options");

	/*
	 * BOOTSTRAP VARIABLES TAB
	 */

	$orgzr->add(array(
		'name' => _x( 'Style', 'Theme options', 'Theme options', 'waboot' ),
		'desc' => _x( 'Settings about css styles', 'Theme options', 'waboot' ),
		'type' => 'info'
	));

	$orgzr->set_group("css_injection");

	$orgzr->add(array(
		'name' => _x('Text color', 'Theme options', 'waboot'),
		'desc' => _x('Change the color of html, body, p, ul, li', 'Theme options', 'waboot'),
		'id' => 'btstrp_text_color',
		'std' => "#333333",
		'type' => 'color',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x('Title color', 'Theme options', 'waboot'),
		'desc' => _x('Change the color of h1, h2, h3, h4, h5, h6', 'Theme options', 'waboot'),
		'id' => 'btstrp_title_color',
		'std' => "#333333",
		'type' => 'color',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x('Primary color', 'Theme options', 'waboot'),
		'desc' => _x('Change the color of @brand-primary.', 'Theme options', 'waboot'),
		'id' => 'btstrp_brand_primary',
		'std' => "#428bca",
		'type' => 'color',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

    $orgzr->add(array(
        'name' => _x('Success color', 'Theme options', 'waboot'),
        'desc' => _x('Change the color of @brand-success.', 'Theme options', 'waboot'),
        'id' => 'btstrp_brand_success',
        'std' => "#5cb85c",
        'type' => 'color',
        'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
    ));

	$orgzr->add(array(
		'name' => _x('Info color', 'Theme options', 'waboot'),
		'desc' => _x('Change the color of @brand-info.', 'Theme options', 'waboot'),
		'id' => 'btstrp_brand_info',
		'std' => "#5bc0de",
		'type' => 'color',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x('Warning color', 'Theme options', 'waboot'),
		'desc' => _x('Change the color of @brand-warning.', 'Theme options', 'waboot'),
		'id' => 'btstrp_brand_warning',
		'std' => "#f0ad4e",
		'type' => 'color',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x('Danger color', 'Theme options', 'waboot'),
		'desc' => _x('Change the color of @brand-danger.', 'Theme options', 'waboot'),
		'id' => 'btstrp_brand_danger',
		'std' => "#d9534f",
		'type' => 'color',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x('Border radius base', 'Theme options', 'waboot'),
		'desc' => _x('Change the width in pixel of @border-radius-base.', 'Theme options', 'waboot'),
		'id' => 'btstrp_border_radius_base',
		'std' => "4",
		'type' => 'text',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x('Border radius large', 'Theme options', 'waboot'),
		'desc' => _x('Change the width in pixel of @border-radius-large.', 'Theme options', 'waboot'),
		'id' => 'btstrp_border_radius_lg',
		'std' => "6",
		'type' => 'text',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x('Border radius small', 'Theme options', 'waboot'),
		'desc' => _x('Change the width in pixel of @border-radius-small.', 'Theme options', 'waboot'),
		'id' => 'btstrp_border_radius_sm',
		'std' => "3",
		'type' => 'text',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x('Well background', "Theme Options", 'waboot'),
		'desc' => _x('Change the color of @well-bg.', "Theme Options", 'waboot'),
		'id' => 'btstrp_well_bg',
		'std' => "#f5f5f5",
		'type' => 'color',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->reset_group();
	$orgzr->set_group("std_options");

	/*
	 * TYPOGRAPHY
	 */

	$orgzr->add(array(
		'name' => _x( 'Typography', 'Theme options', 'waboot' ),
		'desc' => _x( 'Settings about typography', 'Theme options', 'waboot' ),
		'type' => 'info'
	));

	$orgzr->set_group("css_injection");

	$orgzr->add([
		'name' => _x('Fonts to load', "Theme Options", "waboot"),
		'id' => 'fonts',
		'css_selectors' => ['body,p,ul', 'h1,h2,h3', 'h4,h5,h6'],
		'std' => [],
		'type' => 'fonts_selector',
		'fonts_type' => 'google',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	]);

	/*$orgzr->add(array(
		'name' => _x('Primary font (body, p, ul, li)', "Theme Options", "waboot"),
		'id' => 'typo_primary_font',
		'std' => array(
			'family' => 'Source Sans Pro',
			'style'  => 'regular',
			'charset' => 'latin',
			'color'  => '#666666'
		),
		'type' => 'typography',
		'fonts_type' => 'google',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x('Secondary font (h1, h2, h3, h4, h5, h6)', 'Theme options', "waboot"),
		'id' => 'typo_secondary_font',
		'std' => array(
			'family' => 'Source Sans Pro',
			'style'  => 'bold',
			'charset' => 'latin',
			'color'  => '#666666'
		),
		'type' => 'typography',
		'fonts_type' => 'google',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));*/

	$orgzr->reset_group();
	$orgzr->set_group("std_options");

	/*
	 * FAVICON
	 */

	$orgzr->add(array(
		'name' => _x( 'Favicon', 'Theme options', 'waboot' ),
		'desc' => _x( 'Settings about typography', 'Theme options', 'waboot' ),
		'type' => 'info'
	));

	/*$orgzr->add(array(
		'name' => _x( 'Icon', 'waboot' ),
		'desc' => _x( 'Upload a favicon (only .png and .ico files are allowed).', "Theme Options", 'waboot' ),
		'id' => 'favicon_icon',
		'std' => '',
		'type' => 'upload',
		'readonly' => true,
		'upload_to' => ABSPATH,
		'upload_as' => 'favicon',
		'allowed_extensions' => array("png","ico"),
		'save_action' => "\\Waboot\\functions\\deploy_favicon"
	));

	$orgzr->add(array(
		'name' => _x( 'Apple Touch 120x120 Icon', "Theme Options", 'waboot' ),
		'desc' => _x( 'Upload a favicon (only .png and .ico files are allowed).', "Theme Options", 'waboot' ),
		'id' => 'favicon_apple120',
		'std' => '',
		'type' => 'upload',
		'readonly' => true,
		'upload_to' => ABSPATH,
		'upload_as' => 'touch-icon-iphone-retina',
		'allowed_extensions' => array("png","ico"),
		'save_action' => "\\Waboot\\functions\\deploy_favicon"
	));*/

	$orgzr->add(array(
		'name' => _x( 'Favicon (at least 152x152)', "Theme Options", 'waboot' ),
		'desc' => _x( 'Upload a favicon (only .png, .ico and .jpg files are allowed).', "Theme Options", 'waboot' ),
		'id' => 'favicon',
		'std' => '',
		'type' => 'upload',
		'readonly' => true,
		'allowed_extensions' => array("png","ico","jpg","jpeg"),
		'save_action' => "\\Waboot\\functions\\deploy_favicon"
	));

	/*
	 * CUSTOM CSS TAB
	 */

	$orgzr->add_section("custom_css",_x( 'Custom CSS', "Theme Options", 'waboot' ));

	$orgzr->set_section("custom_css");

	$orgzr->add(array(
		'name' => _x( 'Custom CSS', "Theme Options", 'waboot' ),
		'desc' => _x( 'Enter custom css to apply to the theme (press CTRL-SPACE on Windows, or CTRL-F on Mac for suggestions).', "Theme Options", 'waboot' ),
		'id'   => 'custom_css',
		'type' => 'csseditor'
	));

	$orgzr->reset_group();
	$orgzr->reset_section();

	/**********************
	 * LAYOUT
	 **********************/

	$orgzr->add_section("layout",__( 'Layout', 'waboot' ));

	$orgzr->set_section("layout");

	$orgzr->add(array(
		'name' => __('Page', 'waboot'),
		'desc' => __('Select page width. Fluid or Boxed?', 'waboot'),
		'id' => 'page_width',
		'std' => 'container',
		'type' => 'images',
		'options' => array(
			'container-fluid' => array (
				'label' => 'Fluid',
				'value' => $imagepath . 'layout/page-fluid.png'
			),
			'container' => array (
				'label' => 'Boxed',
				'value' => $imagepath . 'layout/page-boxed.png'
			)
		)
	));

    /**********************
     * BLOG
     **********************/

    $orgzr->add_section("blog",__( 'Blog', 'waboot' ));

    $orgzr->set_section("blog");

    $orgzr->add(array(
        'name' => __( 'Display Blog page title', 'waboot' ),
        'desc' => __( 'Check this box to show blog page title.', 'waboot' ),
        'id'   => 'blog_display_title',
        'std'  => '1',
        'type' => 'checkbox'
    ));

    $orgzr->add(array(
        'name' => __('Blog page title position', 'waboot'),
        'desc' => __('Select where to display page title of blog page', 'waboot'),
        'id' => 'blog_title_position',
        'std' => 'top',
        'type' => 'select',
        'options' => array('top' => __("Above primary","waboot"), 'bottom' => __("Below primary","waboot"))
    ));

	$layouts = \WBF\modules\options\of_add_default_key(_get_available_body_layouts());
	if(isset($layouts['values'][0]['thumb'])){
		$opt_type = "images";
		foreach($layouts['values'] as $k => $v){
			$final_layout[$v['value']]['label'] = $v['name'];
			$final_layout[$v['value']]['value'] = isset($v['thumb']) ? $v['thumb'] : "";
		}
	}else{
		$opt_type = "select";
		foreach($layouts['values'] as $k => $v){
			$final_layout[$v['value']]['label'] = $v['name'];
		}
	}
	$orgzr->add(array(
		'name' => __('Index page and blog page layout', 'waboot'),
		'desc' => __('Select the layout that will be applied to main blog page (which can be the default index or a custom blog page)', 'waboot'),
		'id' => 'blog_layout',
		'std' => $layouts['default'],
		'type' => $opt_type,
		'options' => $final_layout
	));

	$orgzr->add(array(
		'name' => __("Primary Sidebar width","waboot"),
		'desc' => __("Choose the primary sidebar width","waboot"),
		'id' => 'blog_primary_sidebar_size',
		'std' => '1/4',
		'type' => "select",
		'options' => array("1/2"=>"1/2","1/3"=>"1/3","1/4"=>"1/4","1/6"=>"1/6")
	));

	$orgzr->add(array(
		'name' => __("Secondary Sidebar width","waboot"),
		'desc' => __("Choose the secondary sidebar width","waboot"),
		'id' => 'blog_secondary_sidebar_size',
		'std' => '1/4',
		'type' => "select",
		'options' => array("1/2"=>"1/2","1/3"=>"1/3","1/4"=>"1/4","1/6"=>"1/6")
	));

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
		"title" => __("Display page title","waboot"),
		"desc" => __("Default rendering value for page title","waboot"),
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
		"title" => __("Title position","waboot"),
		"desc" => __("Default title positioning in pages","waboot"),
		"type" => "select",
		"options" => [
			[
				"name" => __("Above primary","waboot"),
				"value" => "top"
			],
			[
				"name" => __("Below primary","waboot"),
				"value" => "bottom"
			]
		],
		"default" => "top",
		"valid" => ["page","post","-{blog}","{cpt}","-slideshow","-{ctag:\\Waboot\\woocommerce\\is_shop}"]
	];

	$body_layouts = \WBF\modules\options\of_add_default_key(_get_available_body_layouts());
	$behaviors[] = array(
		"name" => "layout",
		"title" => __("Body layout","waboot"),
		"desc" => __("Default body layout for posts and pages","waboot"),
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
				'value' => "container",
				'thumb' => $imagepath . '/layout/page-boxed.png'
			],
			[
				'name' => 'Fluid',
				'value' => "container-fluid",
				'thumb' => $imagepath . '/layout/page-fluid.png'
			],
		],
		'default' => 'container',
		"valid" => ["page","post","{cpt}"]
	];

	$behaviors[] = [
		'name' => 'primary-sidebar-size',
		'title' => __("Primary Sidebar width","waboot"),
		'desc' => __("Choose the primary sidebar width","waboot"),
		'type' => "select",
		'options' => [
			[
				"name" => __("1/2","waboot"),
				"value" => "1/2"
			],
			[
				"name" => __("1/3","waboot"),
				"value" => "1/3"
			],
			[
				"name" => __("1/4","waboot"),
				"value" => "1/4"
			],
			[
				"name" => __("1/6","waboot"),
				"value" => "1/6"
			]
		],
		"default" => "1/4",
		"valid" => ['*','-slideshow',"-{ctag:\\Waboot\\woocommerce\\is_shop}","-{blog}"]
	];

	$behaviors[] = [
		'name' => 'secondary-sidebar-size',
		'title' => __("Secondary Sidebar width","waboot"),
		'desc' => __("Choose the secondary sidebar width","waboot"),
		'type' => "select",
		'options' => [
			[
				"name" => __("1/2","waboot"),
				"value" => "1/2"
			],
			[
				"name" => __("1/3","waboot"),
				"value" => "1/3"
			],
			[
				"name" => __("1/4","waboot"),
				"value" => "1/4"
			],
			[
				"name" => __("1/6","waboot"),
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
			"name" => __("No sidebar","waboot"),
			"value" => Layout::LAYOUT_FULL_WIDTH,
			"thumb"   => $imagepath . "behaviour/no-sidebar.png"
		],
		//2
		[
			"name" => __("Sidebar right","waboot"),
			"value" => Layout::LAYOUT_PRIMARY_RIGHT,
			"thumb"   => $imagepath . "behaviour/sidebar-right.png"
		],
		//3
		[
			"name" => __("Sidebar left","waboot"),
			"value" => Layout::LAYOUT_PRIMARY_LEFT,
			"thumb"   => $imagepath . "behaviour/sidebar-left.png"
		],
		//4
		[
			"name" => __("2 Sidebars","waboot"),
			"value" => Layout::LAYOUT_TWO_SIDEBARS,
			"thumb"   => $imagepath . "behaviour/sidebar-left-right.png"
		],
		//5
		[
			"name" => __("2 Sidebars right","waboot"),
			"value" => Layout::LAYOUT_TWO_SIDEBARS_RIGHT,
			"thumb"   => $imagepath . "behaviour/sidebar-right-2.png"
		],
		//6
		[
			"name" => __("2 Sidebars left","waboot"),
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