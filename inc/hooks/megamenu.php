<?php

namespace Waboot\inc\hooks;

use Walker_Nav_Menu;

add_action('init', function () {
    if (function_exists('acf_add_local_field_group')) :
        acf_add_local_field_group([
            'key' => 'group_megamenu',
            'title' => 'Blocco Modal',
            'fields' => [
                [
                    'key' => 'field_megamenu_block',
                    'label' => 'Seleziona il blocco',
                    'name' => 'megamenu_block',
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
                        'value' => 'location/megamenu',
                    ],
                ],
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'page',
                    ],
                ],
                [
                    [
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'product',
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

class Walker_Menu_Type extends Walker_Nav_Menu {
    private $menu_type;

    public function __construct($menu_type = 'default') {
        $this->menu_type = $menu_type;
    }

    public function start_lvl(&$output, $depth = 0, $args = null) {
        if ($this->menu_type === 'default') {
            $indent = str_repeat("\t", $depth);
            $output .= "\n$indent<ul class=\"sub-menu\" role=\"menu\" aria-hidden=\"true\">\n";
        }
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        if ($this->menu_type === 'default') {
            $indent = str_repeat("\t", $depth);
            $output .= "$indent</ul>\n";
        }
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? [] : (array) $item->classes;
        $has_children = in_array('menu-item-has-children', $classes);
        $has_megamenu = get_field('megamenu_block', 'nav_menu_item_' . $item->ID);
        
        // Determina il tipo di menu per questo elemento
        $item_menu_type = $this->menu_type;
        if ($has_megamenu) {
            $item_menu_type = 'pattern';
        } elseif ($has_children && $this->menu_type === 'megamenu') {
            $item_menu_type = 'megamenu';
        }

        // Aggiungi classi specifiche per il tipo di menu
        if ($item_menu_type === 'pattern') {
            $classes[] = 'has-megamenu-pattern';
        } elseif ($item_menu_type === 'megamenu') {
            $classes[] = 'has-megamenu-classic';
        }

        $class_names = implode(' ', array_filter($classes));
        $aria_has_popup = ($has_children || $has_megamenu) ? ' aria-haspopup="true" aria-expanded="false"' : '';

        $output .= '<li class="' . esc_attr($class_names) . '">';

        // Link principale
        $output .= '<a href="' . esc_url($item->url) . '"' . $aria_has_popup . '>';
        $output .= esc_html($item->title);
        $output .= '</a>';

        // Gestione dei sottomenu in base al tipo
        if ($item_menu_type === 'pattern' && $has_megamenu) {
            $block = get_post($has_megamenu);
            if ($block && $block->post_status === 'publish') {
                $output .= '<div class="mega-menu mega-menu--pattern" role="region" aria-label="' . esc_attr($item->title) . '">';
                $output .= '<div class="mega-menu__columns">';
                $output .= do_blocks($block->post_content);
                $output .= '</div>';
                $output .= '</div>';
            }
        } elseif ($item_menu_type === 'megamenu' && $has_children) {
            $output .= '<div class="mega-menu mega-menu--classic" role="region" aria-label="' . esc_attr($item->title) . '">';
            $output .= '<div class="mega-menu__columns">';
            // Il sottomenu classico verrà aggiunto da start_lvl/end_lvl
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        $has_children = in_array('menu-item-has-children', empty($item->classes) ? [] : (array) $item->classes);
        $has_megamenu = get_field('megamenu_block', 'nav_menu_item_' . $item->ID);
        
        if ($this->menu_type === 'megamenu' && $has_children && !$has_megamenu) {
            $output .= '</div></div>'; // Chiude mega-menu--classic
        }
        
        $output .= '</li>';
    }
}