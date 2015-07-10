<?php

namespace Waboot\inc\widgets;

class RecentPosts extends \WP_Widget{

	var $widget_slug = "wbrw";

	function __construct(){
		$this->WP_Widget(
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
		echo $args['before_widget'];

		$post_ids = array_keys(wbf_get_posts());
		$q = new \WP_Query([
			'posts__in' => $post_ids
		]);

		if($q->have_posts()){
			while($q->have_posts()){
				$q->the_post();
				$this->post_tpl();
			}
			wp_reset_postdata();
		}else{
			$this->empty_posts_tpl();
		}
		wp_reset_query();

		echo $args['after_widget'];
	}

	function form($instance){
		/*
		 * GLOBALS
		 */
		$post_types = get_post_types(array('public' => true), 'objects');
		$post_status = get_available_post_statuses();
		/*
		 * INSTANCE
		 */
		$instance = wp_parse_args( (array) $instance, $this->get_defaults() );
		?>
		<div class="wbrw-column">
			<p><strong><?php _ex('General', 'Recent Posts Widget' , 'waboot') ?></strong></p>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
				<input placeholder="<?php _ex( 'New title', 'Recent Posts Widget' , 'waboot' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
			</p>
		</div>
		<div class="wbrw-column">
			<p><strong><?php _ex('Query', 'Recent Posts Widget' , 'waboot') ?></strong></p>
			<!-- POST TYPES -->
			<div class="multiple-check" data-wbrw-post-type-selector>
				<label>
					<?php _ex( 'Post Types', "Recent Posts Widget" , 'waboot' ); ?>
				</label>
				<ul>
					<?php foreach ( $post_types as $t ) : ?>
						<li>
							<input type="checkbox" value="<?php echo esc_attr( $t->name ); ?>" id="<?php echo $this->get_field_id( 'post_type' ) . '-' . $t->name; ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>[]" <?php checked( is_array( $instance['post_type'] ) && in_array( $t->name, $instance['post_type'] ) ); ?> />
							<label for="<?php echo $this->get_field_id( 'post_type' ) . '-' . $t->name; ?>">
								<?php echo esc_html( $t->labels->name ); ?>
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<!-- CATEGORIES -->
			<div class="multiple-check" data-wbrw-term-type="category">
				<label>
					<?php _ex( 'Limit to Category', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
				<ul>
					<?php foreach ( $this->get_terms( $instance ) as $term ) : ?>
						<li>
							<input type="checkbox" value="<?php echo (int) $term->term_id; ?>" id="<?php echo $this->get_field_id( 'cat' ) . '-' . (int) $term->term_id; ?>" name="<?php echo $this->get_field_name( 'cat' ); ?>[<?php echo $term->registered_for_post_type; ?>][]" <?php checked( is_array( $instance['cat'] ) && in_array( $term->term_id, $instance['cat'] ) ); ?> />
							<label for="<?php echo $this->get_field_id( 'cat' ) . '-' . (int) $term->term_id; ?>">
								<?php echo esc_html( $term->name ); ?> [<?php echo $term->registered_for_post_type; ?>]
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
				<script type="text/template">
					<% _.each(cats,function(t, k){ %>
					<li>
						<input type="checkbox" value="<%= t.term_id %>" id="<%= widget_cat %>-<%= t.term_id %>" name="<%= widget_cat %>[<%= t.registered_for_post_type %>][]" />
						<label for="<%= widget_cat %>-<%= t.term_id %>">
							<%= t.name %> [<%= t.registered_for_post_type %>]
						</label>
					</li>
					<% }); %>
				</script>
			</div>
			<!-- TAGS -->
			<div class="multiple-check" data-wbrw-term-type="tag">
				<label>
					<?php _ex( 'Limit to Tag', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
				<ul>
					<?php foreach ( $this->get_terms( $instance, false ) as $term ) : ?>
						<li>
							<input type="checkbox" value="<?php echo (int) $term->term_id; ?>" id="<?php echo $this->get_field_id( 'tag' ) . '-' . (int) $term->term_id; ?>" name="<?php echo $this->get_field_name( 'tag' ); ?>[<?php echo $term->registered_for_post_type; ?>][]" <?php checked( is_array( $instance['tag'] ) && in_array( $term->term_id, $instance['tag'] ) ); ?> />
							<label for="<?php echo $this->get_field_id( 'tag' ) . '-' . (int) $term->term_id; ?>">
								<?php echo esc_html( $term->name ); ?> [<?php echo $term->registered_for_post_type; ?>]
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
				<script type="text/template">
					<% _.each(tags,function(t, k){ %>
						<li>
							<input type="checkbox" value="<%= t.term_id %>" id="<%= widget_tag %>-<%= t.term_id %>" name="<%= widget_tag %>[<%= t.registered_for_post_type %>][]" />
							<label for="<%= widget_tag %>-<%= t.term_id %>">
								<%= t.name %> [<%= t.registered_for_post_type %>]
							</label>
						</li>
					<% }); %>
				</script>
			</div>
			<!-- POST STATUS -->
			<p>
				<label for="<?php echo $this->get_field_id( 'post_status' ); ?>">
					<?php _ex( 'Post Status', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'post_status' ); ?>" name="<?php echo $this->get_field_name( 'post_status' ); ?>" style="width:100%;">
					<?php foreach ( $post_status as $status_value => $status_label ) { ?>
						<option value="<?php echo esc_attr( $status_label ); ?>" <?php selected( $instance['post_status'], $status_label ); ?>><?php echo esc_html( ucfirst( $status_label ) ); ?></option>
					<?php } ?>
				</select>
			</p>
			<!-- ORDER -->
			<p>
				<label for="<?php echo $this->get_field_id( 'order' ); ?>">
					<?php _ex( 'Order', 'Recent Posts Widget' ,'waboot' ); ?>
				</label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>" style="width:100%;">
					<option value="DESC" <?php selected( $instance['order'], 'DESC' ); ?>><?php _ex( 'Descending', 'Recent Posts Widget' , 'waboot' ) ?></option>
					<option value="ASC" <?php selected( $instance['order'], 'ASC' ); ?>><?php _ex( 'Ascending', 'Recent Posts Widget', 'waboot' ) ?></option>
				</select>
			</p>
			<!-- ORDER_BY -->
			<p>
				<label for="<?php echo $this->get_field_id( 'orderby' ); ?>">
					<?php _ex( 'Orderby', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
				<select class="widefat" id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>" style="width:100%;">
					<option value="ID" <?php selected( $instance['orderby'], 'ID' ); ?>><?php _ex( 'ID', 'Recent Posts Widget', 'waboot' ) ?></option>
					<option value="author" <?php selected( $instance['orderby'], 'author' ); ?>><?php _ex( 'Author', 'Recent Posts Widget', 'waboot' ) ?></option>
					<option value="title" <?php selected( $instance['orderby'], 'title' ); ?>><?php _ex( 'Title', 'Recent Posts Widget', 'waboot' ) ?></option>
					<option value="date" <?php selected( $instance['orderby'], 'date' ); ?>><?php _ex( 'Date', 'Recent Posts Widget', 'waboot' ) ?></option>
					<option value="modified" <?php selected( $instance['orderby'], 'modified' ); ?>><?php _ex( 'Modified', 'Recent Posts Widget', 'waboot' ) ?></option>
					<option value="rand" <?php selected( $instance['orderby'], 'rand' ); ?>><?php _ex( 'Random', 'Recent Posts Widget', 'waboot' ) ?></option>
					<option value="comment_count" <?php selected( $instance['orderby'], 'comment_count' ); ?>><?php _ex( 'Comment Count', 'Recent Posts Widget', 'waboot' ) ?></option>
					<option value="menu_order" <?php selected( $instance['orderby'], 'menu_order' ); ?>><?php _ex( 'Menu Order', 'Recent Posts Widget', 'waboot' ) ?></option>
				</select>
			</p>
		</div>
		<div class="wbrw-column last">
			<p><strong><?php _ex('Options', 'Recent Posts Widget' , 'waboot') ?></strong></p>
			<!-- IGNORE STICKY -->
			<p>
				<input class="checkbox" type="checkbox" <?php checked( $instance['ignore_sticky'], 1 ); ?> id="<?php echo $this->get_field_id( 'ignore_sticky' ); ?>" name="<?php echo $this->get_field_name( 'ignore_sticky' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'ignore_sticky' ); ?>">
					<?php _ex( 'Ignore sticky posts', 'Recent Posts Widget' ,'waboot' ); ?>
				</label>
			</p>
			<!-- POST LIMIT -->
			<p>
				<label for="<?php echo $this->get_field_id( 'limit' ); ?>">
					<?php _ex( 'Number of posts to show', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="number" step="1" min="-1" value="<?php echo (int)( $instance['limit'] ); ?>" />
			</p>
		</div>
		<div class="wbrw-clear"></div>
	<?php
	}

	function update($new_instance, $old_instance){
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
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
			//Get only taxonomies that are hierarchical or not accordingly to $hierarchical param
			$taxs = call_user_func(function() use ($pt, $hierarchical){
				$result = [];
				$taxs = get_object_taxonomies( $pt, 'objects' );
				foreach($taxs as $k => $tax){
					if($tax->hierarchical != $hierarchical ){
						unset($taxs[$k]);
					}else{
						$result[] = $tax->name;
					}
				}
				return $result;
			});
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

	private function get_defaults(){
		$defaults = array(
			'title'             => esc_attr__( 'Recent Posts', 'rpwe' ),

			'limit'            => 5,
			'offset'           => 0,
			'order'            => 'DESC',
			'orderby'          => 'date',
			'cat'              => array(),
			'tag'              => array(),
			'taxonomy'         => '',
			'post_type'        => array( 'post' ),
			'post_status'      => 'publish',
			'ignore_sticky'    => 1,

			'excerpt'          => false,
			'length'           => 10,
			'thumb'            => true,
			'thumb_height'     => 45,
			'thumb_width'      => 45,
			'thumb_default'    => 'http://placehold.it/45x45/f0f0f0/ccc',
			'thumb_align'      => 'rpwe-alignleft',
			'date'             => true,
			'date_relative'    => false,
			'readmore'         => false,
			'readmore_text'    => __( 'Read More &raquo;', 'rpwe' ),
		);

		return $defaults;
	}

	function post_tpl(){
		echo "Ciao";
	}

	function empty_posts_tpl(){
		echo "Nessun post";
	}
}