<?php

namespace Waboot\addons\packages\megamenu;

/**
 * Eneble Reusable Blocks Menu Link in Backend
 */
add_action( 'admin_menu', function() {
    add_menu_page(
        esc_html__( 'Reusable Blocks', 'reusable-blocks-admin-menu-option' ),
        esc_html__( 'Reusable Blocks', 'reusable-blocks-admin-menu-option' ),
        'edit_posts',
        'edit.php?post_type=wp_block',
        '',
        'dashicons-layout',
        21
    );
});

/**
 * Add Relationship Block to Main Menu items
 */
add_action('init', function () {
    if (function_exists('acf_add_local_field_group')) :
        acf_add_local_field_group([
            'key' => 'group_megamenu',
            'title' => 'Blocco MegaMenu',
            'fields' => [
                [
                    'key' => 'field_related_menu',
                    'label' => 'Related Menu',
                    'name' => 'related_menu',
                    'type' => 'relationship',
                    'post_type' => ['wp_block'],
                    'filters' => [
                        0 => 'search',
                    ],
                    'return_format' => 'id',
                    'min' => 0,
                    'max' => 1,
                ],
            ],
            'location' => [
                [
                    [
                        'param' => 'nav_menu_item',
                        'operator' => '==',
                        'value' => 'location/main',
                    ],
                ],
            ],
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
        ]);
    endif;
});


/**
 * Append mega-menu to each related menu item.
 *
 * @param object $args   An object containing nav menu item arguments.
 * @param object $item   The menu item object.
 *
 * @return object $args  Modified nav menu item arguments.
 */

add_filter('nav_menu_item_args', function ($args, $item) {
    $args->after = '';
    $menuID = get_field('related_menu', $item->ID);

    if (empty($menuID)) {
        return $args;
    }

    $menu = get_post($menuID[0]);

    if (empty($menu)) {
        return $args;
    }
    $args->after .= '<div class="sub-menu sub-menu--megamenu">' . apply_filters('the_content', $menu->post_content) . '</div>';

    $item->classes[] = 'menu-item-has-children';
    $item->classes[] = 'menu-item-has-megamenu';

    return $args;
}, 20, 4);


/**
 * Add custom CSS class to navigation menu items with children.
 *
 * @param array    $classes CSS classes for the menu item.
 * @param WP_Post  $item    The current menu item.
 *
 * @return array Modified array of CSS classes.
 */
add_filter('nav_menu_css_class', function($classes, $item) {
    if (in_array('menu-item-has-children', $item->classes)) {
        $classes[] = 'menu-item-has-children';
    }

    if (in_array('menu-item-has-megamenu', $item->classes)) {
        $classes[] = 'menu-item-has-megamenu';
    }

    return $classes;
}, 10, 2);

