<?php

if(!function_exists( 'wbft_get_post_terms_hierarchical' )):
	/**
	 * Get a list of term in hierarchical order, with parents before their children.
	 * The functions automatically completes the list with che missing parents (they will be labeled with "not_assigned = true" property)..
	 *
	 * @param int $post_id the $post_id param for wp_get_post_terms()
	 * @param string $taxonomy the $taxonomy param for wp_get_post_terms()
	 * @param array $args the $args param for wp_get_post_terms()
	 * @param boolean $flatten TRUE to flatten the hierarchical array down to one level. Children will be inserted after their parents;
	 *                          FALSE to retrieve a multidimensional array in which the first level is composed by top-level parents. Children will be appended into "children" property of each parent term.
	 *
	 * @param bool|false $convert_to_wp_term is true, the resulting list flatted list will be converted into WP_Term list
	 *
	 * @return array
	 */
	function wbft_get_post_terms_hierarchical($post_id, $taxonomy, $args = [], $flatten = true, $convert_to_wp_term = false){
		static $cache;

		if(isset($cache[$taxonomy][$post_id]) && is_array($cache[$taxonomy][$post_id])) return $cache[$taxonomy][$post_id];

		$args = wp_parse_args($args,[
			'orderby' => 'parent'
		]);
		$args['orderby'] = 'parent'; //we need to force this
		$terms = wp_get_post_terms( $post_id, $taxonomy, $args);

		/**
		 * Convert WP_Term to old-fashion stdClass
		 *
		 * @param $instance
		 *
		 * @return \stdClass
		 */
		$WPTermToStdClass = function($instance) {
			$std = new \stdClass();
			$std->term_id = $instance->term_id;
			$std->name = $instance->name;
			$std->slug = $instance->slug;
			$std->term_group = $instance->term_group;
			$std->term_taxonomy_id = $instance->term_taxonomy_id;
			$std->description = $instance->description;
			$std->parent = $instance->parent;
			$std->count = $instance->count;
			$std->filter = $instance->filter;
			return $std;
		};

		/**
		 * Insert a mixed at specified position into input $array
		 *
		 * @param array $input
		 * @param $position
		 * @param $insertion
		 *
		 * @return array
		 */
		$array_insert = function(Array $input,$position,$insertion){
			$insertion = array($insertion);
			$first_array = array_splice ($input, 0, $position);
			$output = array_merge ($first_array, $insertion, $input);
			return $output;
		};

		/**
		 * Insert $insertion after the element with $term->id == $insert_at_term_id of array $input
		 * @param array $input
		 * @param int   $insert_at_term_id
		 * @param array $insertion
		 *
		 * @return array|bool
		 */
		$children_insert = function(Array $input,$insert_at_term_id,$insertion) use(&$children_insert,$WPTermToStdClass){
			$output = $input;

			foreach($output as $k => $t){
				if($t instanceof \WP_Term){
					$output[$k] = $WPTermToStdClass($t);
				}
			}

			foreach($input as $k => $v){
				if($v->term_id == $insert_at_term_id){ //We found the parent
					if(!isset($output[$k]->childeren) || !is_integer(array_search($insertion,$output[$k]->children))){
						$output[$k]->children[] = $insertion;
						return $output;
					}
				}elseif(isset($v->children) && count($v->children) >= 1){ //Search in parent children
					$new_children = $children_insert($v->children,$insert_at_term_id,$insertion);
					if(is_array($new_children)){
						$output[$k]->children = $new_children;
						return $output;
					}
				}
			}
			return false; //We haven't found any point of insertion
		};

		/**
		 * Complete the terms list with missing parents. Missing parents will be labeled with "not_assigned = true"
		 *
		 * @param $terms
		 *
		 * @return mixed
		 * @internal param $p
		 * @internal param $t
		 *
		 */
		$complete_missing_terms = function($terms) use($taxonomy){
			/**
			 * Add the parent pf $child into the $terms_list (if not present)
			 * @param $child
			 * @param $terms_list
			 *
			 * @return array
			 */
			$add_parent = function($child,$terms_list) use(&$add_parent,$taxonomy){
				$parent = get_term($child->parent,$taxonomy);
				$terms_list_as_array = json_decode(json_encode($terms_list),true);
				$found = wbft_associative_array_search($terms_list_as_array,"term_id",$parent->term_id);
				if(empty($found)){
					$parent->not_assigned = true; //Set a flag to tell that this parent is added programmatically and not by the user
					$terms_list[] = $parent;
				}
				if($parent->parent != 0){
					return $add_parent($parent,$terms_list);
				}else{
					return $terms_list;
				}
			};
			$new_term_list = $terms;
			foreach($terms as $t){
				if($t->parent != 0){
					$new_term_list = $add_parent($t,$new_term_list);
				}
			}
			return $new_term_list;
		};

		/**
		 * Build term hierarchy
		 * @param array $cats the terms to reorder
		 *
		 * @return array
		 */
		$build_hierarchy = function(Array $cats) use ($array_insert, $children_insert){
			$cats_count = count($cats); //meow! How many terms have we?
			$result = [];

			if($cats_count < 1){
				return $result;
			}
			elseif($cats_count == 1){
				return $cats;
			}

			//Populate all the parent
			foreach ($cats as $i => $cat) {
				if($cat->parent == 0){
					$result[] = $cat;
					unset($cats[$i]); //remove the parent from the list
				}
			}

			$inserted_cats = count($result); //Count the items inserted at this point
			$cats = array_values($cats); //resort the array

			if($inserted_cats == 0){
				return []; //Here we return if no parents are present within the terms
			}

			//Populate with children
			while(count($cats) > 0){ //Go on until we reached have some terms to order
				foreach ($cats as $i => $cat) {
					$parent_term_id = $cat->parent;
					$r = $children_insert($result,$parent_term_id,$cat);
					if(is_array($r)){ //We found a valid parent, and $r is the new array with $cat appended into parent
						$result = $r;
						unset($cats[$i]);
						$cats = array_values($cats); //resort the array
						break; //and break!
					}
				}
			}

			return $result;
		};

		$flatten_terms_hierarchy = function($term_hierarchy) use($convert_to_wp_term){
			$output_terms = [];
			$flat = function($term_hierarchy) use (&$output_terms,&$flat,$convert_to_wp_term){
				foreach($term_hierarchy as $k => $t){
					$output_terms[] = $convert_to_wp_term ? \WP_Term::get_instance($t->term_id,$t->taxonomy) : $t;
					if(isset($t->children) && $t->children >= 1){
						$flat($t->children);
					}
				}
			};
			$flat($term_hierarchy);

			foreach($output_terms as $k=>$v){
				if(isset($v->children)){
					unset($output_terms[$k]->children);
				}
			}

			return $output_terms;
		};

		if(!is_array($terms) || empty($terms)) return [];

		foreach($terms as $k => $t){
			if($t instanceof \WP_Term){
				$terms[$k] = $WPTermToStdClass($t);
			}
		}

		$terms = $complete_missing_terms($terms);
		$h = $build_hierarchy($terms);

		$sortedTerms = $flatten ? $flatten_terms_hierarchy($h) : $h; //Extract the children

		$cache[$taxonomy][$post_id] = $sortedTerms;

		return $sortedTerms;
	}
endif;

if(!function_exists( 'wbft_get_the_terms_list_hierarchical' )):
	/**
	 * Retrieve a post's terms as a list with specified format and in an hierarchical order
	 *
	 * @param int $id Post ID.
	 * @param string $taxonomy Taxonomy name.
	 * @param string $before Optional. Before list.
	 * @param string $sep Optional. Separate items using this.
	 * @param string $after Optional. After list.
	 *
	 * @use wbft_get_post_terms_hierarchical
	 *
	 * @return string|bool|WP_Error A list of terms on success, false if there are no terms, WP_Error on failure.
	 */
	function wbft_get_the_terms_list_hierarchical( $id, $taxonomy, $before = '', $sep = '', $after = '' ) {
		$terms = wbft_get_post_terms_hierarchical( $id, $taxonomy );

		if ( is_wp_error( $terms ) )
			return $terms;

		if ( empty( $terms ) )
			return false;

		$links = array();

		foreach ( $terms as $term ) {
			$link = get_term_link( $term, $taxonomy );
			if ( is_wp_error( $link ) ) {
				return $link;
			}
			$links[] = '<a href="' . esc_url( $link ) . '" rel="tag">' . $term->name . '</a>';
		}

		/**
		 * Filter the term links for a given taxonomy.
		 *
		 * The dynamic portion of the filter name, `$taxonomy`, refers
		 * to the taxonomy slug.
		 *
		 * @since 2.5.0
		 *
		 * @param array $links An array of term links.
		 */
		$term_links = apply_filters( "term_links-$taxonomy", $links );

		return $before . join( $sep, $term_links ) . $after;
	}
endif;

if(!function_exists( 'wbft_get_the_category')):
	/**
	 * Get the post categories ordered by ID. If the post is a custom post type it retrieve the specified $taxonomy terms or the first registered taxonomy
	 *
	 * @param null $post_id
	 * @param null $taxonomy the taxonomy to retrieve if the POST is a custom post type
	 * @param bool $ids_only retrieve only the ID of the categories
	 *
	 * @return array
	 */
	function wbft_get_the_category($post_id = null, $taxonomy = null, $ids_only = false){
		if(!isset($post_id)){
			global $post;
			$post_id = $post->ID;
		}else{
			$post = get_post($post_id);
		}

		if(get_post_type($post_id) == "post"){
			$terms = get_the_category($post_id);
			if($ids_only){
				foreach($terms as $id => $term){
					$categories[] = $id;
				}
			}else{
				$categories = $terms;
			}
		}else{
			if(!isset($taxonomy)){
				$terms = get_the_terms($post_id,waboot_get_first_taxonomy($post_id));
				if($ids_only){
					foreach($terms as $id => $term){
						$categories[] = $id;
					}
				}else{
					$categories = $terms;
				}
			}else{
				if($ids_only){
					$categories = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
				}else{
					$categories = wp_get_object_terms( $post_id, $taxonomy);
				}
			}
		}

		if($ids_only){
			if(isset($categories) && is_array($categories))
				sort($categories,SORT_NUMERIC);
		}else{
			if(isset($categories) && is_array($categories))
				usort($categories,"waboot_sort_categories_by_id");
		}

		return $categories;
	}
endif;

if(!function_exists( 'wbft_get_top_categories')):
	/**
	 * Get the top level categories
	 * @param null $taxonomy
	 * @return array
	 */
	function wbft_get_top_categories($taxonomy = null){
		if(!$taxonomy){
			$cats = get_categories();
		}else{
			$cats = get_categories(array(
				'taxonomy' => $taxonomy
			));
		}

		$top_cat_obj = array();

		foreach($cats as $cat) {
			if ($cat->parent == 0) {
				$top_cat_obj[] = $cat;
			}
		}

		return $top_cat_obj;
	}
endif;

if(!function_exists( 'wbft_get_top_category')):
	/**
	 * Gets top level category of the current or specified post
	 * @param string $return_value "id" or "slug". If empty the category object is returned.
	 * @return string|object
	 */
	function wbft_get_top_category($return_value = "", $post_id = null) {
		if(!$post_id)
			$cats = waboot_get_the_category(); // category object
		else
			$cats = waboot_get_the_category($post_id); // category object

		if(!$cats) return false;

		$top_cat_obj = array();

		foreach($cats as $cat) {
			if ($cat->parent == 0) {
				$top_cat_obj[] = $cat;
			}
		}

		if(!isset($top_cat_obj[0])){
			$top_cat_obj = $cats[0];
		}else{
			$top_cat_obj = $top_cat_obj[0];
		}

		if($return_value == ""){
			return $top_cat_obj;
		}else{
			switch($return_value){
				case "id":
					return $top_cat_obj->term_id;
					break;
				case "slug":
					return $top_cat_obj->slug;
					break;
				default:
					return $top_cat_obj;
					break;
			}
		}
	}
endif;

if(!function_exists( 'wbft_get_first_taxonomy')):
	/**
	 * Get the first registered taxonomy of a custom post type
	 * @param null $post_id
	 * @return string
	 */
	function wbft_get_first_taxonomy($post_id = null){
		if(!isset($post_id)){
			global $post;
			$post_id = $post->ID;
		}else{
			$post = get_post($post_id);
		}

		if(get_post_type($post_id) == "post"){
			return 'category';
		}else{
			$post_type_taxonomies = get_object_taxonomies($post->post_type);
			return $post_type_taxonomies[0];
		}
	}
endif;

if(!function_exists( 'wbft_sort_categories_by_id')):
	/**
	 * Sort the categories of a post by ID (ASC)
	 * @param $a
	 * @param $b
	 * @return int
	 */
	function wbft_sort_categories_by_id($a,$b){
		if((int)$a->term_id == (int)$b->term_id) return 0;
		return (int)$a->term_id < (int)$b->term_id ? -1 : 1;
	}
endif;