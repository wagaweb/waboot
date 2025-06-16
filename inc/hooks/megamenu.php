<?php

namespace Waboot\inc\hooks;

use Walker_Nav_Menu;

/**
 * Add MegaMenu block options to WordPress menu items.
 *
 * This code allows assigning reusable blocks (wp_block) categorized under
 * 'megamenu' to WordPress menu items. The assigned block content is displayed
 * dynamically in the frontend.
 */

/**
 * Adds a custom field in the menu editor for assigning a MegaMenu block.
 *
 * @param int    $itemId  The menu item ID.
 */
add_action('wp_nav_menu_item_custom_fields', function ($itemId) {
    // Fetch reusable blocks categorized under 'megamenu'
    $megaMenuBlocks = get_posts([
        'post_type' => 'wp_block',
        'tax_query' => [[
            'taxonomy' => 'wp_pattern_category',
            'field'    => 'slug',
            'terms'    => 'megamenu',
        ]],
        'posts_per_page' => -1,
    ]);

    // Get the current MegaMenu block assigned to this menu item
    $currentValue = get_post_meta($itemId, '_megamenu_block', true);

    // Display the custom field in the menu editor
    echo '<p class="field-megamenu description description-wide">
        <label for="edit-menu-item-megamenu-block-' . esc_attr($itemId) . '">' . esc_html__('Blocco Megamenu', 'waboot') . '</label>
        <select name="menu-item-megamenu-block[' . esc_attr($itemId) . ']" id="edit-menu-item-megamenu-block-' . esc_attr($itemId) . '">
            <option value="">' . esc_html__('Nessun Blocco', 'waboot') . '</option>';

    foreach ($megaMenuBlocks as $block) {
        echo '<option value="' . esc_attr($block->ID) . '"' . selected($currentValue, $block->ID, false) . '>' . esc_html($block->post_title) . '</option>';
    }

    echo '</select></p>';
}, 10, 4);

/**
 * Save the MegaMenu block assignment when a menu item is updated.
 *
 * @param int $menuId      The menu ID.
 * @param int $menuItemId  The menu item ID.
 */
add_action('wp_update_nav_menu_item', function ($menuId, $menuItemId) {
    if (!isset($_POST['menu-item-megamenu-block'][$menuItemId])) {
        return;
    }

    $metaValue = sanitize_text_field($_POST['menu-item-megamenu-block'][$menuItemId]);

    // Update or delete the meta value based on the input
    if (!empty($metaValue)) {
        update_post_meta($menuItemId, '_megamenu_block', $metaValue);
    } else {
        delete_post_meta($menuItemId, '_megamenu_block');
    }
}, 10, 2);

class Walker_Megamenu_Block extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"sub-menu\">\n";
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $block_id = get_post_meta($item->ID, '_megamenu_block', true);

        $block = $block_id ? get_post($block_id) : null;
        $has_block = $block && $block->post_status === 'publish';

        if ($has_block) {
            $classes[] = 'has-megamenu';
        }

        $class_names = implode(' ', array_filter($classes));
        $output .= '<li class="' . esc_attr($class_names) . '">';

        // Link senza attributi aria dinamici
        $output .= '<a href="' . esc_url($item->url) . '">';
        $output .= esc_html($item->title);
        $output .= '</a>';

        if ($has_block) {
            $output .= '<div class="mega-menu" id="megamenu-' . esc_attr($item->ID) . '" role="region">';
            $output .= '<div class="mega-menu__inner">';
            $output .= '<div class="mega-menu__content">';
            $output .= apply_filters('the_content', $block->post_content);
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= "</li>\n";
    }
}
