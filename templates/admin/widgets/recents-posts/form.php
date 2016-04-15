<div class="wbrw-column">
	<p><strong><?php _ex('General', 'Recent Posts Widget' , 'waboot') ?></strong></p>
	<p>
		<label for="<?php echo $widget->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
		<input placeholder="<?php _ex( 'New title', 'Recent Posts Widget' , 'waboot' ); ?>" class="widefat" id="<?php echo $widget->get_field_id( 'title' ); ?>" name="<?php echo $widget->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
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
					<input type="checkbox" value="<?php echo esc_attr( $t->name ); ?>" id="<?php echo $widget->get_field_id( 'post_type' ) . '-' . $t->name; ?>" name="<?php echo $widget->get_field_name( 'post_type' ); ?>[]" <?php checked( is_array( $instance['post_type'] ) && in_array( $t->name, $instance['post_type'] ) ); ?> />
					<label for="<?php echo $widget->get_field_id( 'post_type' ) . '-' . $t->name; ?>">
						<?php echo esc_html( $t->labels->name ); ?>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<!-- CATEGORIES -->
	<div class="multiple-check" data-wbrw-term-type="category" data-field-id=<?php echo $widget->get_field_id( 'cat' ); ?> data-field-name="<?php echo $widget->get_field_name( 'cat' ); ?>">
		<label>
			<?php _ex( 'Limit to Category', 'Recent Posts Widget' , 'waboot' ); ?>
		</label>
		<ul>
			<?php foreach ( $widget->get_terms( $instance ) as $term ) : ?>
				<li>
					<input type="checkbox" value="<?php echo (int) $term->term_id; ?>" id="<?php echo $widget->get_field_id( 'cat' ) . '-' . (int) $term->term_id; ?>" name="<?php echo $widget->get_field_name( 'cat' ); ?>[<?php echo $term->registered_for_post_type; ?>][]" <?php checked( is_array( $instance['cat'] ) && isset($instance['cat'][$term->registered_for_post_type]) && in_array( $term->term_id, $instance['cat'][$term->registered_for_post_type] ) ); ?> />
					<label for="<?php echo $widget->get_field_id( 'cat' ) . '-' . (int) $term->term_id; ?>">
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
	<div class="multiple-check" data-wbrw-term-type="tag" data-field-id=<?php echo $widget->get_field_id( 'tag' ); ?> data-field-name="<?php echo $widget->get_field_name( 'tag' ); ?>">
		<label>
			<?php _ex( 'Limit to Tag', 'Recent Posts Widget' , 'waboot' ); ?>
		</label>
		<ul>
			<?php foreach ( $widget->get_terms( $instance, false ) as $term ) : ?>
				<li>
					<input type="checkbox" value="<?php echo (int) $term->term_id; ?>" id="<?php echo $widget->get_field_id( 'tag' ) . '-' . (int) $term->term_id; ?>" name="<?php echo $widget->get_field_name( 'tag' ); ?>[<?php echo $term->registered_for_post_type; ?>][]" <?php checked( is_array( $instance['tag'] ) && isset($instance['tag'][$term->registered_for_post_type]) && in_array( $term->term_id, $instance['tag'][$term->registered_for_post_type] ) ); ?> />
					<label for="<?php echo $widget->get_field_id( 'tag' ) . '-' . (int) $term->term_id; ?>">
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
		<label for="<?php echo $widget->get_field_id( 'post_status' ); ?>">
			<?php _ex( 'Post Status', 'Recent Posts Widget' , 'waboot' ); ?>
		</label>
		<select class="widefat" id="<?php echo $widget->get_field_id( 'post_status' ); ?>" name="<?php echo $widget->get_field_name( 'post_status' ); ?>" style="width:100%;">
			<?php foreach ( $post_status as $status_value => $status_label ) { ?>
				<option value="<?php echo esc_attr( $status_label ); ?>" <?php selected( $instance['post_status'], $status_label ); ?>><?php echo esc_html( ucfirst( $status_label ) ); ?></option>
			<?php } ?>
		</select>
	</p>
	<!-- ORDER -->
	<p>
		<label for="<?php echo $widget->get_field_id( 'order' ); ?>">
			<?php _ex( 'Order', 'Recent Posts Widget' ,'waboot' ); ?>
		</label>
		<select class="widefat" id="<?php echo $widget->get_field_id( 'order' ); ?>" name="<?php echo $widget->get_field_name( 'order' ); ?>" style="width:100%;">
			<option value="DESC" <?php selected( $instance['order'], 'DESC' ); ?>><?php _ex( 'Descending', 'Recent Posts Widget' , 'waboot' ) ?></option>
			<option value="ASC" <?php selected( $instance['order'], 'ASC' ); ?>><?php _ex( 'Ascending', 'Recent Posts Widget', 'waboot' ) ?></option>
		</select>
	</p>
	<!-- ORDER_BY -->
	<p>
		<label for="<?php echo $widget->get_field_id( 'orderby' ); ?>">
			<?php _ex( 'Orderby', 'Recent Posts Widget' , 'waboot' ); ?>
		</label>
		<select class="widefat" id="<?php echo $widget->get_field_id( 'orderby' ); ?>" name="<?php echo $widget->get_field_name( 'orderby' ); ?>" style="width:100%;">
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
		<input class="checkbox" type="checkbox" <?php checked( $instance['ignore_sticky'], 1 ); ?> id="<?php echo $widget->get_field_id( 'ignore_sticky' ); ?>" name="<?php echo $widget->get_field_name( 'ignore_sticky' ); ?>" />
		<label for="<?php echo $widget->get_field_id( 'ignore_sticky' ); ?>">
			<?php _ex( 'Ignore sticky posts', 'Recent Posts Widget' ,'waboot' ); ?>
		</label>
	</p>
	<!-- POST LIMIT -->
	<p>
		<label for="<?php echo $widget->get_field_id( 'limit' ); ?>">
			<?php _ex( 'Number of posts to show', 'Recent Posts Widget' , 'waboot' ); ?>
		</label>
		<input class="widefat" id="<?php echo $widget->get_field_id( 'limit' ); ?>" name="<?php echo $widget->get_field_name( 'limit' ); ?>" type="number" step="1" min="-1" value="<?php echo (int)( $instance['limit'] ); ?>" />
	</p>
	<!-- THUMBNAILS -->
	<?php if ( current_theme_supports( 'post-thumbnails' ) ) : ?>
		<p>
			<input id="<?php echo $widget->get_field_id( 'thumb' ); ?>" name="<?php echo $widget->get_field_name( 'thumb' ); ?>" type="checkbox" <?php checked( $instance['thumb'] ); ?> />
			<label class="input-checkbox" for="<?php echo $widget->get_field_id( 'thumb' ); ?>">
				<?php _ex( 'Display Thumbnail', 'Recent Posts Widget' , 'waboot' ); ?>
			</label>
		</p>
		<!-- THUMBNAIL SIZE -->
		<p>
			<label class="input-checkbox" for="<?php echo $widget->get_field_id( 'thumb_size' ); ?>">
				<?php _ex( 'Thumbnail size', 'Recent Posts Widget' , 'waboot' ); ?>
			</label>
			<select id="<?php echo $widget->get_field_id( 'thumb_size' ); ?>" name="<?php echo $widget->get_field_name( 'thumb_size' ); ?>">
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
		<input id="<?php echo $widget->get_field_id( 'excerpt' ); ?>" name="<?php echo $widget->get_field_name( 'excerpt' ); ?>" type="checkbox" <?php checked( $instance['excerpt'] ); ?> />
		<label class="input-checkbox" for="<?php echo $widget->get_field_id( 'excerpt' ); ?>">
			<?php _ex( 'Display Excerpt', 'Recent Posts Widget' , 'waboot' ); ?>
		</label>
	</p>
	<p>
		<label for="<?php echo $widget->get_field_id( 'excerpt_length' ); ?>">
			<?php _ex( 'Excerpt Length', 'Recent Posts Widget' , 'waboot' ); ?>
		</label>
		<input class="widefat" id="<?php echo $widget->get_field_id( 'excerpt_length' ); ?>" name="<?php echo $widget->get_field_name( 'excerpt_length' ); ?>" type="number" step="1" min="0" value="<?php echo (int)( $instance['excerpt_length'] ); ?>" />
	</p>
	<!-- READMORE -->
	<p>
		<input id="<?php echo $widget->get_field_id( 'readmore' ); ?>" name="<?php echo $widget->get_field_name( 'readmore' ); ?>" type="checkbox" <?php checked( $instance['readmore'] ); ?> />
		<label class="input-checkbox" for="<?php echo $widget->get_field_id( 'readmore' ); ?>">
			<?php _e( 'Display Readmore', 'rpwe' ); ?>
		</label>
	</p>
	<p>
		<label for="<?php echo $widget->get_field_id( 'readmore_text' ); ?>">
			<?php _ex( 'Readmore Text', 'Recent Posts Widget' , 'waboot' ); ?>
		</label>
		<input class="widefat" id="<?php echo $widget->get_field_id( 'readmore_text' ); ?>" name="<?php echo $widget->get_field_name( 'readmore_text' ); ?>" type="text" value="<?php echo strip_tags( $instance['readmore_text'] ); ?>" />
	</p>
	<p>
		<label for="<?php echo $widget->get_field_id( 'readmore_prefix' ); ?>">
			<?php _ex( 'Readmore Text Prefix', 'Recent Posts Widget' , 'waboot' ); ?>
		</label>
		<input class="widefat" id="<?php echo $widget->get_field_id( 'readmore_prefix' ); ?>" name="<?php echo $widget->get_field_name( 'readmore_prefix' ); ?>" type="text" value="<?php echo strip_tags( $instance['readmore_prefix'] ); ?>" />
	</p>
	<!-- DATE -->
	<p>
		<input id="<?php echo $widget->get_field_id( 'date' ); ?>" name="<?php echo $widget->get_field_name( 'date' ); ?>" type="checkbox" <?php checked( $instance['date'] ); ?> />
		<label class="input-checkbox" for="<?php echo $widget->get_field_id( 'date' ); ?>">
			<?php _ex( 'Display Date', 'Recent Posts Widget' , 'waboot' ); ?>
		</label>
	</p>
	<p>
		<input id="<?php echo $widget->get_field_id( 'date_relative' ); ?>" name="<?php echo $widget->get_field_name( 'date_relative' ); ?>" type="checkbox" <?php checked( $instance['date_relative'] ); ?> />
		<label for="<?php echo $widget->get_field_id( 'date_relative' ); ?>">
			<?php _ex( 'Use Relative Date. eg: 5 days ago', 'Recent Posts Widget' , 'waboot' ); ?>
		</label>
	</p>
</div>
<div class="wbrw-clear"></div>