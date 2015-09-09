<?php

namespace Waboot\inc\widgets;

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
			<div class="multiple-check" data-wbrw-term-type="category" data-field-id=<?php echo $this->get_field_id( 'cat' ); ?> data-field-name="<?php echo $this->get_field_name( 'cat' ); ?>">
				<label>
					<?php _ex( 'Limit to Category', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
				<ul>
					<?php foreach ( $this->get_terms( $instance ) as $term ) : ?>
						<li>
							<input type="checkbox" value="<?php echo (int) $term->term_id; ?>" id="<?php echo $this->get_field_id( 'cat' ) . '-' . (int) $term->term_id; ?>" name="<?php echo $this->get_field_name( 'cat' ); ?>[<?php echo $term->registered_for_post_type; ?>][]" <?php checked( is_array( $instance['cat'] ) && isset($instance['cat'][$term->registered_for_post_type]) && in_array( $term->term_id, $instance['cat'][$term->registered_for_post_type] ) ); ?> />
							<label for="<?php echo $this->get_field_id( 'cat' ) . '-' . (int) $term->term_id; ?>">
								<?php echo esc_html( $term->name ); ?> [<?php echo $term->registered_for_post_type; ?>]
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
				<script type="text/template">
					<% _.each(terms,function(t, k){ %>
						<li>
							<input type="checkbox" value="<%= t.term_id %>" id="<%= field_id %>-<%= t.term_id %>" name="<%= field_name %>[<%= t.registered_for_post_type %>][]" />
							<label for="<%= field_name %>-<%= t.term_id %>">
								<%= t.name %> [<%= t.registered_for_post_type %>]
							</label>
						</li>
					<% }); %>
					<% if(terms.length == 0){ %>
						<?php _ex('No categories available', 'Recent Posts Widget', 'waboot'); ?>
					<% } %>
				</script>
			</div>
			<!-- TAGS -->
			<div class="multiple-check" data-wbrw-term-type="tag" data-field-id=<?php echo $this->get_field_id( 'tag' ); ?> data-field-name="<?php echo $this->get_field_name( 'tag' ); ?>">
				<label>
					<?php _ex( 'Limit to Tag', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
				<ul>
					<?php foreach ( $this->get_terms( $instance, false ) as $term ) : ?>
						<li>
							<input type="checkbox" value="<?php echo (int) $term->term_id; ?>" id="<?php echo $this->get_field_id( 'tag' ) . '-' . (int) $term->term_id; ?>" name="<?php echo $this->get_field_name( 'tag' ); ?>[<?php echo $term->registered_for_post_type; ?>][]" <?php checked( is_array( $instance['tag'] ) && isset($instance['tag'][$term->registered_for_post_type]) && in_array( $term->term_id, $instance['tag'][$term->registered_for_post_type] ) ); ?> />
							<label for="<?php echo $this->get_field_id( 'tag' ) . '-' . (int) $term->term_id; ?>">
								<?php echo esc_html( $term->name ); ?> [<?php echo $term->registered_for_post_type; ?>]
							</label>
						</li>
					<?php endforeach; ?>
				</ul>
				<script type="text/template">
					<% _.each(terms,function(t, k){ %>
						<li>
							<input type="checkbox" value="<%= t.term_id %>" id="<%= field_id %>-<%= t.term_id %>" name="<%= field_name %>[<%= t.registered_for_post_type %>][]" />
							<label for="<%= field_name %>-<%= t.term_id %>">
								<%= t.name %> [<%= t.registered_for_post_type %>]
							</label>
						</li>
					<% }); %>
					<% if(terms.length == 0){ %>
						<?php _ex('No tags available', 'Recent Posts Widget', 'waboot'); ?>
					<% } %>
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
			<!-- THUMBNAILS -->
			<?php if ( current_theme_supports( 'post-thumbnails' ) ) : ?>
			<p>
				<input id="<?php echo $this->get_field_id( 'thumb' ); ?>" name="<?php echo $this->get_field_name( 'thumb' ); ?>" type="checkbox" <?php checked( $instance['thumb'] ); ?> />
				<label class="input-checkbox" for="<?php echo $this->get_field_id( 'thumb' ); ?>">
					<?php _ex( 'Display Thumbnail', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
			</p>
			<!-- THUMBNAIL SIZE -->
			<p>
				<label class="input-checkbox" for="<?php echo $this->get_field_id( 'thumb_size' ); ?>">
					<?php _ex( 'Thumbnail size', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
				<select id="<?php echo $this->get_field_id( 'thumb_size' ); ?>" name="<?php echo $this->get_field_name( 'thumb_size' ); ?>">
					<?php foreach(get_intermediate_image_sizes() as $size_name) : ?>
						<?php
							$w = get_option( $size_name . '_size_w' );
							$h = get_option( $size_name . '_size_h' );
						?>
						<option value="<?php echo $size_name; ?>" <?php selected($size_name,$instance['thumb_size']); ?>><?php echo $size_name; ?> <?php if($w && $h && $w!="" && $h!="") echo "({$w}x{$h})"; ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<?php endif; ?>
			<!-- EXCERPT -->
			<p>
				<input id="<?php echo $this->get_field_id( 'excerpt' ); ?>" name="<?php echo $this->get_field_name( 'excerpt' ); ?>" type="checkbox" <?php checked( $instance['excerpt'] ); ?> />
				<label class="input-checkbox" for="<?php echo $this->get_field_id( 'excerpt' ); ?>">
					<?php _ex( 'Display Excerpt', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'excerpt_length' ); ?>">
					<?php _ex( 'Excerpt Length', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'excerpt_length' ); ?>" name="<?php echo $this->get_field_name( 'excerpt_length' ); ?>" type="number" step="1" min="0" value="<?php echo (int)( $instance['excerpt_length'] ); ?>" />
			</p>
			<!-- READMORE -->
			<p>
				<input id="<?php echo $this->get_field_id( 'readmore' ); ?>" name="<?php echo $this->get_field_name( 'readmore' ); ?>" type="checkbox" <?php checked( $instance['readmore'] ); ?> />
				<label class="input-checkbox" for="<?php echo $this->get_field_id( 'readmore' ); ?>">
					<?php _e( 'Display Readmore', 'rpwe' ); ?>
				</label>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'readmore_text' ); ?>">
					<?php _ex( 'Readmore Text', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'readmore_text' ); ?>" name="<?php echo $this->get_field_name( 'readmore_text' ); ?>" type="text" value="<?php echo strip_tags( $instance['readmore_text'] ); ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'readmore_prefix' ); ?>">
					<?php _ex( 'Readmore Text Prefix', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'readmore_prefix' ); ?>" name="<?php echo $this->get_field_name( 'readmore_prefix' ); ?>" type="text" value="<?php echo strip_tags( $instance['readmore_prefix'] ); ?>" />
			</p>
			<!-- DATE -->
			<p>
				<input id="<?php echo $this->get_field_id( 'date' ); ?>" name="<?php echo $this->get_field_name( 'date' ); ?>" type="checkbox" <?php checked( $instance['date'] ); ?> />
				<label class="input-checkbox" for="<?php echo $this->get_field_id( 'date' ); ?>">
					<?php _ex( 'Display Date', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
			</p>
			<p>
				<input id="<?php echo $this->get_field_id( 'date_relative' ); ?>" name="<?php echo $this->get_field_name( 'date_relative' ); ?>" type="checkbox" <?php checked( $instance['date_relative'] ); ?> />
				<label for="<?php echo $this->get_field_id( 'date_relative' ); ?>">
					<?php _ex( 'Use Relative Date. eg: 5 days ago', 'Recent Posts Widget' , 'waboot' ); ?>
				</label>
			</p>
		</div>
		<div class="wbrw-clear"></div>
	<?php
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

		$excerpt = function() use ($settings){
			?>
			<?php if($settings['excerpt']) : ?>
				<?php if($settings['readmore']) : ?>
					<?php waboot_the_trimmed_excerpt($settings['excerpt_length'],$settings['readmore_prefix']."<a href='".get_the_permalink()."' class='more-link'>".$settings['readmore_text']."</a>");?>
				<?php else: ?>
					<?php waboot_the_trimmed_excerpt($settings['excerpt_length'],false);?>
				<?php endif; ?>
			<?php endif; ?>
			<?php
		};
		$footer = function() use ($settings){
			?>
			<?php if($settings['date']) : ?>
				<?php if(!$settings['date_relative']): ?>
					<?php waboot_do_posted_on() ?>
				<?php else : ?>
					<?php waboot_do_posted_on(true) ?>
				<?php endif; ?>
			<?php endif; ?>
			<?php
		}
		?>
		<article role="article" <?php post_class("recent-post row"); ?>>
			<?php $link_title = sprintf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute('echo=0') ) ?>

			<?php if(has_post_thumbnail() && $settings['thumb']) : ?>
				<div class="entry-image col-sm-4 ">
					<a href="<?php the_permalink(); ?>" title="<?php echo $link_title; ?>"><?php echo get_the_post_thumbnail( get_the_ID(), $settings['thumb_size'], ['class' => 'img-responsive'] ); ?></a>
				</div>
				<div class="col-sm-8">
					<header>
						<h4><a href="<?php the_permalink(); ?>" title="<?php echo $link_title; ?>"><?php echo apply_filters("waboot_entry_title_text",get_the_title()); ?></a></h4>
					</header>
					<footer class="entry-footer">
						<?php $footer(); ?>
					</footer>
					<div class="entry-content">
						<?php $excerpt(); ?>
					</div>
				</div>

			<?php else: ?>
				<div class="col-sm-12">
					<header>
						<h4><a href="<?php the_permalink(); ?>" title="<?php echo $link_title; ?>"><?php echo apply_filters("waboot_entry_title_text",get_the_title()); ?></a></h4>
					</header>
					<footer class="entry-footer">
						<?php $footer(); ?>
					</footer>
					<div class="entry-content">
						<?php $excerpt(); ?>
					</div>
				</div>
			<?php endif; ?>
		</article>
		<?php
	}

	function empty_posts_tpl(){
		echo "Nessun post";
	}
}