<?php

locate_template('/wbf/wbf.php', true);

if ( ! function_exists( 'waboot_setup' ) ):
    function waboot_setup() {
        // Custom template tags for this theme.
        locate_template( '/inc/hooks.php', true );
        locate_template( '/inc/template-tags.php', true );

        // Register the navigation menus.
        locate_template( '/inc/menus.php', true );

        // Register sidebars
        locate_template( '/inc/widgets.php', true );

        // Header image
        //locate_template( '/inc/custom-header.php', true );

        // Customizer
        locate_template( '/inc/customizer.php', true );

        // Load the CSS
        locate_template( '/inc/stylesheets.php', true );

        // Load scripts
        locate_template( '/inc/scripts.php', true );

        // Add default posts and comments RSS feed links to head
        add_theme_support( 'automatic-feed-links' );

        // Add support for custom backgrounds
        add_theme_support( 'custom-background', array(
            'default-color' => 'ffffff',
        ) );

        // Add support for post-thumbnails
        add_theme_support( 'post-thumbnails' );

        // Add support for post formats. To be styled in later release.
        add_theme_support( 'post-formats', array( 'aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat' ) );

        // Load Jetpack related support if needed.
        if ( class_exists( 'Jetpack' ) )
            locate_template( '/inc/jetpack.php', true );
    }
endif;
add_action('after_setup_theme', 'waboot_setup', 11);

/** ACF TEST */

//add_action('init', 'cptui_register_my_cpt_gallery');
function cptui_register_my_cpt_gallery()
{
    register_post_type('gallery', array(
        'label' => 'Galleries',
        'description' => 'Add a new Gallery',
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'hierarchical' => false,
        'rewrite' => array('slug' => 'gallery', 'with_front' => true),
        'query_var' => true,
        'supports' => array('title', 'editor', 'excerpt', 'revisions', 'thumbnail', 'author', 'page-attributes'),
        'labels' => array(
            'name' => 'Galleries',
            'singular_name' => 'Gallery',
            'menu_name' => 'Galleries',
            'add_new' => 'Add Gallery',
            'add_new_item' => 'Add New Gallery',
            'edit' => 'Edit',
            'edit_item' => 'Edit Gallery',
            'new_item' => 'New Gallery',
            'view' => 'View Gallery',
            'view_item' => 'View Gallery',
            'search_items' => 'Search Galleries',
            'not_found' => 'No Galleries Found',
            'not_found_in_trash' => 'No Galleries Found in Trash',
            'parent' => 'Parent Gallery',
        )
    ));
}

if (function_exists('register_field_group')):
    /*register_field_group(array (
        'key' => 'group_546b8401907bd',
        'title' => 'Gallery Fields',
        'fields' => array (
            array (
                'key' => 'field_546b8410a1a30',
                'label' => 'Photos',
                'name' => 'photos',
                'prefix' => '',
                'type' => 'gallery',
                'instructions' => '',
                'required' => 1,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'min' => '',
                'max' => '',
                'preview_size' => 'thumbnail',
                'library' => 'uploadedTo',
            ),
        ),
        'location' => array (
            array (
                array (
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'gallery',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'acf_after_title',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
    ));*/
endif;