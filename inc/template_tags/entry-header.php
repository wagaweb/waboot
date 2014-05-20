<?php
/**
 * Print the opening markup for the entry header.
 * @since 1.0
 */
function waboot_entry_header_markup_open() {
    echo '<header class="entry-header">';
}
add_action( 'waboot_entry_header', 'waboot_entry_header_markup_open', 5 );

/**
 * Set title to H1 if in single view, otherwise set it to H2
 * @since 1.0
 */
function waboot_do_entry_title() {

    $title = get_the_title();

    if ( 0 === mb_strlen( $title ) )
        return;

    if ( is_singular() ) {
        $entry_title = sprintf( '<h1 class="entry-title">%s</h1>', $title );

    } else {
        $entry_title = sprintf( '<h2 class="entry-title"><a class="entry-title" title="%s" rel="bookmark" href="%s">%s</a></h2>', the_title_attribute( 'echo=0' ), get_permalink(), $title );

    }
    echo apply_filters( 'waboot_entry_title_text', $entry_title );
}
add_action( 'waboot_entry_header', 'waboot_do_entry_title' );

/**
 * Print the closing markup for the entry header.
 * @since 1.0
 */
function waboot_entry_header_markup_close() {
    echo '</header>';
}
add_action( 'waboot_entry_header', 'waboot_entry_header_markup_close', 15 );