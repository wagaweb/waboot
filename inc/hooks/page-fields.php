<?php

namespace Waboot\inc\hooks;

add_action('init', function () {
    if (function_exists("register_field_group")) :

        register_field_group([
            'key' => 'group_page_fields',
            'title' => __('Extra Fields', LANG_TEXTDOMAIN),
            'fields' => [
                [
                    'key' => 'field_hide_title',
                    'label' => __('Hide Title', LANG_TEXTDOMAIN),
                    'name' => 'hide_title',
                    'type' => 'true_false',
                    'default_value' => 0,
                    'ui' => 1,
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ],
                ],
            ],
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
        ]);

    endif;
});
