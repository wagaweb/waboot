<?php

namespace Waboot\inc\widgets;

use WBF\components\mvc\HTMLView;

class RecentPosts extends \WP_Widget{

	var $widget_slug = "wbrw";

	function __construct(){
		parent::__construct(
			"waboot_recent_posts_widget",
			__( 'Waboot Recent Posts Widget', "waboot" ),
			[
				'classname'   => 'wbrw',
				'description' => __( 'A widget to show the recents posts', "waboot" )
			],
			[
				'width'  => 750,
				'height' => 350
			]
		);

		add_action("wp_ajax_{$this->widget_slug}_get_terms",[$this,'get_terms']);
	}

	function widget($args,$instance){
		echo $args['before_widget']; ?>

		<h3 class="widget-title"><?php echo $instance['title']; ?></h3>

		<?php

		/**
		 * Search $instance terms into selected post for specified taxonomies type (hierarchical or not)
		 * @param        $p
		 * @param string $tax_type
		 *
		 * @return bool
		 */
		$search_terms_into = function(&$p,$post_type,$tax_type = "categories") use(&$instance){
			$hierarchical = $tax_type == "categories" ? true : false;
			$taxs = $this->get_taxonomies_type($post_type,$hierarchical);
			$terms = wp_get_post_terms($p->ID,$taxs);
			$found_flag = false;
			$search_target = $tax_type == "categories" ? $instance['cat'][$post_type] : $instance['tag'][$post_type];
			foreach($terms as $t){
				if(in_array($t->term_id,$search_target)){
					$found_flag = true;
					break;
				}
			}
			return $found_flag;
		};

		/**
		 * Callback functions for wbf_get_posts(). Applies categories and tag filters to retreived posts.
		 * @param $p
		 *
		 * @use $search_terms_into
		 * @return bool
		 */
		$filter_posts = function($p) use(&$instance,$search_terms_into){
			$maybe_valid = true;
			$ptype = get_post_type($p->ID);
			//Check post type against instance settings
			if(!in_array($ptype,$instance['post_type'])){
				$maybe_valid = false;
			}
			//Check all hierarchical terms of the post against instance settings
			if(isset($instance['cat']) && !empty($instance['cat']) && isset($instance['cat'][$ptype])){
				$maybe_valid = empty($instance['cat'][$ptype]) ? false : $search_terms_into($p,$ptype,"categories");
			}
			//Check all non-hierarchical terms of the post against instance settings
			if(isset($instance['tag']) && !empty($instance['tag']) && isset($instance['tag'][$ptype])){
				$maybe_valid = empty($instance['tag'][$ptype]) ? false : $search_terms_into($p,$ptype,"tags");
			}
			return $maybe_valid;
		};

		$post_ids = array_keys(wbf_get_posts($filter_posts,[
			'post_type' => $instance['post_type'],
			'ignore_sticky_posts' => $instance['ignore_sticky']
		]));
		$q = new \WP_Query([
			'post_type' => $instance['post_type'],
			'post__in' => $post_ids,
			'posts_per_page' => $instance['limit'],
			'order' => $instance['order'],
			'orderby' => $instance['orderby']
		]);

		if($q->have_posts()){
			while($q->have_posts()){
				$q->the_post();
				$this->post_tpl($instance);
			}
			wp_reset_postdata();
		}else{
			$this->empty_posts_tpl();
		}
		wp_reset_query();

		echo $args['after_widget'];
	}

	function form($instance){
		$instance = wp_parse_args( (array) $instance, $this->get_defaults() );
		$post_types = get_post_types(array('public' => true), 'objects');
		$post_status = get_available_post_statuses();
		$v = new HTMLView("templates/admin/widgets/recents-posts/form.php");
		$v->clean()->display([
			'instance' => $instance,
			'post_types' => $post_types,
			'post_status' => $post_status,
			'widget' => $this
		]);
	}

	function update($new_instance, $old_instance){
		// Validate post_type submissions
		$name = get_post_types( array( 'public' => true ), 'names' );
		$types = array();
		foreach( $new_instance['post_type'] as $type ) {
			if ( in_array( $type, $name ) ) {
				$types[] = $type;
			}
		}
		if ( empty( $types ) ) {
			$types[] = 'post';
		}

		$instance                     = $old_instance;
		$instance['title']            = $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		//$instance['title_url']        = esc_url( $new_instance['title_url'] );

		$instance['ignore_sticky']    = isset( $new_instance['ignore_sticky'] ) ? (bool) $new_instance['ignore_sticky'] : 0;
		$instance['limit']            = (int)( $new_instance['limit'] );
		//$instance['offset']           = (int)( $new_instance['offset'] );
		$instance['order']            = $new_instance['order'];
		$instance['orderby']          = $new_instance['orderby'];
		$instance['post_type']        = $types;
		$instance['post_status']      = esc_attr( $new_instance['post_status'] );
		$instance['cat']              = $new_instance['cat'];
		$instance['tag']              = $new_instance['tag'];
		//$instance['taxonomy']         = esc_attr( $new_instance['taxonomy'] );

		$instance['excerpt']          = isset( $new_instance['excerpt'] ) ? (bool) $new_instance['excerpt'] : false;
		$instance['excerpt_length']   = (int)( $new_instance['excerpt_length'] );
		$instance['date']             = isset( $new_instance['date'] ) ? (bool) $new_instance['date'] : false;
		$instance['date_relative']    = isset( $new_instance['date_relative'] ) ? (bool) $new_instance['date_relative'] : false;
		$instance['readmore']         = isset( $new_instance['readmore'] ) ? (bool) $new_instance['readmore'] : false;
		$instance['readmore_text']    = strip_tags( $new_instance['readmore_text'] );
		$instance['readmore_prefix']  = strip_tags( $new_instance['readmore_prefix'] );

		$instance['thumb']            = isset( $new_instance['thumb'] ) ? (bool) $new_instance['thumb'] : false;
		$instance['thumb_size']       = isset( $new_instance['thumb_size'] ) ? $new_instance['thumb_size'] : "thumbnail";
		//$instance['thumb_height']     = (int)( $new_instance['thumb_height'] );
		//$instance['thumb_width']      = (int)( $new_instance['thumb_width'] );
		//$instance['thumb_default']    = esc_url( $new_instance['thumb_default'] );
		//$instance['thumb_align']      = esc_attr( $new_instance['thumb_align'] );

		//$instance['styles_default']   = isset( $new_instance['styles_default'] ) ? (bool) $new_instance['styles_default'] : false;
		//$instance['cssID']            = sanitize_html_class( $new_instance['cssID'] );
		//$instance['css_class']        = sanitize_html_class( $new_instance['css_class'] );
		//$instance['css']              = $new_instance['css'];
		//$instance['before']           = stripslashes( $new_instance['before'] );
		//$instance['after']            = stripslashes( $new_instance['after'] );

		return $instance;
	}

	/**
	 * Get the terms to display into the form accordingly to specific widget instance values
	 * @param      $instance
	 * @param bool $hierarchical
	 *
	 * @return array
	 */
	function get_terms($instance, $hierarchical = true){
		$instance = wp_parse_args( (array) $instance, $this->get_defaults() );

		if(defined("DOING_AJAX") && DOING_AJAX && isset($_POST['states'])){
			$post_type = [];
			foreach($_POST['states'] as $s){
				$s['checked'] = (bool) $s['checked'];
				if($s['checked']){
					$post_type[] = $s['name'];
				}
			}
			$instance['post_type'] = $post_type;
			$hierarchical = (bool) $_POST['hierarchical'];
		}

		$result_terms = [];
		foreach($instance['post_type'] as $pt){
			$taxs = $this->get_taxonomies_type($pt,$hierarchical); //Get only taxonomies that are hierarchical or not accordingly to $hierarchical param
			$terms = get_terms($taxs); //Get all term objects
			$terms = array_map(function($term) use($pt){
				$term->registered_for_post_type = $pt; //Adds "registered_for_post_type" value to each term object, it will be used in form display
				return $term;
			},$terms);
			if($terms && is_array($terms) && !empty($terms)){
				$result_terms = array_merge($result_terms,$terms);
			}
		}

		if(defined("DOING_AJAX") && DOING_AJAX && isset($_POST['states'])){
			echo json_encode($result_terms);
			die();
		}

		return $result_terms;
	}

	private function get_taxonomies_type($post_type,$hierarchical){
		$result = [];
		$taxs = get_object_taxonomies( $post_type, 'objects' );
		foreach($taxs as $k => $tax){
			if($tax->hierarchical != $hierarchical ){
				unset($taxs[$k]);
			}else{
				$result[] = $tax->name;
			}
		}
		return $result;
	}

	private function get_defaults(){
		$defaults = array(
			'title'             => esc_attr__( 'Recent Posts', 'rpwe' ),
			'limit'            => 5,
			//'offset'         => 0,
			'order'            => 'DESC',
			'orderby'          => 'date',
			'cat'              => array(),
			'tag'              => array(),
			//'taxonomy'       => '',
			'post_type'        => array( 'post' ),
			'post_status'      => 'publish',
			'ignore_sticky'    => 1,
			'excerpt'          => false,
			'excerpt_length'   => 10,
			'thumb'            => true,
			'thumb_size'       => "thumbnail",
			//'thumb_height'   => 45,
			//'thumb_width'    => 45,
			//'thumb_default'  => 'http://placehold.it/45x45/f0f0f0/ccc',
			//'thumb_align'    => 'rpwe-alignleft',
			'date'             => true,
			'date_relative'    => false,
			'readmore'         => false,
			'readmore_text'    => _x( 'Read More &raquo;', 'Recent Posts Widget', 'waboot' ),
			'readmore_prefix'    => "&hellip;&nbsp;&nbsp;",
		);
		return $defaults;
	}

	function post_tpl(array $settings){
		$excerpt = $settings['excerpt'] ? (new HTMLView("templates/admin/widgets/recents-posts/post-excerpt.php"))->clean()->get(['settings'=>$settings]) : false;
		$footer = $settings['date'] ? (new HTMLView("templates/admin/widgets/recents-posts/post-footer.php"))->clean()->get(['settings'=>$settings]) : false;
		(new HTMLView("templates/admin/widgets/recents-posts/post.php"))->clean()->display([
			'excerpt' => $excerpt,
			'footer' => $footer,
			'link_title' => sprintf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute('echo=0') ),
			'settings' => $settings
		]);
	}

	function empty_posts_tpl(){
		_ex("No posts","Recent Post Widget","waboot");
	}
}