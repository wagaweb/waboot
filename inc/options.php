<?php

add_action("wbf/theme_options/register","optionsframework_options");
/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the "id" fields, make sure to use all lowercase and no spaces.
 *
 * @param \WBF\modules\options\Organizer $orgzr
 */
function optionsframework_options($orgzr) {

    // If using image radio buttons, define a directory path
	$imagepath = get_template_directory_uri() . '/assets/images/theme_options/';

    // WABOOT SETTINGS TABS

    /*
    * LAYOUT TAB
    */

	$orgzr->add_section("layout",__( 'Layout', 'waboot' ));

	$orgzr->set_section("layout");

    $orgzr->add(array(
        'name' => __('Page', 'waboot'),
        'desc' => __('Select page width. Fluid or Boxed?', 'waboot'),
        'id' => 'waboot_page_width',
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

	$orgzr->add(array(
        'name' => __('Background Page', 'waboot'),
        'desc' => __('Change the page background color.', 'waboot'),
        'id' => 'waboot_page_bgcolor',
        'type' => 'color',
	    'std' => '#ffffff',
        'recompile_styles' => true
    ));


	$orgzr->add(array(
        'name' => __( 'Sections Inner', 'waboot' ),
        'desc' => __( 'Define Inner Sections width and background color', 'waboot' ),
        'type' => 'info'
    ));

	$orgzr->add(array(
        'name' => __('Top Nav', 'waboot'),
        'desc' => __('Select Top Nav width. Fluid or Boxed?', 'waboot'),
        'id' => 'waboot_topnav_width',
        'std' => 'container',
        'type' => 'images',
        'options' => array(
            'container-fluid' => array (
                'label' => 'Fluid',
                'value' => $imagepath . 'layout/top-nav-fluid.png'
            ),
            'container' => array (
                'label' => 'Boxed',
                'value' => $imagepath . 'layout/top-nav-boxed.png'
            )
        )
    ));

	$orgzr->add(array(
        'name' => __( 'Header', 'waboot' ),
        'desc' => __( 'Select header width. Fluid or Boxed?', 'waboot' ),
        'id' => 'waboot_header_width',
        'std' => 'container-fluid',
        'type' => 'images',
        'options' => array(
            'container-fluid' => array (
                'label' => 'Fluid',
                'value' => $imagepath . 'layout/header-fluid.png'
            ),
            'container' => array (
                'label' => 'Boxed',
                'value' => $imagepath . 'layout/header-boxed.png'
            )
        )
    ));

	$orgzr->add(array(
        'name' => __('Background Top Nav', 'waboot'),
        'desc' => __('Change the Top Nav background color.', 'waboot'),
        'id' => 'waboot_topnav_bgcolor',
        'type' => 'color',
        'recompile_styles' => true
    ));

	$orgzr->add(array(
        'name' => __('Background Header', 'waboot'),
        'desc' => __('Change the header background color.', 'waboot'),
        'id' => 'waboot_header_bgcolor',
        'type' => 'color',
	    'std' => '#ffffff',
        'recompile_styles' => true
    ));

	$orgzr->add(array(
        'name' => __( 'Navbar', 'waboot' ),
        'desc' => __( 'Select navbar width. Fluid or Boxed?', 'waboot' ),
        'id' => 'waboot_navbar_width',
        'std' => 'container',
        'type' => 'images',
        'options' => array(
            'container-fluid' => array (
                'label' => 'Fluid',
                'value' => $imagepath . 'layout/header-fluid.png'
            ),
            'container' => array (
                'label' => 'Boxed',
                'value' => $imagepath . 'layout/header-boxed.png'
            )
        )
    ));

	$orgzr->add(array(
        'name' => __( 'Banner', 'waboot' ),
        'desc' => __( 'Select banner width. Fluid or Boxed?', 'waboot' ),
        'id' => 'waboot_banner_width',
        'std' => 'container-fluid',
        'type' => 'images',
        'options' => array(
            'container-fluid' => array (
                'label' => 'Fluid',
                'value' => $imagepath . 'layout/banner-fluid.png'
            ),
            'container' => array (
                'label' => 'Boxed',
                'value' => $imagepath . 'layout/banner-boxed.png'
            )
        )
    ));

	$orgzr->add(array(
        'name' => __('Background Navbar', 'waboot'),
        'desc' => __('Change the navbar background color.', 'waboot'),
        'id' => 'waboot_navbar_bgcolor',
        'type' => 'color',
	    'std' => '#e2e2e2',
        'recompile_styles' => true
    ));

	$orgzr->add(array(
        'name' => __('Background Banner', 'waboot'),
        'desc' => __('Change the banner background color.', 'waboot'),
        'id' => 'waboot_banner_bgcolor',
        'type' => 'color',
        'recompile_styles' => true
    ));

	$orgzr->add(array(
        'name' => __( 'Content', 'waboot' ),
        'desc' => __( 'Select content width. Fluid or Boxed?', 'waboot' ),
        'id' => 'waboot_content_width',
        'std' => 'container',
        'type' => 'images',
        'options' => array(
            'container-fluid' => array (
                'label' => 'Fluid',
                'value' => $imagepath . 'layout/content-fluid.png'
            ),
            'container' => array (
                'label' => 'Boxed',
                'value' => $imagepath . 'layout/content-boxed.png'
            )
        )
    ));

	$orgzr->add(array(
        'name' => __( 'Content Bottom', 'waboot' ),
        'desc' => __( 'Select content bottom width. Fluid or Boxed?', 'waboot' ),
        'id' => 'waboot_bottom_width',
        'std' => 'container',
        'type' => 'images',
        'options' => array(
            'container-fluid' => array (
                'label' => 'Fluid',
                'value' => $imagepath . 'layout/content-bottom-fluid.png'
            ),
            'container' => array (
                'label' => 'Boxed',
                'value' => $imagepath . 'layout/content-bottom-boxed.png'
            )
        )
    ));

	$orgzr->add(array(
        'name' => __('Background Content', 'waboot'),
        'desc' => __('Change the content background color.', 'waboot'),
        'id' => 'waboot_content_bgcolor',
        'type' => 'color',
        'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __('Background Content Bottom', 'waboot'),
        'desc' => __('Change the content bottom background color.', 'waboot'),
        'id' => 'waboot_bottom_bgcolor',
        'type' => 'color',
	    'std' => '#ededed',
        'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __( 'Footer', 'waboot' ),
        'desc' => __( 'Select footer width. Fluid or Boxed?', 'waboot' ),
        'id' => 'waboot_footer_width',
        'std' => 'container',
        'type' => 'images',
        'options' => array(
            'container-fluid' => array (
                'label' => 'Fluid',
                'value' => $imagepath . 'layout/footer-fluid.png'
            ),
            'container' => array (
                'label' => 'Boxed',
                'value' => $imagepath . 'layout/footer-boxed.png'
            )
        )
    ));

    $orgzr->add(array(
        'name' => __( 'Closure', 'waboot' ),
        'desc' => __( 'Select closure width. Fluid or Boxed?', 'waboot' ),
        'id' => 'waboot_closure_width',
        'std' => 'container',
        'type' => 'images',
        'options' => array(
            'container-fluid' => array (
                'label' => 'Fluid',
                'value' => $imagepath . 'layout/closure-fluid.png'
            ),
            'container' => array (
                'label' => 'Boxed',
                'value' => $imagepath . 'layout/closure-boxed.png'
            )
        )
    ));

    $orgzr->add(array(
        'name' => __('Background Footer', 'waboot'),
        'desc' => __('Change the footer background color.', 'waboot'),
        'id' => 'waboot_footer_bgcolor',
        'type' => 'color',
	    'std' => '#f6f6f6',
        'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __('Background Closure', 'waboot'),
        'desc' => __('Change the closure background color.', 'waboot'),
        'id' => 'waboot_closure_bgcolor',
        'type' => 'color',
	    'std' => '#e2e2e2',
        'recompile_styles' => true
    ));


    /*
     * STYLE TAB
     */

	$orgzr->add_section("backgrounds",__( 'Backgrounds', 'waboot' ));

	$orgzr->set_section("backgrounds");

    $orgzr->add(array(
        'name' => __( 'Body Background Image', 'waboot' ),
        'desc' => __( 'Upload a background image, or specify the image address of your image. (http://yoursite.com/image.png)', 'waboot' ),
        'id' => 'waboot_body_bgimage',
        'std' => '',
        'type' => 'upload',
        'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __('Body Background Color', 'waboot'),
        'desc' => __('Change the body background color.', 'waboot'),
        'id' => 'waboot_body_bgcolor',
        'std' => "#ededed",
        'type' => 'color',
	    'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __( 'Body Background Image Repeat', 'waboot' ),
        'desc' => __( 'Select how you want your background image to display.', 'waboot' ),
        'id' => 'waboot_body_bgrepeat',
        'type' => 'select',
        'options' => array( 'no-repeat' => 'No Repeat', 'repeat' => 'Repeat','repeat-x' => 'Repeat Horizontally', 'repeat-y' => 'Repeat Vertically' ),
        'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __( 'Body Background image position', 'waboot' ),
        'desc' => __( 'Select how you would like to position the background', 'waboot' ),
        'id' => 'waboot_body_bgpos',
        'std' => 'top left',
        'type' => 'select',
        'options' => array(
            'top left' => 'top left', 'top center' => 'top center', 'top right' => 'top right',
            'center left' => 'center left', 'center center' => 'center center', 'center right' => 'center right',
            'bottom left' => 'bottom left', 'bottom center' => 'bottom center', 'bottom right' => 'bottom right'
        ),
        'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __( 'Body Background Attachment', 'waboot' ),
        'desc' => __( 'Select whether the background should be fixed or move when the user scrolls', 'waboot' ),
        'id' => 'waboot_body_bgattach',
        'std' => 'scroll',
        'type' => 'select',
        'options' => array( 'scroll' => 'scroll','fixed' => 'fixed' ),
        'recompile_styles' => true
    ));

    /*
     * BOOTSTRAP VARIABLES TAB
     */

	$orgzr->add_section("style",__( 'Style', 'waboot' ));

	$orgzr->set_section("style");

    $orgzr->add(array(
        'name' => __('Primary color', 'waboot'),
        'desc' => __('Change the color of @brand-primary.', 'waboot'),
        'id' => 'waboot_brand_primary',
        'std' => "#428bca",
        'type' => 'color',
	    'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __('Text color', 'waboot'),
        'desc' => __('Change the color of @text-color.', 'waboot'),
        'id' => 'waboot_text_color',
        'std' => "#333333",
        'type' => 'color',
        'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __('Info color', 'waboot'),
        'desc' => __('Change the color of @brand-info.', 'waboot'),
        'id' => 'waboot_brand_info',
        'std' => "#5bc0de",
        'type' => 'color',
        'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __('Success color', 'waboot'),
        'desc' => __('Change the color of @brand-success.', 'waboot'),
        'id' => 'waboot_brand_success',
        'std' => "#5cb85c",
        'type' => 'color',
        'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __('Warning color', 'waboot'),
        'desc' => __('Change the color of @brand-warning.', 'waboot'),
        'id' => 'waboot_brand_warning',
        'std' => "#f0ad4e",
        'type' => 'color',
        'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __('Danger color', 'waboot'),
        'desc' => __('Change the color of @brand-danger.', 'waboot'),
        'id' => 'waboot_brand_danger',
        'std' => "#d9534f",
        'type' => 'color',
        'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __('Border radius base', 'waboot'),
        'desc' => __('Change the width in pixel of @border-radius-base.', 'waboot'),
        'id' => 'waboot_border_radius_base',
        'std' => "4",
        'type' => 'text',
        'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __('Border radius large', 'waboot'),
        'desc' => __('Change the width in pixel of @border-radius-large.', 'waboot'),
        'id' => 'waboot_border_radius_lg',
        'std' => "6",
        'type' => 'text',
        'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __('Border radius small', 'waboot'),
        'desc' => __('Change the width in pixel of @border-radius-small.', 'waboot'),
        'id' => 'waboot_border_radius_sm',
        'std' => "3",
        'type' => 'text',
        'recompile_styles' => true
    ));

    $orgzr->add(array(
        'name' => __('Well background', 'waboot'),
        'desc' => __('Change the color of @well-bg.', 'waboot'),
        'id' => 'waboot_well_bg',
        'std' => "#f5f5f5",
        'type' => 'color',
        'recompile_styles' => true
    ));

    /*
     * TYPOGRAPHY
     */

	$orgzr->add_section("typography",__( 'Typography', 'waboot' ));

	$orgzr->set_section("typography");

	$orgzr->add(array(
		'name' => 'Primary font (body, p, ul, li)',
		'id' => 'waboot_primaryfont',
		'std' => array(
			'family' => 'Source Sans Pro',
			'style'  => 'regular',
			'charset' => 'latin',
			'color'  => '#666666'
		),
		'type' => 'typography',
		'fonts_type' => 'google',
		'recompile_styles' => true
	));

    $orgzr->add(array(
        'name' => 'Secondary font (h1, h2, h3, h4, h5, h6)',
        'id' => 'waboot_secondaryfont',
        'std' => array(
            'family' => 'Source Sans Pro',
            'style'  => 'bold',
            'charset' => 'latin',
            'color'  => '#666666'
        ),
        'type' => 'typography',
        'fonts_type' => 'google',
        'recompile_styles' => true
    ));

    /*
    * HEADER TAB
    */

	$orgzr->add_section("header",__( 'Header', 'waboot' ));

	$orgzr->set_section("header");


    $orgzr->add(array(
        'name' => __( 'Main logo', 'waboot' ),
        'desc' => __( 'Choose the website main logo', 'waboot' ),
        'id'   => 'waboot_logo_in_navbar',
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

	$orgzr->add(array(
		'name' => __( 'Mobile Offcanvas logo', 'waboot' ),
		'desc' => __( 'Choose the logo to display in mobile offcanvas navigation bar', 'waboot' ),
		'id'   => 'mobile_offcanvas_logo',
		'std'  => '',
		'type' => 'upload'
	));

	$orgzr->add(array(
		'name' => __( 'Show Logo in Mobile Nav?', 'waboot' ),
		'desc' => __( 'Choose the visibility of site logo in mobile navigation.', 'waboot' ),
		'id'   => 'waboot_logo_mobilenav',
		'std'  => '1',
		'type' => 'checkbox'
	));

	$orgzr->add(array(
        'name' => __( 'Header', 'waboot' ),
        'desc' => __( 'Select your header layout' ,'waboot' ),
        'id'   => 'waboot_header_layout',
        'std' => 'header1',
        'type' => 'images',
        'options' => array(
            'header1' => array(
                'label' => 'header1',
                'value' => $imagepath . 'header/header-1.png'
            ),
            'header2' => array(
                'label' => 'header2',
                'value' => $imagepath . 'header/header-2.png'
            ),
            'header3' => array(
                'label' => 'header3',
                'value' => $imagepath . 'header/header-3.png'
            )
        )
    ));

	$orgzr->add(array(
		'name' => __('Site title custom text', 'waboot'),
		'desc' => __('When logo is empty, the site title will be used instead. You can customize here the text that will be displayed', 'waboot'),
		'id' => 'custom_site_title',
		'std' => get_bloginfo('name'),
		'type' => 'text',
	));

	$orgzr->add(array(
		'name' => __('Show site description', 'waboot'),
		'desc' => __('Choose visibility of site description', 'waboot'),
		'id'   => 'show_site_description',
		'std'  => '0',
		'type' => 'checkbox'
 	));

    $orgzr->add(array(
        'name' => __('Navbar Align', 'waboot'),
        'desc' => __('Select align of navbar', 'waboot'),
        'id' => 'waboot_navbar_align',
        'std' => 'navbar-left',
        'type' => 'select',
        'options' => array('navbar-left' => __("Left","waboot"), 'navbar-right' => __("Right","waboot"), 'navbar-center' => __("Center","waboot"))
    ));

    $orgzr->add(array(
        'name' => __( 'Show search bar in Header?', 'waboot' ),
        'desc' => __( 'Default is enabled. Uncheck this box to turn it off.', 'waboot' ),
        'id'   => 'waboot_search_bar',
        'std'  => '0',
        'type' => 'checkbox'
    ));

    $orgzr->add(array(
        'name' => __( 'Mobile Nav Style', 'waboot' ),
        'desc' => __( 'Select your mobile nav style' ,'waboot' ),
        'id'   => 'waboot_mobilenav_style',
        'std' => 'offcanvas',
        'type' => 'images',
        'options' => array(
            'bootstrap' => array(
                'label' => 'Bootstrap',
                'value' => $imagepath . 'mobile/nav-bootstrap.png'
            ),
            'offcanvas' => array(
                'label' => 'OffCanvas',
                'value' => $imagepath . 'mobile/nav-offcanvas.png'
            )
        )
    ));

    $orgzr->add(array(
        'name' => __('Top Nav Menu Position', 'waboot'),
        'desc' => __('Select the Top Nav Menu position', 'waboot'),
        'id' => 'waboot_topnavmenu_position',
        'std' => 'left',
        'type' => 'images',
        'options' => array(
            'left' => array (
                'label' => 'Left',
                'value' => $imagepath . 'topnav/top-nav-left.png'
            ),
            'right' => array (
                'label' => 'Right',
                'value' => $imagepath . 'topnav/top-nav-right.png'
            )
        )
    ));

    if (class_exists('\WBF\modules\behaviors\BehaviorsManager')) {

	    $get_archive_pages_type = function($blacklist = array()){
		    static $result;

		    if(isset($result)) return $result;

		    $archive_types = array(
			    "archive" => __("Archive page","waboot"),
			    "tag"     => __("Tag archive","waboot"),
			    "tax"     => __("Taxonomy archive","waboot"),
		    );
		    $blacklist = array_unique(array_merge($blacklist,array()));
		    $result = array();
		    foreach($archive_types as $name => $label){
			    if(!in_array($name,$blacklist)){
				    $result[$name] = $label;
			    }
		    }

		    return $result;
	    };

        $bd_locs = array_merge(array("homepage"=>"Homepage"),wbf_get_filtered_post_types(),$get_archive_pages_type());

        if (!empty($bd_locs)) {
            $orgzr->add(array(
                'id' => 'waboot_breadcrumb_locations',
                'name' => __('Breadcrumb Locations', 'waboot'),
                'desc' => __('Where to show breadcrumb', 'waboot'),
                'type' => 'multicheck',
                'options' => $bd_locs,
                'std' => array(
	                'homepage' => 1,
                    'post' => 1,
                    'page' => 1,
	                'archive' => 1,
	                'tag' => 1,
	                'tax' => 1
                )
            ));
        }
    }


    /*
    * FOOTER TAB
    */

	$orgzr->add_section("footer",__( 'Footer', 'waboot' ));

	$orgzr->set_section("footer");

    $orgzr->add(array(
        'name' => __( 'Show custom footer text?', 'waboot' ),
        'desc' => __( 'Default is disabled. Check this box to use custom footer text. Fill in your text below.', 'waboot' ),
        'id'   => 'waboot_custom_footer_toggle',
        'std'  => '1',
        'type' => 'checkbox'
    ));

    $orgzr->add(array(
        'name' => __( 'Custom footer text', 'waboot' ),
        'desc' => __( 'Enter the text here that you would like displayed at the bottom of your site. This setting will be ignored if you do not enable "Show custom footer text" above.', 'waboot' ),
        'id'   => 'waboot_custom_footer_text',
        'std'  => '&copy; '.date("Y")." - you business name",
        'type' => 'textarea'
    ));

    /*
     * BLOG PAGE TAB
     */

	$orgzr->add_section("blog",__( 'Blog', 'waboot' ));

	$orgzr->set_section("blog");

    $blogpage_layouts = \WBF\modules\options\of_add_default_key(apply_filters("waboot_blogpage_layout",array(
        'classic' =>  array(
            'label' => 'Classic',
            'value' => $imagepath . 'blog/default-blog.png'
        ),
        'masonry' =>  array(
            'label' => 'Masonry',
            'value' => $imagepath . 'blog/masonry-blog.png'
        ),
        'timeline' =>  array(
            'label' => 'Timeline',
            'value' => $imagepath . 'blog/timeline-blog.png'
        ),
        '_default' => 'classic'
    )));

    $orgzr->add(array(
        'name' => __('Blog Style', 'waboot'),
        'desc' => __('Select blog page style', 'waboot'),
        'id' => 'waboot_blogpage_layout',
        'std' => $blogpage_layouts['default'],
        'type' => 'images',
        'options' => $blogpage_layouts['values'],
	    'deps' => array(
          "timeline" => array('components'=>array('timeline')),
          "masonry" => array('components'=>array('masonry'))
        )
    ));


    $sidebar_layouts = \WBF\modules\options\of_add_default_key(waboot_get_available_body_layouts());
	if(isset($sidebar_layouts['values'][0]['thumb'])){
		$opt_type = "images";
		foreach($sidebar_layouts['values'] as $k => $v){
			$final_sidebar_layouts[$v['value']]['label'] = $v['name'];
			$final_sidebar_layouts[$v['value']]['value'] = isset($v['thumb']) ? $v['thumb'] : "";
		}
	}else{
		$opt_type = "select";
		foreach($sidebar_layouts['values'] as $k => $v){
			$final_sidebar_layouts[$v['value']]['label'] = $v['name'];
		}
	}
    $orgzr->add(array(
        'name' => __('Blog Layout', 'waboot'),
        'desc' => __('Select blog page layout', 'waboot'),
        'id' => 'waboot_blogpage_sidebar_layout',
        'std' => $sidebar_layouts['default'],
        'type' => $opt_type,
        'options' => $final_sidebar_layouts
    ));

    $orgzr->add(array(
        'name' => __( 'Display Blog page title', 'waboot' ),
        'desc' => __( 'Check this box to show page title.', 'waboot' ),
        'id'   => 'waboot_blogpage_displaytitle',
        'std'  => '1',
        'type' => 'checkbox'
    ));

    $orgzr->add(array(
        'name' => __('Title position', 'waboot'),
        'desc' => __('Select where to display page title', 'waboot'),
        'id' => 'waboot_blogpage_title_position',
        'std' => 'top',
        'type' => 'select',
        'options' => array('top' => __("Above primary","waboot"), 'bottom' => __("Below primary","waboot"))
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

    /*
     * WOOCOMMERCE PAGE TAB
     */

	if(function_exists("is_woocommerce")){

		$orgzr->add_section("woocommerce",__( 'WooCommerce', 'waboot' ));

		$orgzr->set_section("woocommerce");

	    $orgzr->add(array(
	        'name' => __( 'WooCommerce Shop Page', 'waboot' ),
	        'desc' => __( '', 'waboot' ),
	        'type' => 'info'
	    ));

	    $orgzr->add(array(
	        'name' => __('WooCommerce Shop Layout', 'waboot'),
	        'desc' => __('Select WooCommerce shop page layout', 'waboot'),
	        'id' => 'waboot_woocommerce_shop_sidebar_layout',
	        'std' => $sidebar_layouts['default'],
	        'type' => $opt_type,
	        'options' => $final_sidebar_layouts
	    ));

	    $orgzr->add(array(
	        'name' => __("Primary Sidebar width","waboot"),
	        'desc' => __("Choose the primary sidebar width","waboot"),
	        'id' => 'woocommerce_shop_primary_sidebar_size',
	        'std' => '1/4',
	        'type' => "select",
	        'options' => array("1/2"=>"1/2","1/3"=>"1/3","1/4"=>"1/4","1/6"=>"1/6")
	    ));

	    $orgzr->add(array(
	        'name' => __("Secondary Sidebar width","waboot"),
	        'desc' => __("Choose the secondary sidebar width","waboot"),
	        'id' => 'woocommerce_shop_secondary_sidebar_size',
	        'std' => '1/4',
	        'type' => "select",
	        'options' => array("1/2"=>"1/2","1/3"=>"1/3","1/4"=>"1/4","1/6"=>"1/6")
	    ));

	    $orgzr->add(array(
	        'name' => __( 'Display WooCommerce page title', 'waboot' ),
	        'desc' => __( 'Check this box to show page title.', 'waboot' ),
	        'id'   => 'woocommerce_shop_displaytitle',
	        'std'  => '1',
	        'type' => 'checkbox'
	    ));

	    $orgzr->add(array(
	        'name' => __('Title position', 'waboot'),
	        'desc' => __('Select where to display page title', 'waboot'),
	        'id' => 'woocommerce_shop_title_position',
	        'std' => 'top',
	        'type' => 'select',
	        'options' => array('top' => __("Above primary","waboot"), 'bottom' => __("Below primary","waboot"))
	    ));

	    $orgzr->add(array(
	        'name' => __( 'WooCommerce Archives and Categories', 'waboot' ),
	        'desc' => __( '', 'waboot' ),
	        'type' => 'info'
	    ));

	    $orgzr->add(array(
	        'name' => __('WooCommerce Archive Layout', 'waboot'),
	        'desc' => __('Select Woocommerce archive layout', 'waboot'),
	        'id' => 'waboot_woocommerce_sidebar_layout',
	        'std' => $sidebar_layouts['default'],
	        'type' => $opt_type,
	        'options' => $final_sidebar_layouts
	    ));

	    $orgzr->add(array(
	        'name' => __("Primary Sidebar width","waboot"),
	        'desc' => __("Choose the primary sidebar width","waboot"),
	        'id' => 'waboot_woocommerce_primary_sidebar_size',
	        'std' => '1/4',
	        'type' => "select",
	        'options' => array("1/2"=>"1/2","1/3"=>"1/3","1/4"=>"1/4","1/6"=>"1/6")
	    ));

	    $orgzr->add(array(
	        'name' => __("Secondary Sidebar width","waboot"),
	        'desc' => __("Choose the secondary sidebar width","waboot"),
	        'id' => 'waboot_woocommerce_secondary_sidebar_size',
	        'std' => '1/4',
	        'type' => "select",
	        'options' => array("1/2"=>"1/2","1/3"=>"1/3","1/4"=>"1/4","1/6"=>"1/6")
	    ));

	    $orgzr->add(array(
	        'name' => __( 'Display WooCommerce page title', 'waboot' ),
	        'desc' => __( 'Check this box to show page title.', 'waboot' ),
	        'id'   => 'waboot_woocommerce_displaytitle',
	        'std'  => '1',
	        'type' => 'checkbox'
	    ));

	    $orgzr->add(array(
	        'name' => __('Title position', 'waboot'),
	        'desc' => __('Select where to display page title', 'waboot'),
	        'id' => 'waboot_woocommerce_title_position',
	        'std' => 'top',
	        'type' => 'select',
	        'options' => array('top' => __("Above primary","waboot"), 'bottom' => __("Below primary","waboot"))
	    ));

	    $orgzr->add(array(
	        'name' => __('Items for Row', 'waboot'),
	        'desc' => __('How many items display for row', 'waboot'),
	        'id' => 'waboot_woocommerce_cat_items',
	        'std' => 'col-sm-3',
	        'type' => 'select',
	        'options' => array('col-sm-3' => '4', 'col-sm-4' => '3')
	    ));

		$orgzr->add(array(
			'name' => __('Products per page', 'waboot'),
			'desc' => __('How many products display per page', 'waboot'),
			'id' => 'woocommerce_products_per_page',
			'std' => '10',
			'type' => 'text'
		));

        $orgzr->add(array(
            'name' => __( 'Catalog Mode', 'waboot' ),
            'desc' => __( 'Hide add to cart button', 'waboot' ),
            'id'   => 'waboot_woocommerce_catalog',
            'std'  => '0',
            'type' => 'checkbox'
        ));

        $orgzr->add(array(
            'name' => __( 'Hide Price', 'waboot' ),
            'desc' => __( 'Hide price in catalog', 'waboot' ),
            'id'   => 'waboot_woocommerce_hide_price',
            'std'  => '0',
            'type' => 'checkbox'
        ));

	}

    /*
     * POST META TAB
     */

	$orgzr->add_section("post_meta",__( 'Post Meta', 'waboot' ));

	$orgzr->set_section("post_meta");

    $orgzr->add(array(
        'name' => __( 'Show content navigation above posts?', 'waboot' ),
        'desc' => __( 'Displays links to next and previous posts above the current post and above the posts on the index page. Default is hide. Check this box to show content nav above posts.', 'waboot' ),
        'id'   => 'waboot_content_nav_above',
        'std'  => '0',
        'type' => 'checkbox'
    ));

    $orgzr->add(array(
        'name' => __( 'Show content navigation below posts?', 'waboot' ),
        'desc' => __( 'Displays links to next and previous posts below the current post and below the posts on the index page. Default is show. Uncheck this box to hide content nav above posts.', 'waboot' ),
        'id'   => 'waboot_content_nav_below',
        'std'  => '1',
        'type' => 'checkbox'
    ));

    $orgzr->add(array(
        'name' => __( 'Show post author?', 'waboot' ),
        'desc' => __( 'Displays the post author. Default is show. Uncheck this box to hide the post author.', 'waboot' ),
        'id'   => 'waboot_post_author',
        'std'  => '0',
        'type' => 'checkbox'
    ));

    $orgzr->add(array(
        'name' => __( 'Show published date?', 'waboot' ),
        'desc' => __( 'Displays the date the article was posted. Default is show. Uncheck this box to hide post published date.', 'waboot' ),
        'id'   => 'waboot_published_date',
        'std'  => '1',
        'type' => 'checkbox'
    ));

    $orgzr->add(array(
        'name' => __( 'Show post categories?', 'waboot' ),
        'desc' => __( 'Displays the categories in which a post was published. Default is show. Uncheck this box to hide post categories.', 'waboot' ),
        'id'   => 'waboot_post_categories',
        'std'  => '1',
        'type' => 'checkbox'
    ));

    $orgzr->add(array(
        'name' => __( 'Show post tags?', 'waboot' ),
        'desc' => __( 'Displays the tags attached to a post. Default is show. Uncheck this box to hide post tags.', 'waboot' ),
        'id'   => 'waboot_post_tags',
        'std'  => '1',
        'type' => 'checkbox'
    ));

    $orgzr->add(array(
        'name' => __( 'Show link for # of comments / Leave a comment?', 'waboot' ),
        'desc' => __( 'Displays the number of comments and/or a Leave a comment message on posts. Default is show. Uncheck this box to hide.' ,'waboot' ),
        'id'   => 'waboot_post_comments_link',
        'std'  => '1',
        'type' => 'checkbox'
    ));

	/*
    * CONTACT FORM TAB
    */

	$orgzr->add_section("contact_form",__( 'Contact form', 'waboot' ));

	$orgzr->set_section("contact_form");

	$orgzr->add(array(
		'name' => __('Recipient type', 'waboot'),
		'desc' => __('Select who will receive the mail', 'waboot'),
		'id' => 'contact_form_mail_recipient_type',
		'std' => 'admin',
		'type' => 'select',
		'options' => array('admin' => __('The site admin'), 'author' => __('The author of the post in which the form is inserted',"waboot"), 'specific_contact' => __("A specific recipient"))
	));

	$orgzr->add(array(
		'name' => __( 'Recipient address', 'waboot' ),
		'desc' => __( 'Enter the recipient email address (valid for "specific recipient" option)', 'waboot' ),
		'id'   => 'contact_form_mail_recipient_email',
		'type' => 'text',
		'std'  => ''
	));

	$orgzr->add(array(
		'name' => __( 'Custom privacy text', 'waboot' ),
		'desc' => __( 'Enter your privacy statement for contact form', 'waboot' ),
		'id'   => 'contact_form_privacy_text',
		'std'  => '',
		'type' => 'textarea'
	));

    /*
     * FAVICON TAB
     */

	$orgzr->add_section("favicons",__( 'Favicons', 'waboot' ));

	$orgzr->set_section("favicons");

	$orgzr->add(array(
		'name' => __( 'Icon', 'waboot' ),
		'desc' => __( 'Upload a favicon (only .png and .ico files are allowed).', 'waboot' ),
		'id' => 'favicon_icon',
		'std' => '',
		'type' => 'upload',
		'readonly' => true,
		'upload_to' => ABSPATH,
		'upload_as' => 'favicon',
		'allowed_extensions' => array("png","ico")
	));

	$orgzr->add(array(
		'name' => __( 'Apple Touch 120x120 Icon', 'waboot' ),
		'desc' => __( 'Upload a favicon (only .png and .ico files are allowed).', 'waboot' ),
		'id' => 'favicon_apple120',
		'std' => '',
		'type' => 'upload',
		'readonly' => true,
		'upload_to' => ABSPATH,
		'upload_as' => 'touch-icon-iphone-retina',
		'allowed_extensions' => array("png","ico")
	));

	$orgzr->add(array(
		'name' => __( 'Apple Touch 152x152 Icon', 'waboot' ),
		'desc' => __( 'Upload a favicon (only .png and .ico files are allowed).', 'waboot' ),
		'id' => 'favicon_apple152',
		'std' => '',
		'type' => 'upload',
		'readonly' => true,
		'upload_to' => ABSPATH,
		'upload_as' => 'touch-icon-ipad-retina',
		'allowed_extensions' => array("png","ico")
	));

    /*
     * SOCIAL TAB
     */

	$orgzr->add_section("social",__( 'Social', 'waboot' ));

	$orgzr->set_section("social");

	$socials = waboot_get_available_socials();

	foreach($socials as $k => $s){
		$orgzr->add(array(
			'name' => $s['name'],
			'desc' => $s['theme_options_desc'],
			'id'   => 'waboot_social_'.$k,
			'type' => 'text',
			'std'  => ''
		));
	}

    $orgzr->add(array(
        'name' => __( 'Social Position', 'waboot' ),
        'desc' => __( 'Select one of the following positions for the social links', 'waboot' ),
        'id' => 'waboot_social_position',
        'type' => 'images',
        'std'  => 'navigation',
        'options' => array(
            'footer' =>  array(
                'label' => 'Footer',
                'value' => $imagepath . 'social/footer.png'
            ),
            'header-right' =>  array(
                'label' => 'Header Right',
                'value' => $imagepath . 'social/header-right.png'
            ),
            'header-left' =>  array(
                'label' => 'Header Left',
                'value' => $imagepath . 'social/header-left.png'
            ),
            'topnav-right' =>  array(
                'label' => 'Topnav Right',
                'value' => $imagepath . 'social/topnav-right.png'
            ),
            'topnav-left' =>  array(
                'label' => 'Topnav Left',
                'value' => $imagepath . 'social/topnav-left.png'
            ),
            'navigation' =>  array(
                'label' => 'Navigation',
                'value' => $imagepath . 'social/nav.png'
            )
        )
    ));

	$orgzr->add(array(
		'name' => __( 'Do not use any of the previous positions', 'waboot' ),
		'desc' => __( 'You can manually place the social links with the <strong>waboot social widget</strong> (even if one of the previous positions is selected)', 'waboot' ),
		'id'   => 'social_position_none',
		'std'  => '0',
		'type' => 'checkbox'
	));

    /*
     * CUSTOM CSS TAB
     */

	$orgzr->add_section("custom_css",__( 'Custom CSS', 'waboot' ));

	$orgzr->set_section("custom_css");

    $orgzr->add(array(
        'name' => __( 'Custom CSS', 'waboot' ),
        'desc' => __( 'Enter custom css to apply to the theme (press CTRL-SPACE on Windows, or CTRL-F on Mac for suggestions).', 'waboot' ),
        'id'   => 'waboot_custom_css',
        'type' => 'csseditor'
    ));

	$orgzr->reset_group();
	$orgzr->reset_section();
}