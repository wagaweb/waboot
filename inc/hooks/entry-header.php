<?php
/**
 * Set title to H1 if in single view, otherwise set it to H2
 * @since 0.1.0
 */
function waboot_print_entry_header() {
    echo '<header class="entry-header">';
    $title = get_the_title();
    if(0 === mb_strlen($title))
        return;
    if(is_singular() ) {
        $entry_title = sprintf( '<h1 class="entry-title">%s</h1>', $title );
    }else{
        $entry_title = sprintf( '<h2 class="entry-title"><a class="entry-title" title="%s" rel="bookmark" href="%s">%s</a></h2>', the_title_attribute( 'echo=0' ), get_permalink(), $title );
    }
    echo apply_filters( 'waboot_entry_title_text', $entry_title );
    echo '</header>';
}
add_action( 'waboot_entry_header', 'waboot_print_entry_header' );

/**
 * Nasconde il titolo per post e pagine, utilizzando il modulo behavior
 * @param $title
 * @return string
 * @uses waboot_entry_title_text filter (inc/hooks.php::waboot_do_entry_title)
 * @since 0.1.0
 */
function waboot_title_toggler($title){
    $show_title = get_behavior("show-title","array");

    if(!$show_title->is_enabled_for_current_node()){
        return $title;
    }else{
        if(is_singular() && ($show_title->value == "0" || !$show_title->value || $show_title->value == 0)){
            return "";
        }else{
            return $title;
        }
    }
}
add_filter("waboot_entry_title_text","waboot_title_toggler");