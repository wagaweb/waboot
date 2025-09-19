<?php

namespace Waboot\addons\packages\popup;

use Waboot\inc\core\mvc\HTMLView;
use function Waboot\addons\getAddonDirectory;

/*
 *  Register CPT Popup
 */
add_action('init', function () {
    register_post_type('popup', [
        'labels' => [
            'name'                  => __('Popup Manager', LANG_TEXTDOMAIN),
            'singular_name'         => __('Popup', LANG_TEXTDOMAIN),
            'menu_name'             => __('Popup Manager', LANG_TEXTDOMAIN),
            'name_admin_bar'        => __('Popup', LANG_TEXTDOMAIN),
            'add_new'               => __('Aggiungi nuovo', LANG_TEXTDOMAIN),
            'add_new_item'          => __('Aggiungi nuovo popup', LANG_TEXTDOMAIN),
            'edit_item'             => __('Modifica popup', LANG_TEXTDOMAIN),
            'new_item'              => __('Nuovo popup', LANG_TEXTDOMAIN),
            'view_item'             => __('Visualizza popup', LANG_TEXTDOMAIN),
            'all_items'             => __('Tutti i popup', LANG_TEXTDOMAIN),
        ],
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'publicly_queryable' => false,
        'supports'           => ['title', 'editor'],
        'menu_icon'          => 'dashicons-feedback',
    ]);
});

/*
 *  Register Popup Fields
 */
if( function_exists('acf_add_local_field_group') ) {

    $fields = [
        // ===== TAB Generali =====
        [
            'key' => 'tab_general',
            'label' => __('Generali', LANG_TEXTDOMAIN),
            'type' => 'tab',
        ],
        [
            'key' => 'field_global_popup',
            'label' => __('Mostra su tutto il sito', LANG_TEXTDOMAIN),
            'name' => 'global_popup',
            'type' => 'true_false',
            'message' => __('Se selezionato, il popup apparirà su tutte le pagine ignorando filtri e selezioni.', LANG_TEXTDOMAIN),
            'default_value' => 0,
        ],
        [
            'key' => 'field_show_after',
            'label' => __('Mostra dopo (secondi)', LANG_TEXTDOMAIN),
            'name' => 'show_after',
            'type' => 'number',
            'default_value' => 3,
        ],
        [
            'key' => 'field_max_views',
            'label' => __('Max visualizzazioni per utente', LANG_TEXTDOMAIN),
            'name' => 'max_views',
            'type' => 'number',
            'default_value' => 1,
        ],
        [
            'key' => 'field_custom_post_types',
            'label' => __('Custom Post Types', LANG_TEXTDOMAIN),
            'name' => 'custom_post_types',
            'type' => 'checkbox',
            'instructions' => __('Seleziona i custom post type su cui mostrare il popup', LANG_TEXTDOMAIN),
            'choices' => [], // verranno popolati dal filtro
            'return_format' => 'value',
        ],


        // ===== TAB Aspetto =====
        [
            'key' => 'tab_appearance',
            'label' => __('Aspetto', LANG_TEXTDOMAIN),
            'type' => 'tab',
        ],
        [
            'key' => 'field_width',
            'label' => __('Larghezza', LANG_TEXTDOMAIN),
            'name' => 'width',
            'type' => 'number',
            'default_value' => 600,
        ],
        [
            'key' => 'field_max_height',
            'label' => __('Altezza massima', LANG_TEXTDOMAIN),
            'name' => 'max_height',
            'type' => 'number',
            'instructions' => __('Altezza massima del popup in px (0 = nessun limite)', LANG_TEXTDOMAIN),
            'default_value' => 0,
            'append' => 'px',
            'min' => 0,
            'step' => 1,
        ],
        [
            'key' => 'field_padding',
            'label' => __('Padding', LANG_TEXTDOMAIN),
            'name' => 'padding',
            'type' => 'number',
            'default_value' => 16,
        ],
        [
            'key' => 'field_position',
            'label' => __('Posizione', LANG_TEXTDOMAIN),
            'name' => 'position',
            'type' => 'select',
            'choices' => [
                'top-left'      => __('In alto a sinistra', LANG_TEXTDOMAIN),
                'top-center'    => __('In alto al centro', LANG_TEXTDOMAIN),
                'top-right'     => __('In alto a destra', LANG_TEXTDOMAIN),
                'center-left'   => __('Centro sinistra', LANG_TEXTDOMAIN),
                'center'        => __('Centro', LANG_TEXTDOMAIN),
                'center-right'  => __('Centro destra', LANG_TEXTDOMAIN),
                'bottom-left'   => __('In basso a sinistra', LANG_TEXTDOMAIN),
                'bottom-center' => __('In basso al centro', LANG_TEXTDOMAIN),
                'bottom-right'  => __('In basso a destra', LANG_TEXTDOMAIN),
            ],
            'default_value' => 'center',
            'ui' => 1,
        ],
        [
            'key' => 'field_popup_offset',
            'label' => __('Distanza dal bordo', LANG_TEXTDOMAIN),
            'name' => 'offset',
            'type' => 'number',
            'default_value' => 16,
            'append' => 'px',
            'min' => 0,
            'step' => 1,
        ],
        [
            'key' => 'field_padding',
            'label' => __('Padding contenuto', LANG_TEXTDOMAIN),
            'name' => 'padding',
            'type' => 'number',
            'default_value' => 16,
            'append' => 'px',
            'min' => 0,
            'step' => 1,
        ],

        // ===== TAB Pagine =====
        [
            'key' => 'tab_pages',
            'label' => __('Pagine', LANG_TEXTDOMAIN),
            'type' => 'tab',
        ],
        [
            'key' => 'field_pages_all',
            'label' => __('Mostra su tutte le pagine', LANG_TEXTDOMAIN),
            'name' => 'pages_all',
            'type' => 'true_false',
            'default_value' => 0,
        ],
        [
            'key' => 'field_pages',
            'label' => __('Seleziona pagine da includere', LANG_TEXTDOMAIN),
            'name' => 'pages',
            'type' => 'post_object',
            'post_type' => ['page'],
            'return_format' => 'id',
            'multiple' => 1,
        ],
        [
            'key' => 'field_pages_exclude',
            'label' => __('Considera come esclusione', LANG_TEXTDOMAIN),
            'name' => 'pages_exclude',
            'type' => 'true_false',
            'default_value' => 0,
        ],

        // ===== TAB Post =====
        [
            'key' => 'tab_post',
            'label' => __('Post', LANG_TEXTDOMAIN),
            'type' => 'tab',
        ],
        [
            'key' => 'field_post_category_all',
            'label' => __('Tutte le categorie post', LANG_TEXTDOMAIN),
            'name' => 'post_category_all',
            'type' => 'true_false',
            'default_value' => 0,
        ],
        [
            'key' => 'field_post_category',
            'label' => __('Categorie da includere', LANG_TEXTDOMAIN),
            'name' => 'post_category',
            'type' => 'taxonomy',
            'taxonomy' => 'category',
            'field_type' => 'multi_select',
            'return_format' => 'id',
            'multiple' => 1,
        ],
        [
            'key' => 'field_post_category_exclude',
            'label' => __('Considera come esclusione', LANG_TEXTDOMAIN),
            'name' => 'post_category_exclude',
            'type' => 'true_false',
            'default_value' => 0,
        ],
    ];

    // ===== TAB Prodotti (solo se WooCommerce attivo) =====
    if (class_exists('WooCommerce')) {
        $fields[] = [
            'key' => 'tab_products',
            'label' => __('Prodotti', LANG_TEXTDOMAIN),
            'type' => 'tab',
        ];

        $woo_fields = [
            [
                'key' => 'field_product_category_all',
                'label' => __('Tutte le categorie prodotto', LANG_TEXTDOMAIN),
                'name' => 'product_category_all',
                'type' => 'true_false',
                'default_value' => 0,
            ],
            [
                'key' => 'field_product_category',
                'label' => __('Categorie da includere', LANG_TEXTDOMAIN),
                'name' => 'product_category',
                'type' => 'taxonomy',
                'taxonomy' => 'product_cat',
                'field_type' => 'multi_select',
                'return_format' => 'id',
                'multiple' => 1,
            ],
            [
                'key' => 'field_product_category_exclude',
                'label' => __('Considera come esclusione', LANG_TEXTDOMAIN),
                'name' => 'product_category_exclude',
                'type' => 'true_false',
                'default_value' => 0,
            ],
            [
                'key' => 'field_product_tag_all',
                'label' => __('Tutti i tag prodotto', LANG_TEXTDOMAIN),
                'name' => 'product_tags_all',
                'type' => 'true_false',
                'default_value' => 0,
            ],
            [
                'key' => 'field_product_tag',
                'label' => __('Tag da includere', LANG_TEXTDOMAIN),
                'name' => 'product_tag',
                'type' => 'taxonomy',
                'taxonomy' => 'product_tag',
                'field_type' => 'multi_select',
                'return_format' => 'id',
                'multiple' => 1,
            ],
            [
                'key' => 'field_product_tag_exclude',
                'label' => __('Considera come esclusione', LANG_TEXTDOMAIN),
                'name' => 'product_tag_exclude',
                'type' => 'true_false',
                'default_value' => 0,
            ],
            [
                'key' => 'field_products_all',
                'label' => __('Tutti i prodotti', LANG_TEXTDOMAIN),
                'name' => 'products_all',
                'type' => 'true_false',
                'default_value' => 0,
            ],
            [
                'key' => 'field_products',
                'label' => __('Prodotti specifici da includere', LANG_TEXTDOMAIN),
                'name' => 'products',
                'type' => 'post_object',
                'post_type' => ['product'],
                'return_format' => 'id',
                'multiple' => 1,
            ],
            [
                'key' => 'field_products_exclude',
                'label' => __('Considera come esclusione', LANG_TEXTDOMAIN),
                'name' => 'products_exclude',
                'type' => 'true_false',
                'default_value' => 0,
            ],
        ];

        $fields = array_merge($fields, $woo_fields);
    }

    acf_add_local_field_group([
        'key' => 'group_popup_settings',
        'title' => __('Popup Settings', LANG_TEXTDOMAIN),
        'fields' => $fields,
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'popup',
                ],
            ],
        ],
    ]);
}

add_filter('acf/load_field/name=custom_post_types', function($field){
    $field['choices'] = [];
    $exclude = ['popup', 'post', 'page', 'attachment', 'product'];
    $post_types = get_post_types(['public' => true], 'objects');
    foreach($post_types as $cpt) {
        if(in_array($cpt->name, $exclude)) continue;
        $field['choices'][$cpt->name] = $cpt->label;
    }
    return $field;
});



add_action('waboot/layout/page-after', function(){
    $view = new HTMLView(getAddonDirectory('popup').'/templates/frontend.php', false);
    $view->display();
}, 5);
