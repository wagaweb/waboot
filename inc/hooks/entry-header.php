<?php

/**
 * Set title to H1 if in single view, otherwise set it to H2
 * @since 0.1.0
 */
function waboot_entry_title(){
    global $post;

    if(get_behavior('show-title') == "0") return "";

    $title = get_the_title($post->ID);

    if (mb_strlen($title) == 0)
        return "";
    if(is_singular() ) {
        $str = sprintf(apply_filters('waboot_entry_title_text_singular', '<h1 class="entry-title">%s</h1>'), $title);
    }else{
        $str = sprintf(apply_filters('waboot_entry_title_text_posts', '<h2 class="entry-title"><a class="entry-title" title="%s" rel="bookmark" href="%s">%s</a></h2>'), the_title_attribute('echo=0'), get_permalink(), $title);
    }

    return apply_filters('waboot_entry_title_text', $str);
}

function waboot_print_entry_header() {
	$str = '<header class="entry-header">';
	$str .= waboot_entry_title();
	$str .= '</header>';

    if (get_behavior('title-position') == "top" || get_behavior("show-title") == "0")
        echo "";
    else
        echo apply_filters('waboot_entry_header_text', $str);
}
add_action( 'waboot_entry_header', 'waboot_print_entry_header' );

function waboot_index_title($display = true)
{
    $_post = get_queried_object();
    if (get_behavior('show-title', $_post->ID) == "1") {
        $title = apply_filters('waboot_index_title_text', single_post_title('', false));
    } else {
        $title = "";
    }

    if ($display) {
        echo $title;
    }
    return $title;
}

function waboot_index_title_wrapper($title)
{
    return "<h1 class='entry-header'>" . $title . "</h1>";
}

add_filter('waboot_index_title_text', 'waboot_index_title_wrapper');

/**
 * Apply the "show-title" behavior to the entry header.
 * @param $title
 * @return string
 * @since 0.1.0
 * @deprecated
 */
function waboot_entries_title_toggler($title)
{
    if (get_behavior('title-position') == "top") return "";
    $show_title = get_behavior("show-title", 0, "object");
    if($show_title->is_enabled_for_current_node()){
        if(is_singular() && ($show_title->value == "0" || !$show_title->value || $show_title->value == 0)){
            return "";
        }
    }
    return $title;
}
//add_filter("waboot_entry_header_text","waboot_entries_title_toggler");