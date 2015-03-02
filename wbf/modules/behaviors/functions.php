<?php

namespace WBF\modules\behaviors;

/**
 * Get a behaviour.
 * @param $name
 * @param string $return (value OR array)
 * @return array|bool|mixed|string
 */
function get_behavior($name, $post_id = 0, $return = "value") {

	if ($post_id == 0 && !is_archive()) {
		if(is_home() || is_404() || is_search()){
			$post_id = get_queried_object_id();
		}else{
			global $post;
			$post_id = $post->ID;
		}
	}

	if(is_archive()){
		$blog_page = get_option('page_for_posts');
		if($blog_page){
			$post_id = $blog_page;
		}
	}

	$b = BehaviorsManager::get($name, $post_id);

	if(!$b->is_enable_for_node($post_id)) return null;

	if($return == "value"){
		return $b->value;
	}else{
		return $b;
	}
}

function create_metabox(){
	$behaviors = BehaviorsManager::getAll();
	add_meta_box("behavior","Behaviors",'\WBF\modules\behaviors\display_metabox',null,"advanced","core",array($behaviors));
}

function display_metabox(\WP_Post $post,array $behaviors){
	$behaviors = $behaviors['args'][0];

	wp_nonce_field('behaviors_meta_box','behaviors_meta_box_nonce');

	?>
	<?php $opt_n=0; foreach($behaviors as $b) : ?>
		<?php if($b->is_enable_for_node($post->ID)) : ?>
			<?php
			$opt_n++;
			$b->print_metabox($post->ID);
			?>
		<?php endif; ?>
	<?php endforeach; ?>
	<?php if($opt_n == 0) : ?>
		<p>No behavior available for this post type.</p>
	<?php endif;
}

function save_metabox($post_id){
	// Check if our nonce is set.
	if ( ! isset( $_POST['behaviors_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['behaviors_meta_box_nonce'], 'behaviors_meta_box' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	// Then save behaviors...

	$behaviors = BehaviorsManager::getAll();
	foreach($behaviors as $b){
		$metaname = $b->metaname;

		if($b->is_enable_for_node($post_id)){
			if(!isset($_POST[$metaname])){
				if($b->type == "checkbox"){
					if($b->has_multiple_choices())
						$_POST[$metaname] = array();
					else
						$_POST[$metaname] = "0";
				}
			}

			if(isset($_POST[$metaname])){
				if(isset($_POST[$metaname."_default"]) || (is_array($_POST[$metaname]) && in_array("_default",$_POST[$metaname]))){
					$b->set_value("_default");
				}else{
					$b->set_value($_POST[$metaname]);
				}
				$b->save_meta($post_id);
			}
		}
	}
}