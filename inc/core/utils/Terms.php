<?php
namespace Waboot\inc\core\utils;

trait Terms {
    /**
     * Add a new term to the database if it does not already exist.
     *
     * @param string $tagName The term name.
     * @param string $taxonomy Optional. The taxonomy within which to create the term. Default 'post_tag'.
     * @return array|\WP_Error If array: [term_id => xx, term_taxonomy_id => yy]
     */
    public static function createTerm(string $tagName, string $taxonomy = 'post_tag')
    {
        if(function_exists('\wp_create_term')){
            return wp_create_term($tagName,$taxonomy);
        }
        $retriedArray = term_exists($tagName, $taxonomy);
        if($retriedArray){
            return $retriedArray;
        }
        return wp_insert_term($tagName, $taxonomy);
    }

    /**
     * @param string $taxonomy
     * @param string $name
     * @throws \RuntimeException
     * @return \WP_Term
     */
    public static function getOrCreateTerm(string $taxonomy, string $name): \WP_Term
    {
        $term = get_term_by('name', $name, $taxonomy);
        if(empty($term)) {
            $term = self::createTerm($name, $taxonomy);
            if(is_wp_error($term)){
                throw new \RuntimeException('getOrCreateTerm("'.$name.'","'.$taxonomy.'"): '.$term->get_error_message());
            }
            $term = get_term_by('term_taxonomy_id', $term['term_taxonomy_id']);
        }
        return $term;
    }

    /**
     * Convert WP_Term to old-fashion stdClass
     *
     * @param $instance
     *
     * @return \stdClass
     */
    static function wpTermToStdClass(\WP_Term $instance){
        $std = new \stdClass();
        $std->term_id = $instance->term_id;
        $std->name = $instance->name;
        $std->slug = $instance->slug;
        $std->taxonomy = $instance->taxonomy;
        $std->term_group = $instance->term_group;
        $std->term_taxonomy_id = $instance->term_taxonomy_id;
        $std->description = $instance->description;
        $std->parent = $instance->parent;
        $std->count = $instance->count;
        $std->filter = $instance->filter;
        return $std;
    }

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
	static function getPostTermsHierarchical($post_id, $taxonomy, $args = [], $flatten = true, $convert_to_wp_term = false){
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
		$WPTermToStdClass = function(\WP_Term $instance) {
			$std = new \stdClass();
			$std->term_id = $instance->term_id;
			$std->name = $instance->name;
			$std->slug = $instance->slug;
			$std->taxonomy = $instance->taxonomy;
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
				if(!$parent instanceof \WP_Term){
					throw new \RuntimeException('Term with id: '.$child->parent.' not found in taxonomy: '.$taxonomy);
				}
				$terms_list_as_array = json_decode(json_encode($terms_list),true);
				$found = Utilities::associativeArraySearch($terms_list_as_array, 'term_id',$parent->term_id);
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
					try{
						$new_term_list = $add_parent($t,$new_term_list);
					}catch (\RuntimeException $e){
						continue;
					}
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
					unset($cats[$i]);
					break; //and break!
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
				$convertedTerm = $WPTermToStdClass($t);
				if(property_exists($convertedTerm,'term_id') && property_exists($convertedTerm,'parent')){
					$terms[$k] = $WPTermToStdClass($t);
				}
			}
		}

		$terms = array_filter($terms, static function($term){
			return is_object($term) && property_exists($term,'term_id');
		});
		$terms = $complete_missing_terms($terms);
		$h = $build_hierarchy($terms);

		$sortedTerms = $flatten ? $flatten_terms_hierarchy($h) : $h; //Extract the children

		$cache[$taxonomy][$post_id] = $sortedTerms;

		return $sortedTerms;
	}

    /**
     * Returns an hierarchical tree of $taxonomy terms
     *
     * @param $taxonomy
     *
     * @return array
     */
    static function getTaxTermsHierarchical($taxonomy){
        $terms = get_terms(['taxonomy' => $taxonomy]);
        if(is_array($terms) && !empty($terms)){
            /**
             * generate array of arrays where each topmost key is a parent id.
             * the function place each child under the correspondent parent id.
             * Topmost parents have parent id equals to 0
             */
            $organized_terms = [];
            foreach ($terms as $term) {
                $organized_terms[$term->parent][$term->term_id] = $term;
            }

            /**
             * Place each array of children under the voice 'children' of the correct parent term
             */
            foreach ( $organized_terms as $parent_id => $children ) {
                foreach ( $children as $id => $child ) {
                    if (array_key_exists($id,$organized_terms)) {
                        try {
                            if (is_object($child)) {
                                $child->children = $organized_terms[$id];
                            } elseif (is_array($child)) {
                                $child['children'] = $organized_terms[$id];
                            }

                        } catch (Exception $e) {
                            echo 'Caught exception: ',  $e->getMessage(), "\n";
                        }
                    }
                }
            }

            // now return only the topmost parent terms array
            return $organized_terms[0];
        }
        return [];
    }

    /**
     * Retrieve the taxonomies of $object, and then retrieve the hierarchical tree of terms of every taxonomy
     *
     * @param $object
     *
     * @return array
     */
    static function getObjectTaxonomiesTermsHierarchical($object){
        // get wp taxonomies as unsorted array
        $taxonomies = get_object_taxonomies($object);
        $organized_terms = [];
        foreach ( $taxonomies as $taxomomy ) {
            $organized_terms[] = self::getTaxTermsHierarchical($taxomomy);
        }
        return $organized_terms;
    }

    /**
     * @param \WP_Term $term
     *
     * @return string|false
     */
    public static function getPostTypeByTerm(\WP_Term $term){
        $taxonomy = $term->taxonomy;
        return self::getPostTypeByTaxonomy($taxonomy);
    }

    /**
     * @param \WP_Taxonomy|string $taxonomy
     *
     * @return string|false
     */
    public static function getPostTypeByTaxonomy($taxonomy){
        global $wp_taxonomies;
        if($taxonomy instanceof \WP_Taxonomy){
            $taxonomy = $taxonomy->name;
        }

        if(isset($taxonomy) && isset($wp_taxonomies[$taxonomy])){
            $tax_obj = $wp_taxonomies[$taxonomy];
            if(is_array($tax_obj->object_type) && !empty($tax_obj->object_type)){
                return $tax_obj->object_type[0];
            }
        }
        return false;
    }

    /**
     * Returns current taxonomy name or FALSE
     *
     * @return false|string
     */
    public static function getCurrentTaxonomy(){
        $o = get_queried_object();
        if($o instanceof \WP_Term){
            return $o->taxonomy;
        }elseif($o instanceof \WP_Taxonomy){
            return $o->name;
        }
        return false;
    }

    /**
     * Assign terms to an object, by querying the database directly
     *
     * @param $object_id
     * @param array $terms
     *
     * @return int
     */
    public static function dbSetObjectTerms($object_id, $terms = []){
        global $wpdb;
        $res = 0;
        if(!\is_array($terms) || count($terms) <= 0){
            return $res;
        }
        $wpdb->delete($wpdb->term_relationships,[
            'object_id' => $object_id
        ]);
        foreach ($terms as $termId){
            $res += (int) $wpdb->insert($wpdb->term_relationships,[
                'object_id' => $object_id,
                'term_taxonomy_id' => $termId,
                'term_order' => 0
            ]);
        }
        return $res;
    }
}