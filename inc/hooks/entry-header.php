<?php

add_action('waboot_entry_header','waboot_print_entry_header');
add_action("waboot_before_inner","waboot_print_entry_title_before_inner");
add_filter("waboot_entry_title_html_singular","waboot_entry_title_before_inner_singular_markup");

function waboot_print_entry_header() {
	$str = '<header class="entry-header">';
	$str .= waboot_entry_title();
	$str .= '</header>';

    if (!is_archive() && (get_behavior('title-position') == "top" || get_behavior("show-title") == "0"))
        echo "";
    else
        echo apply_filters('waboot_entry_header_text', $str);
}

/**
 * Actions for printing the title of post\page outsite the "content-inner"
 */
function waboot_print_entry_title_before_inner(){
    if( is_home() ){
        if ( of_get_option('waboot_blogpage_title_position') == "top" ) {
	        waboot_index_title('<div class="title-wrapper"><div class="container"><h1 class=\'entry-header\' itemprop=\'name\'>', '</h1></div></div>',true);
        }
    }
    elseif( function_exists('is_product_category') && is_product_category() ){ //@woocommerce hard-coded integration
        if ( of_get_option('waboot_woocommerce_title_position') == "top" ) {
            waboot_archive_page_title('<div class="title-wrapper"><div class="container"><h1 class=\'entry-header\' itemprop=\'name\'>', '</h1></div></div>');
            $term_description = term_description();
            if ( ! empty( $term_description ) )
                printf( '<div class="taxonomy-description"><div class="container">%s</div></div>', $term_description );
        }
    }
    elseif( function_exists('is_shop') && is_shop() ){ //@woocommerce hard-coded integration
        if ( of_get_option('woocommerce_shop_title_position') == "top" ) {
            waboot_index_title('<div class="title-wrapper"><div class="container"><h1 class=\'entry-header\' itemprop=\'name\'>', '</h1></div></div>');
        }
    }
    elseif( is_archive() ){
	    if ( of_get_option('waboot_blogpage_title_position') == "top" ) {
		    waboot_archive_page_title('<div class="title-wrapper"><div class="container"><h1 class=\'entry-header\' itemprop=\'name\'>', '</h1></div></div>');
            $term_description = term_description();
            if ( ! empty( $term_description ) )
                printf( '<div class="taxonomy-description"><div class="container">%s</div></div>', $term_description );
	    }
    }
    elseif( is_singular() && get_behavior('title-position') == "top" ){
		echo waboot_entry_title();
	}
}

/**
 * Specify the markup for is_singular() title (if before inner)
 * @return string
 */
function waboot_entry_title_before_inner_singular_markup($markup){
	if(is_singular() && get_behavior('title-position') == "top" ){
		return "<div class='title-wrapper'><div class='container'><h1 class='entry-title' itemprop='name'>%s</h1></div></div>";
	}
	return $markup;
}