<?php

namespace Waboot\inc\widgets;

class Social extends \WP_Widget {
	function __construct(){
		parent::__construct( "waboot_social_widget", __( 'Waboot Social Widget', "waboot" ), array(
			'description' => __( 'A widget to show the social buttons in custom positions', "waboot" )
		));
	}

	function widget($args,$instance){
		echo $args['before_widget'];

		$socials = waboot_get_available_socials();

		foreach($socials as $name => $opt) :
			$opt_name = "waboot_social_".$name;
			$opt_value = of_get_option( $opt_name );
			if($opt_value) :
		?>
				<a href="<?php echo of_get_option( $opt_name ); ?>"><i class="fa <?php echo $opt['icon_class'] ?>"></i></a>
		<?php
			endif;
		endforeach;

		echo $args['after_widget'];
	}

	function form($instance){
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input placeholder="<?php _e( 'New title', 'wb-property' ); ?>" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php
	}

	function update($new_instance, $old_instance){
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
}