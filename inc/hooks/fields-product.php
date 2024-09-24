<?php
if(function_exists("register_field_group")){
    // Product Accordion
	register_field_group(array (
        'key' => 'group_product_accordion',
        'title' => __('Informazioni prodotto', LANG_TEXTDOMAIN),
        'fields' => array (
            array (
                'key' => 'field_product_shipping',
                'label' => __('Spedizioni', LANG_TEXTDOMAIN),
                'name' => 'product_shipping',
                'type' => 'wysiwyg',
            ),
            array (
                'key' => 'field_product_refunds',
                'label' => __('Cambi e resi', LANG_TEXTDOMAIN),
                'name' => 'product_refunds',
                'type' => 'wysiwyg',
            ),
        ),
        'location' => array (
            array (
                array (
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'product',
                ),
            ),
        ),
        'menu_order' => 1,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
    ));

	//Product Color
    register_field_group(array (
        'key' => 'group_product_color',
        'title' => __('Colore', LANG_TEXTDOMAIN),
        'fields' => array (
            array (
                'key' => 'field_product_color',
                'label' => __('Colore', LANG_TEXTDOMAIN),
                'name' => 'product_color',
                'type' => 'color_picker',
                'default_value' => '#000',
            ),
        ),
        'location' => array (
            array (
                array (
                    'param' => 'taxonomy',
                    'operator' => '==',
                    'value' => 'pa_color',
                ),
            ),
        ),
        'menu_order' => 1,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
    ));

	// Product Gallery Videos
	register_field_group(array (
		'key' => 'group_product_video_oembed',
		'title' => __('Video del prodotto', LANG_TEXTDOMAIN),
		'fields' => array (
			array (
				'key' => 'field_product_video_repeater',
				'label' => __('Video', LANG_TEXTDOMAIN),
				'name' => 'product_video_repeater',
				'type' => 'repeater',
				'instructions' => __('Inserisci fino a 5 video', LANG_TEXTDOMAIN),
				'min' => 0,
				'max' => 5,
				'button_label' => __('Aggiungi video', LANG_TEXTDOMAIN),
				'sub_fields' => array (
					array (
						'key' => 'field_product_video_oembed',
						'label' => __('Video', LANG_TEXTDOMAIN),
						'name' => 'product_video_oembed',
						'type' => 'oembed',
						'instructions' => __('Inserisci l\'URL del video', LANG_TEXTDOMAIN),
						'width' => '',
						'height' => '',
					),
				),
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'product',
				),
			),
		),
		'menu_order' => 0,
	));
}