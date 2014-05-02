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

    $shortname = "wship";

    // Pull all the categories into an array
    $options_categories = array();
    $options_categories_obj = get_categories();
    foreach ($options_categories_obj as $category) {
      $options_categories[$category->cat_ID] = $category->cat_name;
    }

    // Pull all the tags into an array
    $options_tags = array();
    $options_tags_obj = get_tags( array('hide_empty' => false) );
    $options_tags[''] = __( 'Select a tag:', 'wship' );
    foreach ($options_tags_obj as $tag) {
      $options_tags[$tag->term_id] = $tag->name;
    }

    // Pull all the pages into an array
    $options_pages = array();
    $options_pages_obj = get_pages('sort_column=post_parent,menu_order');
    $options_pages[''] = __( 'Select a page:', 'wship' );
    foreach ($options_pages_obj as $page) {
      $options_pages[$page->ID] = $page->post_title;
    }

    // If using image radio buttons, define a directory path
    $imagepath =  get_bloginfo('stylesheet_directory') . '/img/';

    $options = array();

    // Display Settings tab
    $options[] = array(
      'name' => __( 'Display Options', 'wship' ),
      'type' => 'heading'
    );


    // Navigation elements
    $options[] = array(
      'name' => __( 'Navigation Elements', 'wship' ),
      'desc' => __( 'Top navbar, breadcrumb navigation, and content navigation design options', 'wship' ),
      'type' => 'info'
    );

    $options[] = array(
      'name' => __( 'Show Top Menu navigation bar?', 'wship' ),
      'desc' => __( 'Displays the top navbar on your site, even if there\'s no menu assigned in Appearance > Menu. Uncheck this box to hide it. Default is enabled.', 'wship' ),
      'id'   => 'alienship_show_top_navbar',
      'std'  => '1',
      'type' => 'checkbox'
    );

    $options[] = array(
      'name' => __( 'Show site name in Top Menu navigation bar?', 'wship' ),
      'desc' => __( 'Default is enabled. Uncheck this box to hide site name in Top Menu navigation bar.', 'wship' ),
      'id'   => 'alienship_name_in_navbar',
      'std'  => '1',
      'type' => 'checkbox'
    );

    $options[] = array(
      'name' => __( 'Show search bar in Top Menu navigation bar?', 'wship' ),
      'desc' => __( 'Default is enabled. Uncheck this box to turn it off.', 'wship' ),
      'id'   => 'alienship_search_bar',
      'std'  => '1',
      'type' => 'checkbox'
    );

    $options[] = array(
      'name' => __( 'Show Breadcrumb Navigation?', 'wship' ),
      'desc' => __( 'Default is show. Uncheck this box to hide breadcrumbs.', 'wship' ),
      'id'   => 'alienship_breadcrumbs',
      'std'  => '1',
      'type' => 'checkbox'
    );

    $options[] = array(
      'name' => __( 'Show content navigation above posts?', 'wship' ),
      'desc' => __( 'Displays links to next and previous posts above the current post and above the posts on the index page. Default is hide. Check this box to show content nav above posts.', 'wship' ),
      'id'   => 'alienship_content_nav_above',
      'std'  => '0',
      'type' => 'checkbox'
    );

    $options[] = array(
      'name' => __( 'Show content navigation below posts?', 'wship' ),
      'desc' => __( 'Displays links to next and previous posts below the current post and below the posts on the index page. Default is show. Uncheck this box to hide content nav above posts.', 'wship' ),
      'id'   => 'alienship_content_nav_below',
      'std'  => '1',
      'type' => 'checkbox'
    );

    // Miscellaneous text options
    $options[] = array(
      'name' => __( 'Miscellaneous Text', 'wship' ),
      'desc' => __( 'Miscellaneous text options.', 'wship' ),
      'type' => 'info'
    );

    $options[] = array(
      'name' => __( 'Show custom footer text?', 'wship' ),
      'desc' => __( 'Default is disabled. Check this box to use custom footer text. Fill in your text below.', 'wship' ),
      'id'   => 'alienship_custom_footer_toggle',
      'std'  => '0',
      'type' => 'checkbox'
    );

    $options[] = array(
      'name' => __( 'Custom footer text', 'wship' ),
      'desc' => __( 'Enter the text here that you would like displayed at the bottom of your site. This setting will be ignored if you do not enable "Show custom footer text" above.', 'wship' ),
      'id'   => 'alienship_custom_footer_text',
      'std'  => '',
      'type' => 'text'
    );

    $options[] = array(
      'name' => __( 'Posts and Pages', 'wship' ),
      'desc' => __( 'Options related to the display of posts and pages, like excerpts and post meta information (published date, author, categories, and tags - is displayed on each post to provide your readers with information). Use the options below to control what is displayed.', 'wship' ),
      'type' => 'info'
    );

    $options[] = array(
      'name' => __( 'Show post author?', 'wship' ),
      'desc' => __( 'Displays the post author. Default is show. Uncheck this box to hide the post author.', 'wship' ),
      'id'   => 'alienship_post_author',
      'std'  => '1',
      'type' => 'checkbox'
    );

    $options[] = array(
      'name' => __( 'Show published date?', 'wship' ),
      'desc' => __( 'Displays the date the article was posted. Default is show. Uncheck this box to hide post published date.', 'wship' ),
      'id'   => 'alienship_published_date',
      'std'  => '1',
      'type' => 'checkbox'
    );

    $options[] = array(
      'name' => __( 'Show post categories?', 'wship' ),
      'desc' => __( 'Displays the categories in which a post was published. Default is show. Uncheck this box to hide post categories.', 'wship' ),
      'id'   => 'alienship_post_categories',
      'std'  => '1',
      'type' => 'checkbox'
    );

    $options[] = array(
      'name' => __( 'Show post tags?', 'wship' ),
      'desc' => __( 'Displays the tags attached to a post. Default is show. Uncheck this box to hide post tags.', 'wship' ),
      'id'   => 'alienship_post_tags',
      'std'  => '1',
      'type' => 'checkbox'
    );

    $options[] = array(
      'name' => __( 'Show link for # of comments / Leave a comment?', 'wship' ),
      'desc' => __( 'Displays the number of comments and/or a Leave a comment message on posts. Default is show. Uncheck this box to hide.' ,'wship' ),
      'id'   => 'alienship_post_comments_link',
      'std'  => '1',
      'type' => 'checkbox'
    );

    // Featured Posts tab
    $options[] = array(
      'name' => __( 'Featured Posts', 'wship' ),
      'type' => 'heading'
    );

    $options[] = array(
      'name' => __( 'Featured Posts Information', 'wship' ),
      'desc' => __( 'This feature displays certain posts in a photo slider or in a block grid at the top of your post index. This is a good way to make special content stand out. You can feature any post here, according to the criteria you choose below. Don\'t forget to assign featured images to your posts in the post editor!', 'wship' ),
      'type' => 'info'
    );

    $options[] = array(
      'name' => __( 'Enable Featured Posts?', 'wship' ),
      'desc' => __( 'Check this box to turn on featured posts functionality. Set the options below to determine how your featured posts will work. Default is disabled.', 'wship' ),
      'id'   => 'alienship_featured_posts',
      'std'  => '0',
      'type' => 'checkbox'
    );

    $options[] = array(
      'name'    => __( 'Display Featured Posts in a slider or in a grid?', 'wship' ),
      'desc'    => __( 'Displays your featured posts in either a photo slider or a block grid. The default setting is Slider.', 'wship' ),
      'id'      => 'alienship_featured_posts_display_type',
      'std'     => '1',
      'type'    => 'radio',
      'options' => array(
          '1' => __( 'Slider', 'wship' ),
          '0' => __( 'Grid', 'wship' )
      )
    );

    $options[] = array(
      'name'    => __( 'Featured Posts Tag', 'wship' ),
      'desc'    => __( 'The tag you select here determines which posts show in the featured posts slider or grid. Example: if you were to select the moo tag, posts tagged with moo would be displayed. Don\'t forget to attach your featured images in the post editor!', 'wship' ),
      'id'      => 'alienship_featured_posts_tag',
      'type'    => 'select',
      'class'   => 'mini',
      'options' => $options_tags
    );

    $options[] = array(
      'name'    => __( 'Maximum # of Featured Posts to display', 'wship' ),
      'desc'    => __( 'Select the maximum number of posts you want to display in the featured posts slider or grid. The default is three. NOTE: The grid displays two posts per row. For best results, select an even number here.', 'wship' ),
      'id'      => 'alienship_featured_posts_maxnum',
      'std'     => '3',
      'type'    => 'radio',
      'options' => array(
          '1' => __( 'One', 'wship' ),
          '2' => __( 'Two', 'wship' ),
          '3' => __( 'Three', 'wship' ),
          '4' => __( 'Four', 'wship' ),
          '5' => __( 'Five', 'wship' ),
          '6' => __( 'Six', 'wship' )
      )
    );

    $options[] = array(
      'name'    => __( 'Captions' ,'wship' ),
      'desc'    => __( 'Show post titles as captions with slider images. Default is Show.', 'wship' ),
      'id'      => 'alienship_featured_posts_captions',
      'std'     => '1',
      'type'    => 'radio',
      'options' => array(
          '1' => __( 'Show slide captions', 'wship' ),
          '0' => __( 'Hide slide captions', 'wship' )
      )
    );

    $options[] = array(
      'name'    => __( 'Indicators' ,'wship' ),
      'desc'    => __( 'Show indicators at the bottom of the slider that show the current slideshow position and allow for navigation between slides. Default is Hide.', 'wship' ),
      'id'      => 'alienship_featured_posts_indicators',
      'std'     => '0',
      'type'    => 'radio',
      'options' => array(
          '1' => __( 'Show slide indicators', 'wship' ),
          '0' => __( 'Hide slide indicators', 'wship' )
      )
    );

    $options[] = array(
      'name'    => __( 'Duplicate featured posts' ,'wship' ),
      'desc'    => __( 'Show posts from the featured content section in the rest of the body. Default is Hide.', 'wship' ),
      'id'      => 'alienship_featured_posts_show_dupes',
      'std'     => '0',
      'type'    => 'radio',
      'options' => array(
          '1' => __( 'Show duplicate posts', 'wship' ),
          '0' => __( 'Hide duplicate posts', 'wship' )
      )
    );

    $options[] = array(
      'name' => __( 'Featured Posts Images', 'wship' ),
      'desc' => __( 'A note about images: For best results, all of your images should be the same size (preferably the size you set below). If they are not the same size, your content will not look as good. For example: the photo slider will display images of varying sizes, but when it does the slider resizes itself between each slide. The grid will not display evenly if images are different sizes.', 'wship' ),
      'type' => 'info'
    );

    $options[] = array(
      'name'  => __( 'Featured post image width', 'wship' ),
      'desc'  => __( 'Enter the width (in pixels) you want the featured images to be. Default is 850 pixels.', 'wship' ),
      'id'    => 'alienship_featured_posts_image_width',
      'std'   => '850',
      'class' => 'mini',
      'type'  => 'text'
    );

    $options[] = array(
      'name'  => __( 'Featured post image height', 'wship' ),
      'desc'  => __( 'Enter the height (in pixels) you want the featured images to be. Default is 350 pixels.', 'wship' ),
      'id'    => 'alienship_featured_posts_image_height',
      'std'   => '350',
      'class' => 'mini',
      'type'  => 'text'
    );

    // WABOOT SETTINGS TABS

    /*
     * BACKGROUND TAB
     */

    $options[] = array(
        'name' => __( 'Background', 'wship' ),
        'type' => 'heading'
    );

    $options[] = array(
        'name' => __('Body Background Color', 'wship'),
        'desc' => __('Change the body background color.', 'wship'),
        'id' => 'wship_body_bgcolor',
        'std' => $background_defaults,
        'type' => 'color'
    );

    $options[] = array(
        'name' => __( 'Background Image', 'wship' ),
        'desc' => __( 'Upload a background image, or specify the image address of your image. (http://yoursite.com/image.png)', 'woothemes' ),
        'id' => 'wship_body_bgimage',
        'std' => '',
        'type' => 'upload'
    );

    $options[] = array(
        'name' => __( 'Background Image Repeat', 'wship' ),
        'desc' => __( 'Select how you want your background image to display.', 'wship' ),
        'id' => 'wship_body_bgrepeat',
        'type' => 'select',
        'options' => array( 'no-repeat' => 'No Repeat', 'repeat' => 'Repeat','repeat-x' => 'Repeat Horizontally', 'repeat-y' => 'Repeat Vertically' )
    );

    $options[] = array(
        'name' => __( 'Background image position', 'wship' ),
        'desc' => __( 'Select how you would like to position the background', 'wship' ),
        'id' => 'wship_body_bgpos',
        'std' => 'top left',
        'type' => 'select',
        'options' => array(
            'top left' => 'top left', 'top center' => 'top center', 'top right' => 'top right',
            'center left' => 'center left', 'center center' => 'center center', 'center right' => 'center right',
            'bottom left' => 'bottom left', 'bottom center' => 'bottom center', 'bottom right' => 'bottom right'
        )
    );

    $options[] = array(
        'name' => __( 'Background Attachment', 'wship' ),
        'desc' => __( 'Select whether the background should be fixed or move when the user scrolls', 'wship' ),
        'id' => 'wship_body_bgattach',
        'std' => 'scroll',
        'type' => 'select',
        'options' => array( 'scroll' => 'scroll','fixed' => 'fixed' )
    );

    /*
     * LAYOUT TAB
     */

    $options[] = array(
        'name' => __( 'Layout', 'wship' ),
        'type' => 'heading'
    );

    $options[] = array(
        'name' => __( 'Show Logo?', 'wship' ),
        'desc' => __( 'Displays the logo on your site.', 'wship' ),
        'id'   => 'wship_logo_in_navbar',
        'type' => 'upload'
    );

    $options[] = array(
        'name' => __( 'Logo Align', 'wship' ),
        'desc' => __( 'Select logo alignment', 'wship' ),
        'id' => 'wship_logo_align',
        'std' => 'left',
        'type' => 'select',
        'options' => array( 'left' => 'left','center' => 'center','right' => 'right' )
    );

    $options[] = array(
        'name' => __( 'Menu allineato al logo', 'wship' ),
        'desc' => __( 'Allinea il menu principale al logo' ,'wship' ),
        'id'   => 'wship_float_navbar',
        'std'  => '',
        'type' => 'checkbox'
    );

    $options[] = array(
        'name' => __('Navbar Background Color', 'wship'),
        'desc' => __('Change the navbar background color.', 'wship'),
        'id' => 'wship_navbar_bgcolor',
        'type' => 'color'
    );

    $options[] = array(
        'name' => __( 'Boxed Top Nav?', 'wship' ),
        'desc' => __( 'Boxed Top Nav' ,'wship' ),
        'id'   => 'wship_boxed_navbar',
        'std'  => '',
        'type' => 'checkbox'
    );

    $options[] = array(
        'name' => __( 'Page Width: Fluid or Boxed?', 'wship' ),
        'desc' => __( 'Select page width', 'wship' ),
        'id' => 'wship_page_width',
        'std' => 'container',
        'type' => 'images',
        'options' => array( 'container-fluid' => $imagepath . '1c.png','container' => $imagepath . '3cm.png' )
    );

    $options[] = array(
        'name' => __('Page Background Color', 'wship'),
        'desc' => __('Change the page background color.', 'wship'),
        'id' => 'wship_page_bgcolor',
        'type' => 'color'
    );

    $options[] = array(
        'name' => __( 'Header Width: Fluid or Boxed?', 'wship' ),
        'desc' => __( 'Select header width', 'wship' ),
        'id' => 'wship_header_width',
        'std' => 'container-fluid',
        'type' => 'images',
        'options' => array( 'container-fluid' => $imagepath . '1c.png','container' => $imagepath . '3cm.png' )
    );
    $options[] = array(
        'name' => __('Header Background Color', 'wship'),
        'desc' => __('Change the header background color.', 'wship'),
        'id' => 'wship_header_bgcolor',
        'type' => 'color'
    );

    $options[] = array(
        'name' => __( 'Banner Width: Fluid or Boxed?', 'wship' ),
        'desc' => __( 'Select banner width', 'wship' ),
        'id' => 'wship_banner_width',
        'std' => 'container-fluid',
        'type' => 'images',
        'options' => array( 'container-fluid' => $imagepath . '1c.png','container' => $imagepath . '3cm.png' )
    );
    $options[] = array(
        'name' => __('Banner Background Color', 'wship'),
        'desc' => __('Change the banner background color.', 'wship'),
        'id' => 'wship_banner_bgcolor',
        'type' => 'color'
    );

    $options[] = array(
        'name' => __( 'Content Width: Fluid or Boxed?', 'wship' ),
        'desc' => __( 'Select content width', 'wship' ),
        'id' => 'wship_content_width',
        'std' => 'container-fluid',
        'type' => 'images',
        'options' => array( 'container-fluid' => $imagepath . '1c.png','container' => $imagepath . '3cm.png' )
    );
    $options[] = array(
        'name' => __('Content Background Color', 'wship'),
        'desc' => __('Change the content background color.', 'wship'),
        'id' => 'wship_content_bgcolor',
        'type' => 'color'
    );

    $options[] = array(
        'name' => __( 'Content Bottom Width: Fluid or Boxed?', 'wship' ),
        'desc' => __( 'Select content bottom width', 'wship' ),
        'id' => 'wship_bottom_width',
        'std' => 'container-fluid',
        'type' => 'images',
        'options' => array( 'container-fluid' => $imagepath . '1c.png','container' => $imagepath . '3cm.png' )
    );
    $options[] = array(
        'name' => __('Banner Content Bottom Color', 'wship'),
        'desc' => __('Change the content bottom background color.', 'wship'),
        'id' => 'wship_bottom_bgcolor',
        'type' => 'color'
    );

    $options[] = array(
        'name' => __( 'Footer Width: Fluid or Boxed?', 'wship' ),
        'desc' => __( 'Select footer width', 'wship' ),
        'id' => 'wship_footer_width',
        'std' => 'container-fluid',
        'type' => 'images',
        'options' => array( 'container-fluid' => $imagepath . '1c.png','container' => $imagepath . '3cm.png' )
    );
    $options[] = array(
        'name' => __('Footer Background Color', 'wship'),
        'desc' => __('Change the footer background color.', 'wship'),
        'id' => 'wship_footer_bgcolor',
        'type' => 'color'
    );

    /*
     * BEHAVIOUR TAB
     */

    if(function_exists("waboot_behavior_get_options")) :

        $options[] = array(
            'name' => __( 'Behaviour', 'wship' ),
            'type' => 'heading'
        );

        //get predefined options
        $predef_behavior = waboot_behavior_get_options();

        foreach($predef_behavior as $b){
            $option = waboot_behavior_generate_option($b);
            $options[] = $option;
        }

    endif;

    /*
     * SOCIAL TAB
     */

    $options[] = array(
        'name' => __( 'Social', 'wship' ),
        'type' => 'heading'
    );

    $options[] = array(
        'name' => __( 'Social Position', 'wship' ),
        'desc' => __( 'Select the social widget position', 'wship' ),
        'id' => 'wship_social_position',
        'type' => 'select',
        'options' => array(
            'header-right' => 'Header Right',
            'header-left' => 'Header Left',
            'topnav-right' => 'TopNav Right',
            'topnav-left' => 'TopNav Left',
            'footer' => 'Footer')
    );

    $options[] = array(
        'name' => __( 'Facebook', 'wship' ),
        'desc' => __( 'Enter your facebook fan page link', 'wship' ),
        'id'   => 'wship_social_facebook',
        'type' => 'text'
    );

    $options[] = array(
        'name' => __( 'Twitter', 'wship' ),
        'desc' => __( 'Enter your twitter page link', 'wship' ),
        'id'   => 'wship_social_twitter',
        'type' => 'text'
    );

    $options[] = array(
        'name' => __( 'Google+', 'wship' ),
        'desc' => __( 'Enter your google+ page link', 'wship' ),
        'id'   => 'wship_social_google',
        'type' => 'text'
    );

    $options[] = array(
        'name' => __( 'Pinterest', 'wship' ),
        'desc' => __( 'Enter your pinterest page link', 'wship' ),
        'id'   => 'wship_social_pinterest',
        'type' => 'text'
    );

    $options[] = array(
        'name' => __( 'Linkedin', 'wship' ),
        'desc' => __( 'Enter your linkedin page link', 'wship' ),
        'id'   => 'wship_social_linkedin',
        'type' => 'text'
    );

    $options[] = array(
        'name' => __( 'Instagram', 'wship' ),
        'desc' => __( 'Enter your instagram page link', 'wship' ),
        'id'   => 'wship_social_instagram',
        'type' => 'text'
    );

    $options[] = array(
        'name' => __( 'Feed RSS', 'wship' ),
        'desc' => __( 'Enter your feed RSS link', 'wship' ),
        'id'   => 'wship_social_feedrss',
        'type' => 'text'
    );

    return $options;
}
