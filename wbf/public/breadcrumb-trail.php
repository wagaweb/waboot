<?php

/**
 * Shows a breadcrumb for all types of pages.  This is a wrapper function for the Breadcrumb_Trail class,
 * which should be used in theme templates.
 *
 * It uses the Wordpress permalink structures to build the trails.
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
function wbf_breadcrumb_trail( $args = array() ) {

    if ( function_exists( 'is_bbpress' ) && is_bbpress() )
        $breadcrumb = new bbPress_Breadcrumb_Trail( $args );
    else
        $breadcrumb = new WBF_Breadcrumb_Trail( $args );

    $breadcrumb->trail();
}

class WBF_Breadcrumb_Trail extends Breadcrumb_Trail{
    /**
     * Formats and outputs the breadcrumb trail.
     *
     * @since  1.0
     * @access public
     * @return string
     */
    public function trail() {

        $breadcrumb = '';

	    /* Allow developers to edit BC items. */
	    $this->items = apply_filters("wbf/breadcrumb_trail/items",$this->items);

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

    /**
     * Adds a specific post's hierarchy to the items array.  The hierarchy is determined by post type's
     * rewrite arguments and whether it has an archive page.
     *
     * @since  0.6.0
     * @access public
     * @param  int $post_id The ID of the post to get the hierarchy for.
     * @return void
     */
    public function do_post_hierarchy($post_id) {
        $permalink_structure = get_option( 'permalink_structure' );

        /* Get the post type. */
        $post_type = get_post_type($post_id);
        $post_type_object = get_post_type_object($post_type);

        /*
         * WAGA MOD: Display the archive page before the categories
         */

        /* If there's an archive page, add it to the trail. */
        if (!empty($post_type_object->has_archive)) {
            /* Add support for a non-standard label of 'archive_title' (special use case). */
            $label = !empty($post_type_object->labels->archive_title) ? $post_type_object->labels->archive_title : $post_type_object->labels->name;
            $this->items[] = '<a href="' . get_post_type_archive_link($post_type) . '">' . $label . '</a>';
        }

        /* If this is the 'post' post type, get the rewrite front items and map the rewrite tags. */
        if ('post' === $post_type) {
            /* Add $wp_rewrite->front to the trail. */
            $this->do_rewrite_front_items();
            /* Map the rewrite tags. */
            $this->map_rewrite_tags( $post_id, $permalink_structure );
        } /* If the post type has rewrite rules. */
        elseif (false !== $post_type_object->rewrite) {
            /* Map rewrite tags */
            $this->map_rewrite_tags( $post_id, $permalink_structure );
            /* If 'with_front' is true, add $wp_rewrite->front to the trail. */
            if ($post_type_object->rewrite['with_front'])
                $this->do_rewrite_front_items();
            /* If there's a path, check for parents. */
            if (!empty($post_type_object->rewrite['slug']))
                $this->do_path_parents($post_type_object->rewrite['slug']);
        }
    }

    /**
     * Turns %tag% from permalink structures into usable links for the breadcrumb trail.  This feels kind of
     * hackish for now because we're checking for specific %tag% examples and only doing it for the 'post'
     * post type.  In the future, maybe it'll handle a wider variety of possibilities, especially for custom post
     * types.
     *
     * @since  0.6.0
     * @access public
     * @param  int $post_id ID of the post whose parents we want.
     * @param  string $path Path of a potential parent page.
     * @param  array $args Mixed arguments for the menu.
     * @return array
     */
    public function map_rewrite_tags($post_id, $path) {

        /* Get the post based on the post ID. */
        $post = get_post($post_id);

        /* If no post is returned, an error is returned, or the post does not have a 'post' post type, return. */
        if (empty($post) || is_wp_error($post))
            return $trail;

        /* Trim '/' from both sides of the $path. */
        $path = trim($path, '/');

        /* Split the $path into an array of strings. */
        $matches = explode('/', $path);

        /* If matches are found for the path. */
        if (is_array($matches)) {

            /* Loop through each of the matches, adding each to the $trail array. */
            foreach ($matches as $match) {

                /* Trim any '/' from the $match. */
                $tag = trim($match, '/');

                /* If using the %year% tag, add a link to the yearly archive. */
                if ('%year%' == $tag)
                    $this->items[] = '<a href="' . get_year_link(get_the_time('Y', $post_id)) . '">' . sprintf($this->args['labels']['archive_year'], get_the_time(_x('Y', 'yearly archives date format', 'breadcrumb-trail'))) . '</a>';

                /* If using the %monthnum% tag, add a link to the monthly archive. */
                elseif ('%monthnum%' == $tag)
                    $this->items[] = '<a href="' . get_month_link(get_the_time('Y', $post_id), get_the_time('m', $post_id)) . '">' . sprintf($this->args['labels']['archive_month'], get_the_time(_x('F', 'monthly archives date format', 'breadcrumb-trail'))) . '</a>';

                /* If using the %day% tag, add a link to the daily archive. */
                elseif ('%day%' == $tag)
                    $this->items[] = '<a href="' . get_day_link(get_the_time('Y', $post_id), get_the_time('m', $post_id), get_the_time('d', $post_id)) . '">' . sprintf($this->args['labels']['archive_day'], get_the_time(_x('j', 'daily archives date format', 'breadcrumb-trail'))) . '</a>';

                /* If using the %author% tag, add a link to the post author archive. */
                elseif ('%author%' == $tag)
                    $this->items[] = '<a href="' . get_author_posts_url($post->post_author) . '" title="' . esc_attr(get_the_author_meta('display_name', $post->post_author)) . '">' . get_the_author_meta('display_name', $post->post_author) . '</a>';

                /* If using the %category% tag, add a link to the first category archive to match permalinks. */
                elseif ('%category%' == $tag) {

                    /* Force override terms in this post type. */
                    $this->args['post_taxonomy'][$post->post_type] = false;

                    /* Get the post categories. */
                    if('post' == $post->post_type){
                        $terms = get_the_category($post_id);
                    }else{ /* WAGA MOD */
	                    $post_type_object = get_post_type_object($post->post_type);
	                    $post_type_taxonomies = get_object_taxonomies($post->post_type);
	                    //Reorder the taxonomies with the hierarchical one at the top
	                    if(is_array($post_type_taxonomies) && !empty($post_type_taxonomies)){
		                    usort($post_type_taxonomies,function($a,$b){
			                    if($a == $b) return 0;
			                    $a_tax = get_taxonomy($a);
			                    $b_tax = get_taxonomy($b);
			                    if($a_tax->hierarchical && $b_tax->hierarchical) return 0;
			                    if(!$a_tax->hierarchical && !$b_tax->hierarchical) return 0;
			                    if($a_tax->hierarchical && !$b_tax->hierarchical) return -1;
			                    if(!$a_tax->hierarchical && $b_tax->hierarchical) return 1;
		                    });
		                    $terms = get_the_terms($post_id,$post_type_taxonomies[0]);
	                    }else{
		                    $terms = false;
	                    }
                    }

                    //Check that categories were returned.
                    /*if ($terms) {
                        //Sort the terms by ID and get the first category
                        usort($terms, '_usort_terms_by_ID');
                        if('post' == $post->post_type){
                            $taxonomy_name = "category";
                        }else{
                            $taxonomy_name = $terms[0]->taxonomy;
                        }
                        $term = get_term($terms[0], $taxonomy_name);

                        //If the category has a parent, add the hierarchy to the trail.
                        if ($term->parent > 0){
                            $this->do_term_parents($term->parent, $taxonomy_name);
                        }

                        //Add the category archive link to the trail.
                        $this->items[] = '<a href="' . get_term_link($term, $taxonomy_name) . '" title="' . esc_attr($term->name) . '">' . $term->name . '</a>';
                    }*/
	                //BETA [ WAGA MOD ]:
	                $added_terms = array();
	                if ($terms) {
		                /* Sort the terms by ID and get the first category. */
		                usort($terms, '_usort_terms_by_ID');
		                /* Add the category archive link to the trail. */
		                foreach($terms as $t){
			                if('post' == $post->post_type){
				                $taxonomy_name = "category";
			                }else{
				                $taxonomy_name = $t->taxonomy;
			                }
			                /* If the category has a parent, add the hierarchy to the trail. */
			                if ($t->parent > 0 && !in_array($t->parent,$added_terms)){
				                $this->do_term_parents($t->parent, $taxonomy_name);
			                }
			                $this->items[] = '<a href="' . get_term_link($t, $taxonomy_name) . '" title="' . esc_attr($t->name) . '">' . $t->name . '</a>';
			                $added_terms[] = $t->term_id;
		                }
	                }
                }
            }
        }
    }
}