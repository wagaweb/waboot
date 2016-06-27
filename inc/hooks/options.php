<?php

namespace Waboot\hooks\options;

use WBF\modules\options\Organizer;

add_filter('wbf/modules/options/available','\Waboot\hooks\options\register');

/**
 * Register standard theme options
 */
function register(){
	$orgzr = Organizer::getInstance();

	$orgzr->set_group("std_options");

	$orgzr->add_section("global",_x("Global", "Theme options","waboot"));

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
		'id'   => 'show_site_description',
		'std'  => '0',
		'type' => 'checkbox'
	]);

	/*
	 * BACKGROUNDS
	 */

	$orgzr->add(array(
		'name' => _x( 'Backgrounds', 'Theme options', 'waboot' ),
		'desc' => _x( 'Settings about page backgrounds', 'waboot' ),
		'type' => 'info'
	));

	$orgzr->set_group("css_injection");

	$orgzr->add(array(
		'name' => _x('Background Page', 'Theme options', 'Theme options', 'waboot'),
		'desc' => _x('Change the page background color.', 'Theme options', 'waboot'),
		'id' => 'waboot_page_bgcolor',
		'type' => 'color',
		'std' => '#ffffff',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x('Background Content', 'Theme options', 'Theme options', 'waboot'),
		'desc' => _x('Change the content background color.', 'Theme options', 'waboot'),
		'id' => 'waboot_content_bgcolor',
		'type' => 'color',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x('Background Content Bottom', 'Theme options', 'Theme options', 'waboot'),
		'desc' => _x('Change the content bottom background color.', 'Theme options', 'waboot'),
		'id' => 'waboot_bottom_bgcolor',
		'type' => 'color',
		'std' => '#ededed',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x( 'Body Background Image', 'Theme options', 'waboot' ),
		'desc' => _x( 'Upload a background image, or specify the image address of your image. (http://yoursite.com/image.png)', 'Theme options', 'waboot' ),
		'id' => 'waboot_body_bgimage',
		'std' => '',
		'type' => 'upload',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x('Body Background Color', 'Theme options', 'waboot'),
		'desc' => _x('Change the body background color.', 'Theme options', 'waboot'),
		'id' => 'waboot_body_bgcolor',
		'std' => "#ededed",
		'type' => 'color',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x( 'Body Background Image Repeat', 'Theme options', 'waboot' ),
		'desc' => _x( 'Select how you want your background image to display.', 'waboot' ),
		'id' => 'waboot_body_bgrepeat',
		'type' => 'select',
		'options' => array( 'no-repeat' => 'No Repeat', 'repeat' => 'Repeat','repeat-x' => 'Repeat Horizontally', 'repeat-y' => 'Repeat Vertically' ),
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x( 'Body Background image position', 'Theme options', 'waboot' ),
		'desc' => _x( 'Select how you would like to position the background', 'waboot' ),
		'id' => 'waboot_body_bgpos',
		'std' => 'top left',
		'type' => 'select',
		'options' => array(
			'top left' => 'top left', 'top center' => 'top center', 'top right' => 'top right',
			'center left' => 'center left', 'center center' => 'center center', 'center right' => 'center right',
			'bottom left' => 'bottom left', 'bottom center' => 'bottom center', 'bottom right' => 'bottom right'
		),
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x( 'Body Background Attachment', 'Theme options', 'waboot' ),
		'desc' => _x( 'Select whether the background should be fixed or move when the user scrolls', 'Theme options', 'waboot' ),
		'id' => 'waboot_body_bgattach',
		'std' => 'scroll',
		'type' => 'select',
		'options' => array( 'scroll' => 'scroll','fixed' => 'fixed' ),
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x('Background Footer', 'Theme options', 'waboot'),
		'desc' => _x('Change the footer background color.', 'Theme options', 'waboot'),
		'id' => 'waboot_footer_bgcolor',
		'type' => 'color',
		'std' => '#f6f6f6',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

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
		'name' => _x('Primary color', 'Theme options', 'waboot'),
		'desc' => _x('Change the color of @brand-primary.', 'Theme options', 'waboot'),
		'id' => 'btstrp_brand_primary',
		'std' => "#428bca",
		'type' => 'color',
		'save_action' => "\\Waboot\\functions\\deploy_theme_options_css"
	));

	$orgzr->add(array(
		'name' => _x('Text color', 'Theme options', 'waboot'),
		'desc' => _x('Change the color of @text-color.', 'Theme options', 'waboot'),
		'id' => 'btstrp_text_color',
		'std' => "#333333",
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
		'name' => _x('Success color', 'Theme options', 'waboot'),
		'desc' => _x('Change the color of @brand-success.', 'Theme options', 'waboot'),
		'id' => 'btstrp_brand_success',
		'std' => "#5cb85c",
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

	$orgzr->add(array(
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
	));

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

	$orgzr->add(array(
		'name' => _x( 'Icon', 'waboot' ),
		'desc' => _x( 'Upload a favicon (only .png and .ico files are allowed).', "Theme Options", 'waboot' ),
		'id' => 'favicon_icon',
		'std' => '',
		'type' => 'upload',
		'readonly' => true,
		'upload_to' => ABSPATH,
		'upload_as' => 'favicon',
		'allowed_extensions' => array("png","ico")
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
		'allowed_extensions' => array("png","ico")
	));

	$orgzr->add(array(
		'name' => _x( 'Apple Touch 152x152 Icon', "Theme Options", 'waboot' ),
		'desc' => _x( 'Upload a favicon (only .png and .ico files are allowed).', "Theme Options", 'waboot' ),
		'id' => 'favicon_apple152',
		'std' => '',
		'type' => 'upload',
		'readonly' => true,
		'upload_to' => ABSPATH,
		'upload_as' => 'touch-icon-ipad-retina',
		'allowed_extensions' => array("png","ico")
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
}