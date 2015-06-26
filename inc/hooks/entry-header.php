<?php

/**
 * Set title to H1 if in single view, otherwise set it to H2
 * @since 0.1.0
 * @param null $post
 * @return mixed|void
 */
function waboot_entry_title($post = null) {
    if (!isset($post)) {
        global $post;
    }

	if(!is_archive())
        if (get_behavior('show-title', $post->ID) == "0") return "";

	$title = get_the_title($post->ID);

	if (mb_strlen($title) == 0)
		return "";

	$title = apply_filters("waboot_entry_title_simple",$title); //@Deprecated
	$title = apply_filters("waboot_entry_title_text",$title);

    if(is_singular() ) {
        $str = sprintf(apply_filters('waboot_entry_title_text_singular', '<h1 class="entry-title" itemprop="name">%s</h1>'), $title); //@Deprecated
        $str = sprintf(apply_filters('waboot_entry_title_html_singular', '<h1 class="entry-title" itemprop="name">%s</h1>'), $title);
    }else{
        $str = sprintf(apply_filters('waboot_entry_title_text_posts', '<h2 class="entry-title" itemprop="name"><a class="entry-title" title="%s" rel="bookmark" href="%s">%s</a></h2>'), the_title_attribute('echo=0'), get_permalink(), $title); //@Deprecated
        $str = sprintf(apply_filters('waboot_entry_title_html_posts', '<h2 class="entry-title" itemprop="name"><a class="entry-title" title="%s" rel="bookmark" href="%s">%s</a></h2>'), the_title_attribute('echo=0'), get_permalink(), $title);
    }

    return apply_filters('waboot_entry_title_text', $str);
}

function waboot_print_entry_header() {
	$str = '<header class="entry-header">';
	$str .= waboot_entry_title();
	$str .= '</header>';

    if (!is_archive() && (get_behavior('title-position') == "top" || get_behavior("show-title") == "0"))
        echo "";
    else
        echo apply_filters('waboot_entry_header_text', $str);
}
add_action( 'waboot_entry_header', 'waboot_print_entry_header' );

function waboot_index_title($prefix = "", $suffix = "", $display = true) {
    $_post = get_queried_object();
    if (of_get_option('waboot_blogpage_displaytitle') == "1") {
        $title = $prefix . apply_filters('waboot_index_title_text', single_post_title('', false)) . $suffix;
    } else {
        $title = "";
    }

    if ($display) {
        echo $title;
    }
    return $title;
}

/**
 * Actions for printing the title of post\page outsite the "content-inner"
 */
add_action("waboot_before_inner","waboot_print_entry_title_before_inner");
function waboot_print_entry_title_before_inner(){
	global $post;
    if( is_home() ){
        if ( of_get_option('waboot_blogpage_title_position') == "top" ) {
            add_filter("waboot_entry_title_html_singular", "waboot_entry_title_before_inner_markup");
            waboot_index_title('<div class="title-wrapper"><div class="container"><h1 class=\'entry-header\' itemprop=\'name\'>', '</h1></div></div>');
        }
    }
    elseif( function_exists('is_product_category') && is_product_category() ){
        if ( of_get_option('waboot_woocommerce_title_position') == "top" ) {
            waboot_archive_page_title('<div class="title-wrapper"><div class="container"><h1 class=\'entry-header\' itemprop=\'name\'>', '</h1></div></div>');
            $term_description = term_description();
            if ( ! empty( $term_description ) )
                printf( '<div class="taxonomy-description"><div class="container">%s</div></div>', $term_description );
        }
    }
    elseif( function_exists('is_shop') && is_shop() ){
        if ( of_get_option('woocommerce_shop_title_position') == "top" ) {
            add_filter("waboot_entry_title_html_singular", "waboot_entry_title_before_inner_markup");
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
		add_filter("waboot_entry_title_html_singular","waboot_entry_title_before_inner_markup");
		echo waboot_entry_title();
	}
}

function waboot_entry_title_before_inner_markup($markup){
	return "<div class='title-wrapper'><div class='container'><h1 class='entry-title' itemprop='name'>%s</h1></div></div>";
}