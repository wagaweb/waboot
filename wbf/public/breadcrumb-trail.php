<?php

/**
 * Shows a breadcrumb for all types of pages.  This is a wrapper function for the Breadcrumb_Trail class,
 * which should be used in theme templates.
 *
 * @since  0.1.0
 * @access public
 * @param  array $args Arguments to pass to Breadcrumb_Trail.
 *                     The available options are the default ones for Breadcrumb_Trail (https://github.com/justintadlock/breadcrumb-trail#parameters), plus:
 *                     - wrapper_start: a wrapper open tag (it wraps all the content of the container)
 *                     - wrapper_end: the wrapper close tag
 *                     - additional_classes: a string (space separated) of classes to add to breadcrumb container (since 0.3.10)
 * @return void
 */
function waboot_breadcrumb_trail( $args = array() ) {

    if ( function_exists( 'is_bbpress' ) && is_bbpress() )
        $breadcrumb = new bbPress_Breadcrumb_Trail( $args );
    else
        $breadcrumb = new Waboot_Breadcrumb_Trail( $args );

    $breadcrumb->trail();
}

class Waboot_Breadcrumb_Trail extends Breadcrumb_Trail{
    /**
     * Formats and outputs the breadcrumb trail.
     *
     * @since  1.0
     * @access public
     * @return string
     */
    public function trail() {

        $breadcrumb = '';

        /* Connect the breadcrumb trail if there are items in the trail. */
        if ( !empty( $this->items ) && is_array( $this->items ) ) {

            /* Make sure we have a unique array of items. */
            $this->items = array_unique($this->items);

            /* Open the breadcrumb trail containers. */
            $breadcrumb = "\n\t\t" . '<' . tag_escape($this->args['container']) . ' class="breadcrumb-trail breadcrumbs ' . $this->args['additional_classes'] . '" itemprop="breadcrumb">';

            /* Crea Wrapper */
            $breadcrumb .= !empty( $this->args['wrapper_start'] )? $this->args['wrapper_start'] : "";

            /* If $before was set, wrap it in a container. */
            $breadcrumb .= ( !empty( $this->args['before'] ) ? "\n\t\t\t" . '<span class="trail-before">' . $this->args['before'] . '</span> ' . "\n\t\t\t" : '' );

            /* Add 'browse' label if it should be shown. */
            if ( true === $this->args['show_browse'] )
                $breadcrumb .= "\n\t\t\t" . '<span class="trail-browse">' . $this->args['labels']['browse'] . '</span> ';

            /* Adds the 'trail-begin' class around first item if there's more than one item. */
            if ( 1 < count( $this->items ) )
                array_unshift( $this->items, '<span class="trail-begin">' . array_shift( $this->items ) . '</span>' );

            /* Adds the 'trail-end' class around last item. */
            array_push( $this->items, '<span class="trail-end">' . array_pop( $this->items ) . '</span>' );

            /* Format the separator. */
            $separator = ( !empty( $this->args['separator'] ) ? '<span class="sep">' . $this->args['separator'] . '</span>' : '<span class="sep">/</span>' );

            /* Join the individual trail items into a single string. */
            $breadcrumb .= join( "\n\t\t\t {$separator} ", $this->items );

            /* If $after was set, wrap it in a container. */
            $breadcrumb .= ( !empty( $this->args['after'] ) ? "\n\t\t\t" . ' <span class="trail-after">' . $this->args['after'] . '</span>' : '' );

            /* Chiude Wrapper */
            $breadcrumb .= !empty( $this->args['wrapper_end'] )? $this->args['wrapper_end'] : "";

            /* Close the breadcrumb trail containers. */
            $breadcrumb .= "\n\t\t" . '</' . tag_escape( $this->args['container'] ) . '>';
        }

        /* Allow developers to filter the breadcrumb trail HTML. */
        $breadcrumb = apply_filters( 'breadcrumb_trail', $breadcrumb, $this->args );

        if ( true === $this->args['echo'] )
            echo $breadcrumb;
        else
            return $breadcrumb;
    }
}