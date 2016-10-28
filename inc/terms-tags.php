<?php

namespace Waboot\template_tags;
use WBF\components\utils\Utilities;

/**
 * Retrieve a post's terms as a list with specified format and in an hierarchical order
 *
 * @param int $id Post ID.
 * @param string $taxonomy Taxonomy name.
 * @param string $before Optional. Before list.
 * @param string $sep Optional. Separate items using this.
 * @param string $after Optional. After list.
 *
 * @use WBF\components\utils\Utilities::get_post_terms_hierarchical()
 *
 * @return string A list of terms on success, an empty string in case of failure or when no terms has been found.
 */
function get_the_terms_list_hierarchical( $id, $taxonomy, $before = '', $sep = '', $after = '', $linked = true ) {
	$terms = Utilities::get_post_terms_hierarchical($id, $taxonomy);

	if( is_wp_error($terms) || empty($terms) ){
		return "";
	}

	$links = array();

	foreach ( $terms as $term ) {
		if($term instanceof \stdClass){
			$term = get_term($term->term_id,$taxonomy); //Restore the WP_Term
		}
		$link = get_term_link( $term, $taxonomy );
		if ( is_wp_error( $link ) ) {
			return $link;
		}
        if ($linked) {
            $links[] = '<a href="' . esc_url( $link ) . '" rel="tag">' . $term->name . '</a>';
        }else{
            $links[] = $term->name;
        }
	}

	/**
	 * Filter the term links for a given taxonomy.
	 *
	 * The dynamic portion of the filter name, `$taxonomy`, refers
	 * to the taxonomy slug.
	 *
	 * @param array $links An array of term links.
	 */
	$term_links = apply_filters( "term_links-$taxonomy", $links );

	return $before . join( $sep, $term_links ) . $after;
}


/**
 * Get the post categories ordered by ID. If the post is a custom post type it retrieve the specified $taxonomy terms or the first registered taxonomy
 *
 * @param null $post_id
 * @param null $taxonomy the taxonomy to retrieve if the POST is a custom post type
 * @param bool $ids_only retrieve only the ID of the categories
 *
 * @return array
 */
function get_the_category($post_id = null, $taxonomy = null, $ids_only = false){
	if(!isset($post_id)){
		global $post;
		$post_id = $post->ID;
	}else{
		$post = get_post($post_id);
	}

	$categories = [];

	if(get_post_type($post_id) == "post"){
		$terms = \get_the_category($post_id);
		if($ids_only){
			foreach($terms as $id => $term){
				$categories[] = $id;
			}
		}else{
			$categories = $terms;
		}
	}else{
		if(!isset($taxonomy)){
			$terms = get_the_terms($post_id,get_first_taxonomy($post_id));
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
		if(isset($categories) && is_array($categories)){
			sort($categories,SORT_NUMERIC);
		}
	}else{
		if(isset($categories) && is_array($categories)){
			usort($categories,__NAMESPACE__."\\sort_categories_by_id");
		}
	}

	return $categories;
}

/**
 * Get the top level categories
 * @param null $taxonomy
 * @return array
 */
function get_top_categories($taxonomy = null){
	if(!$taxonomy){
		$cats = \get_categories();
	}else{
		$cats = \get_categories([
			'taxonomy' => $taxonomy
		]);
	}

	$top_cat_obj = array();

	foreach($cats as $cat) {
		if ($cat->parent == 0) {
			$top_cat_obj[] = $cat;
		}
	}

	return $top_cat_obj;
}


/**
 * Gets top level category of the current or specified post
 * @param string $return_value "id" or "slug". If empty the category object is returned.
 * @return string|object
 */
function get_top_category($return_value = "", $post_id = null) {
	if(!$post_id)
		$cats = get_the_category(); // category object
	else
		$cats = get_the_category($post_id); // category object

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

/**
 * Get the first registered taxonomy of a custom post type
 * @param null $post_id
 * @return string
 */
function get_first_taxonomy($post_id = null){
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

/**
 * Sort the categories of a post by ID (ASC)
 * @param $a
 * @param $b
 * @return int
 */
function sort_categories_by_id($a,$b){
	if((int)$a->term_id == (int)$b->term_id) return 0;
	return (int)$a->term_id < (int)$b->term_id ? -1 : 1;
}