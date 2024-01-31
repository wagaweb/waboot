<?php

namespace Waboot\inc\hooks;

add_action('init', function () {
    if( function_exists("register_field_group") ):

        register_field_group(array(
            'key' => 'group_page_fields',
            'title' => __('Extra Fields', LANG_TEXTDOMAIN),
            'fields' => array(
                array(
                    'key' => 'field_hide_title',
                    'label' => __('Hide Title', LANG_TEXTDOMAIN),
                    'name' => 'hide_title',
                    'type' => 'true_false',
                    'required' => 0,
                    'default_value' => 0,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
            'show_in_rest' => 0,
        ));

    endif;
});

