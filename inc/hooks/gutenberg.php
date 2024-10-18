<?php
namespace Waboot\inc\hooks;

/**
 * Custom Gutenberg color palette based on Theme Options choice and custom font sizes
 */
add_action('after_setup_theme', function() {
    $customColors = array(
        array(
            'name'  => esc_attr__('Primary', LANG_TEXTDOMAIN),
            'slug'  => 'primary',
            'color' => '#212121',
        ),
        array(
            'name'  => esc_attr__('Secondary', LANG_TEXTDOMAIN),
            'slug'  => 'secondary',
            'color' => '#16AEA1',
        ),
        array(
            'name'  => esc_attr__('Text', LANG_TEXTDOMAIN),
            'slug'  => 'text',
            'color' => '#333',
        ),
        array(
            'name'  => esc_attr__('Text Light', LANG_TEXTDOMAIN),
            'slug'  => 'text-light',
            'color' => '#777',
        ),
        array(
            'name'  => esc_attr__('Grey', LANG_TEXTDOMAIN),
            'slug'  => 'grey',
            'color' => '#CCC',
        ),
        array(
            'name'  => esc_attr__('Grey Light', LANG_TEXTDOMAIN),
            'slug'  => 'grey-light',
            'color' => '#f4f4f4',
        ),
        array(
            'name'  => esc_attr__('Grey Dark', LANG_TEXTDOMAIN),
            'slug'  => 'grey-dark',
            'color' => '#555',
        ),
        array(
            'name'  => esc_attr__('Grey Darken', LANG_TEXTDOMAIN),
            'slug'  => 'grey-darken',
            'color' => '#212121',
        ),
        array(
            'name'  => esc_attr__('Success', LANG_TEXTDOMAIN),
            'slug'  => 'success',
            'color' => '#6DBF73',
        ),
        array(
            'name'  => esc_attr__('Error', LANG_TEXTDOMAIN),
            'slug'  => 'error',
            'color' => '#EC5B56',
        ),
        array(
            'name'  => esc_attr__('Warning', LANG_TEXTDOMAIN),
            'slug'  => 'warning',
            'color' => '#FF9551',
        ),
    );

    $defaultColors = array(
        array(
            'name'  => __( 'Black', 'default' ),
            'slug'  => 'black',
            'color' => '#000000',
        ),
        array(
            'name'  => __( 'Cyan bluish gray', 'default' ),
            'slug'  => 'cyan-bluish-gray',
            'color' => '#ABB8C3',
        ),
        array(
            'name'  => __( 'White', 'default' ),
            'slug'  => 'white',
            'color' => '#FFFFFF',
        ),
        array(
            'name'  => __( 'Pale Pink', 'default' ),
            'slug'  => 'pale-pink',
            'color' => '#f78da7',
        ),
        array(
            'name'  => __( 'Vivid Red', 'default' ),
            'slug'  => 'vivid-red',
            'color' => '#cf2e2e',
        ),
        array(
            'name'  => __( 'Luminous Vivid Orange', 'default' ),
            'slug'  => 'luminous-vivid-orange',
            'color' => '#ff6900',
        ),
        array(
            'name'  => __( 'Luminous Vivid Amber', 'default' ),
            'slug'  => 'luminous-vivid-amber',
            'color' => '#fcb900',
        ),
        array(
            'name'  => __( 'Light Green Cyan', 'default' ),
            'slug'  => 'light-green-cyan',
            'color' => '#7bdcb5',
        ),
        array(
            'name'  => __( 'Vivid Green Cyan', 'default' ),
            'slug'  => 'vivid-green-cyan',
            'color' => '#00d084',
        ),
        array(
            'name'  => __( 'Pale Cyan Blue', 'default' ),
            'slug'  => 'pale-cyan-blue',
            'color' => '#8ed1fc',
        ),
        array(
            'name'  => __( 'Vivid Cyan Blue', 'default' ),
            'slug'  => 'vivid-cyan-blue',
            'color' => '#0693e3',
        ),
        array(
            'name'  => __( 'Vivid Purple', 'default' ),
            'slug'  => 'vivid-purple',
            'color' => '#9b51e0',
        ),
    );

    $editorColors = array_merge($customColors, $defaultColors);

    add_theme_support('editor-color-palette', $editorColors);
    add_theme_support( 'custom-spacing' );
    add_theme_support( 'border' );
    add_theme_support( 'link-color' );
});