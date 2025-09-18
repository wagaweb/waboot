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
            'view_items'            => __('Visualizza Popup', LANG_TEXTDOMAIN),
            'search_items'          => __('Cerca popup', LANG_TEXTDOMAIN),
            'not_found'             => __('Nessun popup trovato', LANG_TEXTDOMAIN),
            'not_found_in_trash'    => __('Nessun popup nel cestino', LANG_TEXTDOMAIN),
            'all_items'             => __('Tutti i popup', LANG_TEXTDOMAIN),
            'archives'              => __('Archivi popup', LANG_TEXTDOMAIN),
            'attributes'            => __('Attributi popup', LANG_TEXTDOMAIN),
            'insert_into_item'      => __('Inserisci nel popup', LANG_TEXTDOMAIN),
            'uploaded_to_this_item' => __('Caricato in questo popup', LANG_TEXTDOMAIN),
            'filter_items_list'     => __('Filtra lista popup', LANG_TEXTDOMAIN),
            'items_list_navigation' => __('Navigazione lista popup', LANG_TEXTDOMAIN),
            'items_list'            => __('Lista popup', LANG_TEXTDOMAIN),
            'item_published'        => __('Popup pubblicato', LANG_TEXTDOMAIN),
            'item_published_privately'=> __('Popup pubblicato in privato', LANG_TEXTDOMAIN),
            'item_reverted_to_draft' => __('Popup riportato in bozza', LANG_TEXTDOMAIN),
            'item_scheduled'        => __('Popup programmato', LANG_TEXTDOMAIN),
            'item_updated'          => __('Popup aggiornato', LANG_TEXTDOMAIN),
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
    acf_add_local_field_group([
        'key' => 'group_popup_settings',
        'title' => __('Popup Settings', LANG_TEXTDOMAIN),
        'fields' => [

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
                'message' => __('Ignora la selezione delle singole pagine.', LANG_TEXTDOMAIN),
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
                'message' => __('Se selezionato, le pagine sopra saranno considerate come pagine da escludere invece che includere.', LANG_TEXTDOMAIN),
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

            // ===== TAB Prodotti =====
            [
                'key' => 'tab_products',
                'label' => __('Prodotti', LANG_TEXTDOMAIN),
                'type' => 'tab',
            ],
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
        ],
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

add_action('waboot/layout/page-after', function(){
    $view = new HTMLView(getAddonDirectory('popup').'/templates/frontend.php', false);
    $view->display();
}, 5);