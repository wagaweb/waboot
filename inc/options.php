<?php
/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * By default it uses the theme name, in lowercase and without spaces, but this can be changed if needed.
 * If the identifier changes, it'll appear as if the options have been reset.
 *
 */
function optionsframework_option_name() {

    // This gets the theme name from the stylesheet
    $themename = get_option( 'stylesheet' );
    $themename = preg_replace("/\W/", "_", strtolower($themename) );

    $optionsframework_settings = get_option('optionsframework');
    $optionsframework_settings['id'] = $themename;
    update_option('optionsframework', $optionsframework_settings);
}

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the "id" fields, make sure to use all lowercase and no spaces.
 *
 */
function optionsframework_options() {

    // Pull all the categories into an array
    $options_categories = array();
    $options_categories_obj = get_categories();
    foreach ($options_categories_obj as $category) {
        $options_categories[$category->cat_ID] = $category->cat_name;
    }

    // Pull all the tags into an array
    $options_tags = array();
    $options_tags_obj = get_tags( array('hide_empty' => false) );
    $options_tags[''] = __( 'Select a tag:', 'waboot' );
    foreach ($options_tags_obj as $tag) {
        $options_tags[$tag->term_id] = $tag->name;
    }

    // Pull all the pages into an array
    $options_pages = array();
    $options_pages_obj = get_pages('sort_column=post_parent,menu_order');
    $options_pages[''] = __( 'Select a page:', 'waboot' );
    foreach ($options_pages_obj as $page) {
        $options_pages[$page->ID] = $page->post_title;
    }

    // If using image radio buttons, define a directory path
    $imagepath = get_template_directory_uri() . '/wbf/admin/images/';

    $options = array();

    // WABOOT SETTINGS TABS


    /*
    * LAYOUT TAB
    */

    $options[] = array(
        'name' => __( 'Layout', 'waboot' ),
        'type' => 'heading'
    );

    $options[] = array(
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
    );

    $options[] = array(
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
    );

    $options[] = array(
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
    );

    $options[] = array(
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
    );

    $options[] = array(
        'name' => __( 'Content', 'waboot' ),
        'desc' => __( 'Select content width. Fluid or Boxed?', 'waboot' ),
        'id' => 'waboot_content_width',
        'std' => 'container-fluid',
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
    );

    $options[] = array(
        'name' => __( 'Content Bottom', 'waboot' ),
        'desc' => __( 'Select content bottom width. Fluid or Boxed?', 'waboot' ),
        'id' => 'waboot_bottom_width',
        'std' => 'container-fluid',
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
    );

    $options[] = array(
        'name' => __( 'Footer', 'waboot' ),
        'desc' => __( 'Select footer width. Fluid or Boxed?', 'waboot' ),
        'id' => 'waboot_footer_width',
        'std' => 'container-fluid',
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
    );

    $options[] = array(
        'name' => __( 'Closure', 'waboot' ),
        'desc' => __( 'Select closure width. Fluid or Boxed?', 'waboot' ),
        'id' => 'waboot_closure_width',
        'std' => 'container-fluid',
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
    );


    /*
     * STYLE TAB
     */

    $options[] = array(
        'name' => __( 'Backgrounds', 'waboot' ),
        'type' => 'heading'
    );

    $options[] = array(
        'name' => __( 'Body Background Image', 'waboot' ),
        'desc' => __( 'Upload a background image, or specify the image address of your image. (http://yoursite.com/image.png)', 'waboot' ),
        'id' => 'waboot_body_bgimage',
        'std' => '',
        'type' => 'upload'
    );

    $options[] = array(
        'name' => __('Body Background Color', 'waboot'),
        'desc' => __('Change the body background color.', 'waboot'),
        'id' => 'waboot_body_bgcolor',
        'std' => "#ffffff",
        'type' => 'color'
    );

    $options[] = array(
        'name' => __( 'Body Background Image Repeat', 'waboot' ),
        'desc' => __( 'Select how you want your background image to display.', 'waboot' ),
        'id' => 'waboot_body_bgrepeat',
        'type' => 'select',
        'options' => array( 'no-repeat' => 'No Repeat', 'repeat' => 'Repeat','repeat-x' => 'Repeat Horizontally', 'repeat-y' => 'Repeat Vertically' )
    );

    $options[] = array(
        'name' => __( 'Body Background image position', 'waboot' ),
        'desc' => __( 'Select how you would like to position the background', 'waboot' ),
        'id' => 'waboot_body_bgpos',
        'std' => 'top left',
        'type' => 'select',
        'options' => array(
            'top left' => 'top left', 'top center' => 'top center', 'top right' => 'top right',
            'center left' => 'center left', 'center center' => 'center center', 'center right' => 'center right',
            'bottom left' => 'bottom left', 'bottom center' => 'bottom center', 'bottom right' => 'bottom right'
        )
    );

    $options[] = array(
        'name' => __( 'Body Background Attachment', 'waboot' ),
        'desc' => __( 'Select whether the background should be fixed or move when the user scrolls', 'waboot' ),
        'id' => 'waboot_body_bgattach',
        'std' => 'scroll',
        'type' => 'select',
        'options' => array( 'scroll' => 'scroll','fixed' => 'fixed' )
    );

    $options[] = array(
        'name' => __( 'Background Color', 'waboot' ),
        'desc' => __( 'Define background color', 'waboot' ),
        'type' => 'info'
    );

    $options[] = array(
        'name' => __('Page', 'waboot'),
        'desc' => __('Change the page background color.', 'waboot'),
        'id' => 'waboot_page_bgcolor',
        'type' => 'color'
    );

    $options[] = array(
        'name' => __('Top Nav', 'waboot'),
        'desc' => __('Change the Top Nav background color.', 'waboot'),
        'id' => 'waboot_topnav_bgcolor',
        'type' => 'color'
    );

    $options[] = array(
        'name' => __('Header', 'waboot'),
        'desc' => __('Change the header background color.', 'waboot'),
        'id' => 'waboot_header_bgcolor',
        'type' => 'color'
    );

    $options[] = array(
        'name' => __('Navbar', 'waboot'),
        'desc' => __('Change the navbar background color.', 'waboot'),
        'id' => 'waboot_navbar_bgcolor',
        'type' => 'color'
    );

    $options[] = array(
        'name' => __('Banner', 'waboot'),
        'desc' => __('Change the banner background color.', 'waboot'),
        'id' => 'waboot_banner_bgcolor',
        'type' => 'color'
    );

    $options[] = array(
        'name' => __('Content', 'waboot'),
        'desc' => __('Change the content background color.', 'waboot'),
        'id' => 'waboot_content_bgcolor',
        'type' => 'color'
    );

    $options[] = array(
        'name' => __('Content Bottom', 'waboot'),
        'desc' => __('Change the content bottom background color.', 'waboot'),
        'id' => 'waboot_bottom_bgcolor',
        'type' => 'color'
    );

    $options[] = array(
        'name' => __('Footer', 'waboot'),
        'desc' => __('Change the footer background color.', 'waboot'),
        'id' => 'waboot_footer_bgcolor',
        'type' => 'color'
    );

    $options[] = array(
        'name' => __('Closure', 'waboot'),
        'desc' => __('Change the closure background color.', 'waboot'),
        'id' => 'waboot_closure_bgcolor',
        'type' => 'color'
    );

    /*
     * BOOTSTRAP VARIABLES TAB
     */

    $options[] = array(
        'name' => __( 'Style', 'waboot' ),
        'type' => 'heading'
    );

    $options[] = array(
        'name' => __('Primary color', 'waboot'),
        'desc' => __('Change the color of @brand-primary.', 'waboot'),
        'id' => 'waboot_brand_primary',
        'std' => "#428bca",
        'type' => 'color'
    );

    $options[] = array(
        'name' => __('Text color', 'waboot'),
        'desc' => __('Change the color of @text-color.', 'waboot'),
        'id' => 'waboot_text_color',
        'std' => "#333333",
        'type' => 'color'
    );

    $options[] = array(
        'name' => __('Info color', 'waboot'),
        'desc' => __('Change the color of @brand-info.', 'waboot'),
        'id' => 'waboot_brand_info',
        'std' => "#5bc0de",
        'type' => 'color'
    );

    $options[] = array(
        'name' => __('Success color', 'waboot'),
        'desc' => __('Change the color of @brand-success.', 'waboot'),
        'id' => 'waboot_brand_success',
        'std' => "#5cb85c",
        'type' => 'color'
    );

    $options[] = array(
        'name' => __('Warning color', 'waboot'),
        'desc' => __('Change the color of @brand-warning.', 'waboot'),
        'id' => 'waboot_brand_warning',
        'std' => "#f0ad4e",
        'type' => 'color'
    );

    $options[] = array(
        'name' => __('Danger color', 'waboot'),
        'desc' => __('Change the color of @brand-danger.', 'waboot'),
        'id' => 'waboot_brand_danger',
        'std' => "#d9534f",
        'type' => 'color'
    );

    $options[] = array(
        'name' => __('Border radius base', 'waboot'),
        'desc' => __('Change the width in pixel of @border-radius-base.', 'waboot'),
        'id' => 'waboot_border_radius_base',
        'std' => "4",
        'type' => 'text'
    );

    $options[] = array(
        'name' => __('Border radius large', 'waboot'),
        'desc' => __('Change the width in pixel of @border-radius-large.', 'waboot'),
        'id' => 'waboot_border_radius_lg',
        'std' => "6",
        'type' => 'text'
    );

    $options[] = array(
        'name' => __('Border radius small', 'waboot'),
        'desc' => __('Change the width in pixel of @border-radius-small.', 'waboot'),
        'id' => 'waboot_border_radius_sm',
        'std' => "3",
        'type' => 'text'
    );

    $options[] = array(
        'name' => __('Well background', 'waboot'),
        'desc' => __('Change the color of @well-bg.', 'waboot'),
        'id' => 'waboot_well_bg',
        'std' => "#f5f5f5",
        'type' => 'color'
    );

    /*
     * TYPOGRAPHY
     */

    /** Typography Array Merge */

    $typography_mixed_fonts = array_merge( options_typography_get_os_fonts() , options_typography_get_google_fonts() );
    asort($typography_mixed_fonts);


    $options[] = array(
        'name' => __( 'Typography', 'waboot' ),
        'type' => 'heading'
    );

    /*$options[] = array( 'name' => 'Primary font (body, p, ul, li)',
        'desc' => 'Select your primary font face.',
        'id' => 'waboot_primary_font',
        'std' => array( 'size' => '', 'style' => 'normal', 'weight' => 'normal' , 'face' => 'Lato, sans-serif', 'color' => ''),
        'type' => 'typography',
        'options' => array(
            'faces' => $typography_mixed_fonts,
            'styles' => false )
    );*/

	$options[] = array(
		'name' => 'Primary font (body, p, ul, li)',
		'id' => 'waboot_primaryfont',
		'std' => array(
			'family' => 'Lato',
			'style'  => 'regular',
			'charset' => 'latin',
			'color'  => '#444444'
		),
		'type' => 'typography'
	);

    /*$options[] = array( 'name' => 'Secondary font (h1, h2, h3, h4, h5, h6)',
        'desc' => 'Select your secondary font face.',
        'id' => 'waboot_secondary_font',
        'std' => array( 'size' => '', 'style' => 'normal', 'weight' => 'normal' ,'face' => 'Lato, sans-serif', 'color' => ''),
        'type' => 'typography',
        'options' => array(
            'faces' => $typography_mixed_fonts,
            'styles' => false )
    );*/

    $options[] = array(
        'name' => 'Secondary font (h1, h2, h3, h4, h5, h6)',
        'id' => 'waboot_secondaryfont',
        'std' => array(
            'family' => 'Lato',
            'style'  => 'bold',
            'charset' => 'latin',
            'color'  => '#444444'
        ),
        'type' => 'typography'
    );
/*
    $options[] = array( 'name' => 'System Fonts and Google Fonts Mixed (2)',
        'desc' => 'Google fonts mixed with system fonts.',
        'id' => 'google_mixed_2',
        'std' => array( 'size' => '28px', 'face' => 'Arvo, serif', 'color' => '#333333'),
        'type' => 'typography',
        'options' => array(
            'faces' => $typography_mixed_fonts,
            'styles' => false )
    );
*/

    /*
     * TOP NAV TAB
     */

    $options[] = array(
        'name' => __( 'Top Nav', 'waboot' ),
        'type' => 'heading'
    );

    $options[] = array(
        'name' => __( 'Navigation Elements', 'waboot' ),
        'desc' => __( 'Top navbar, breadcrumb navigation, and content navigation design options', 'waboot' ),
        'type' => 'info'
    );

    $options[] = array(
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
    );


    if (class_exists("BehaviorsManager")) {
        $bd_locs = wp_get_filtered_post_types();

        if (!empty($bd_locs)) {
            $options[] = array(
                'id' => 'waboot_breadcrumb_locations',
                'name' => __('Breadcrumb Locations', 'waboot'),
                'desc' => __('Where to show breadcrumb', 'waboot'),
                'type' => 'multicheck',
                'options' => $bd_locs,
                'std' => array(
                    'post' => 1,
                    'page' => 1
                )
            );
        }
    }

    /*
    * HEADER TAB
    */

    $options[] = array(
        'name' => __( 'Header', 'waboot' ),
        'type' => 'heading'
    );

    $options[] = array(
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
    );

    $options[] = array(
        'name' => __( 'Show Logo?', 'waboot' ),
        'desc' => __( 'Displays the logo on your site.', 'waboot' ),
        'id'   => 'waboot_logo_in_navbar',
        'type' => 'upload'
    );

    $options[] = array(
        'name' => __( 'Show Logo in Mobile Nav?', 'waboot' ),
        'desc' => __( 'Default is enabled. Uncheck this box to turn it off.', 'waboot' ),
        'id'   => 'waboot_logo_mobilenav',
        'std'  => '1',
        'type' => 'checkbox'
    );

    $options[] = array(
        'name' => __( 'Show search bar in Header?', 'waboot' ),
        'desc' => __( 'Default is enabled. Uncheck this box to turn it off.', 'waboot' ),
        'id'   => 'waboot_search_bar',
        'std'  => '1',
        'type' => 'checkbox'
    );


    /*
    * FOOTER TAB
    */

    $options[] = array(
        'name' => __( 'Footer', 'waboot' ),
        'type' => 'heading'
    );

    $options[] = array(
        'name' => __( 'Show custom footer text?', 'waboot' ),
        'desc' => __( 'Default is disabled. Check this box to use custom footer text. Fill in your text below.', 'waboot' ),
        'id'   => 'waboot_custom_footer_toggle',
        'std'  => '0',
        'type' => 'checkbox'
    );

    $options[] = array(
        'name' => __( 'Custom footer text', 'waboot' ),
        'desc' => __( 'Enter the text here that you would like displayed at the bottom of your site. This setting will be ignored if you do not enable "Show custom footer text" above.', 'waboot' ),
        'id'   => 'waboot_custom_footer_text',
        'std'  => '',
        'type' => 'textarea'
    );


    /*
    * BEHAVIORS TAB
    */

    if(class_exists("BehaviorsManager")) :

        $options[] = array(
            'name' => __( 'Behaviors', 'waboot' ),
            'type' => 'heading'
        );

        //Get post types
        $post_types = wp_get_filtered_post_types();

        foreach($post_types as $ptSlug => $ptLabel){
            if(BehaviorsManager::count_behaviors_for_post_type($ptSlug) > 0){
                //get predefined options
                //$predef_behavior = waboot_behavior_get_options();
                $predef_behavior = BehaviorsManager::getAll();

                $options[] = array(
                    'name' => $ptLabel,
                    'desc' => sprintf(__( 'Edit default options for "%s" post type', 'waboot' ),strtolower($ptLabel)),
                    'type' => 'info'
                );

                foreach($predef_behavior as $b){
                    if($b->is_enabled_for_post_type($ptSlug)){
                        $option = $b->generate_of_option($ptSlug);
                        $options[] = $option;
                    }
                }
            }
        }

    endif;

    /*
     * BLOG PAGE TAB
     */

    $options[] = array(
        'name' => __( 'Blog page', 'waboot' ),
        'type' => 'heading'
    );

    $blogpage_layouts = of_add_default_key(apply_filters("waboot_blogpage_layout",array(
        'blog' =>  array(
            'label' => 'Blog',
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
        '_default' => 'blog'
    )));

    $options[] = array(
        'name' => __('Layout', 'waboot'),
        'desc' => __('Select blog page layout', 'waboot'),
        'id' => 'waboot_blogpage_layout',
        'std' => $blogpage_layouts['default'],
        'type' => 'images',
        'options' => $blogpage_layouts['values'],
	    'deps' => array("timeline" => array('components'=>array('timeline')))
    );

    $sidebar_layouts = of_add_default_key(waboot_get_available_body_layouts());
    foreach($sidebar_layouts['values'] as $k => $v){
        $final_sidebar_layouts[$v['value']] = $v['name'];
    }

    $options[] = array(
        'name' => __('Sidebar layout', 'waboot'),
        'desc' => __('Select blog page sidebar layout', 'waboot'),
        'id' => 'waboot_blogpage_sidebar_layout',
        'std' => $sidebar_layouts['default'],
        'type' => 'select',
        'options' => $final_sidebar_layouts
    );

    $options[] = array(
        'name' => __( 'Display page title', 'waboot' ),
        'desc' => __( 'Check this box to show page title.', 'waboot' ),
        'id'   => 'waboot_blogpage_displaytitle',
        'std'  => '1',
        'type' => 'checkbox'
    );

    $options[] = array(
        'name' => __('Title position', 'waboot'),
        'desc' => __('Select where to display page title', 'waboot'),
        'id' => 'waboot_blogpage_title_position',
        'std' => 'top',
        'type' => 'select',
        'options' => array('top' => __("Above primary","waboot"), 'bottom' => __("Below primary","waboot"))
    );

    /*$options[] = array(
        'name' => __( 'Display slideshow', 'waboot' ),
        'desc' => __( 'Check this box to display the slideshow selected below.', 'waboot' ),
        'id'   => 'waboot_blogpage_displayslideshow',
        'std'  => '1',
        'type' => 'checkbox'
    );*/

    /*
     * POSTS TAB
     */

    $options[] = array(
        'name' => __( 'Posts', 'waboot' ),
        'type' => 'heading'
    );

    $options[] = array(
        'name' => __( 'Show content navigation above posts?', 'waboot' ),
        'desc' => __( 'Displays links to next and previous posts above the current post and above the posts on the index page. Default is hide. Check this box to show content nav above posts.', 'waboot' ),
        'id'   => 'waboot_content_nav_above',
        'std'  => '0',
        'type' => 'checkbox'
    );

    $options[] = array(
        'name' => __( 'Show content navigation below posts?', 'waboot' ),
        'desc' => __( 'Displays links to next and previous posts below the current post and below the posts on the index page. Default is show. Uncheck this box to hide content nav above posts.', 'waboot' ),
        'id'   => 'waboot_content_nav_below',
        'std'  => '1',
        'type' => 'checkbox'
    );

    $options[] = array(
        'name' => __( 'Show post author?', 'waboot' ),
        'desc' => __( 'Displays the post author. Default is show. Uncheck this box to hide the post author.', 'waboot' ),
        'id'   => 'waboot_post_author',
        'std'  => '1',
        'type' => 'checkbox'
    );

    $options[] = array(
        'name' => __( 'Show published date?', 'waboot' ),
        'desc' => __( 'Displays the date the article was posted. Default is show. Uncheck this box to hide post published date.', 'waboot' ),
        'id'   => 'waboot_published_date',
        'std'  => '1',
        'type' => 'checkbox'
    );

    $options[] = array(
        'name' => __( 'Show post categories?', 'waboot' ),
        'desc' => __( 'Displays the categories in which a post was published. Default is show. Uncheck this box to hide post categories.', 'waboot' ),
        'id'   => 'waboot_post_categories',
        'std'  => '1',
        'type' => 'checkbox'
    );

    $options[] = array(
        'name' => __( 'Show post tags?', 'waboot' ),
        'desc' => __( 'Displays the tags attached to a post. Default is show. Uncheck this box to hide post tags.', 'waboot' ),
        'id'   => 'waboot_post_tags',
        'std'  => '1',
        'type' => 'checkbox'
    );

    $options[] = array(
        'name' => __( 'Show link for # of comments / Leave a comment?', 'waboot' ),
        'desc' => __( 'Displays the number of comments and/or a Leave a comment message on posts. Default is show. Uncheck this box to hide.' ,'waboot' ),
        'id'   => 'waboot_post_comments_link',
        'std'  => '1',
        'type' => 'checkbox'
    );

    /*
     * SOCIAL TAB
     */

    $options[] = array(
        'name' => __( 'Social', 'waboot' ),
        'type' => 'heading'
    );

    $options[] = array(
        'name' => __( 'Social Position', 'waboot' ),
        'desc' => __( 'Select the social widget position', 'waboot' ),
        'id' => 'waboot_social_position',
        'type' => 'images',
        'std'  => 'footer',
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
            )
        )
    );

    $options[] = array(
        'name' => __( 'Facebook', 'waboot' ),
        'desc' => __( 'Enter your facebook fan page link', 'waboot' ),
        'id'   => 'waboot_social_facebook',
        'type' => 'text'
    );

    $options[] = array(
        'name' => __( 'Twitter', 'waboot' ),
        'desc' => __( 'Enter your twitter page link', 'waboot' ),
        'id'   => 'waboot_social_twitter',
        'type' => 'text'
    );

    $options[] = array(
        'name' => __( 'Google+', 'waboot' ),
        'desc' => __( 'Enter your google+ page link', 'waboot' ),
        'id'   => 'waboot_social_google',
        'type' => 'text'
    );

    $options[] = array(
        'name' => __( 'YouTube', 'waboot' ),
        'desc' => __( 'Enter your youtube page link', 'waboot' ),
        'id'   => 'waboot_social_youtube',
        'type' => 'text'
    );

    $options[] = array(
        'name' => __( 'Pinterest', 'waboot' ),
        'desc' => __( 'Enter your pinterest page link', 'waboot' ),
        'id'   => 'waboot_social_pinterest',
        'type' => 'text'
    );

    $options[] = array(
        'name' => __( 'Linkedin', 'waboot' ),
        'desc' => __( 'Enter your linkedin page link', 'waboot' ),
        'id'   => 'waboot_social_linkedin',
        'type' => 'text'
    );

    $options[] = array(
        'name' => __( 'Instagram', 'waboot' ),
        'desc' => __( 'Enter your instagram page link', 'waboot' ),
        'id'   => 'waboot_social_instagram',
        'type' => 'text'
    );

    $options[] = array(
        'name' => __( 'Feed RSS', 'waboot' ),
        'desc' => __( 'Enter your feed RSS link', 'waboot' ),
        'id'   => 'waboot_social_feedrss',
        'type' => 'text'
    );

    /*
     * CUSTOM CSS TAB
     */

    $options[] = array(
        'name' => __( 'Custom CSS', 'waboot' ),
        'type' => 'heading'
    );

    $options[] = array(
        'name' => __( 'Custom CSS', 'waboot' ),
        'desc' => __( 'Enter custom css to apply to the theme (press CTRL-SPACE on Windows, or CTRL-F on Mac for suggestions).', 'waboot' ),
        'id'   => 'waboot_custom_css',
        'type' => 'csseditor'
    );

    return $options;
}
