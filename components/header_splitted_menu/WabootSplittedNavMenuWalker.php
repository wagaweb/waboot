<?php

class WabootSplittedNavMenuWalker extends \WBF\components\navwalker\Bootstrap_NavWalker {

	private $count = 0;
	private $split_position;

	function __construct($position, $menu_name) {

		$this->split_position = ($position == "0" || empty($position) )
			? floor(count(wp_get_nav_menu_items($menu_name))/2)
			: intval($position) ;
	}

	function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {

		/*
		 * here start the logic that split the menu in two and insert the log inbetween
		 */

		if ($depth==0 && $this->count == 0){

			//open the first splitted ul
			$cb_args = array_merge( [&$output], $args);
			call_user_func_array([&$this, 'start_splitted_ul'], $cb_args);

		} elseif ($depth==0 && $this->count == ($this->split_position) ) {

			// close the ul, insert the logo and opent he new ul
			$cb_args = array_merge( [&$output], $args);
			call_user_func_array([&$this, 'insert_logo'], $cb_args);
		}

		// here ends the logic

		//v($element);
		if ( !$element )
			return;

		$id_field = $this->db_fields['id'];

		//display this element
		if ( is_array( $args[0] ) )
			$args[0]['has_children'] = ! empty( $children_elements[$element->$id_field] );
		else if ( is_object( $args[0] ) )
			$args[0]->has_children = ! empty( $children_elements[$element->$id_field] );
		$cb_args = array_merge( array(&$output, $element, $depth), $args);
		call_user_func_array(array(&$this, 'start_el'), $cb_args);

		$id = $element->$id_field;

		// descend only when the depth is right and there are childrens for this element
		if ( ($max_depth == 0 || $max_depth > $depth+1 ) && isset( $children_elements[$id]) ) {

			foreach( $children_elements[ $id ] as $child ){

				if ( !isset($newlevel) ) {
					$newlevel = true;
					//start the child delimiter
					$cb_args = array_merge( array(&$output, $depth), $args);
					call_user_func_array(array(&$this, 'start_lvl'), $cb_args);
				}
				$this->display_element( $child, $children_elements, $max_depth, $depth + 1, $args, $output );
			}
			unset( $children_elements[ $id ] );
		}

		if ( isset($newlevel) && $newlevel ){
			//end the child delimiter
			$cb_args = array_merge( array(&$output, $depth), $args);
			call_user_func_array(array(&$this, 'end_lvl'), $cb_args);
		}

		//end this element
		$cb_args = array_merge( array(&$output, $element, $depth), $args);
		call_user_func_array(array(&$this, 'end_el'), $cb_args);

		$this->count++;

	}

	function start_splitted_ul(&$output, $args) {
		$output .= "\n<ul class='nav navbar-nav navbar-split-left'>\n";
	}

	function insert_logo(&$output, $args) {

		if ( \Waboot\template_tags\get_desktop_logo() != "" ) {
			$logo_menu_list = '<img src="' . \Waboot\template_tags\get_desktop_logo() . '"/>';
		} else {
			$logo_menu_list = get_bloginfo("name");
		}

		$output .= "</ul>";
		$output .= "<div class='logonav hidden-sm hidden-xs'><a href='" . get_bloginfo('url') . "'>" . $logo_menu_list . "</a></div>";
		$output .= "<ul class='nav navbar-nav navbar-split-right'>";
	}
}