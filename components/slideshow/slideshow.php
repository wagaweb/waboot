<?php
/**
Component Name: Slideshow
Description: Waboot Slideshow Component
Version: 1.0
Author: WAGA Team <dev@waga.it>
Author URI: http://www.waga.it
 */

class SlideshowComponent extends Waboot_Component{

	public function setup(){
		parent::setup();
		// Banner Preset Image
		add_image_size('banner', 1280, 500, array('center', 'center') ); // (cropped)
        // Register post type
        register_post_type('slideshow', array(
            'public' => true,
            'label'  => __("Slideshows","waboot"),
            'supports' => array('title','revisions','author'),
            'menu_icon' => 'dashicons-format-image'
        ));
		// Slideshow Fields
		if( function_exists('register_field_group') ):
			register_field_group(array (
				'key' => 'group_wb_slideshow',
				'title' => 'Campi Slideshow',
				'fields' => array (
					array (
						'key' => 'field_wb_slideshow',
						'label' => 'Immagini Slideshow',
						'name' => 'slideshow_images',
						'prefix' => '',
						'type' => 'gallery',
						'instructions' => '',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'min' => '',
						'max' => '',
						'preview_size' => 'thumbnail',
						'library' => 'all',
					),
				),
				'location' => array (
					array (
						array (
							'param' => 'post_type',
							'operator' => '==',
							'value' => 'slideshow',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'acf_after_title',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
			));
			register_field_group(array (
				'key' => 'group_wb_slideshow_options',
				'title' => 'Opzioni Slideshow',
				'fields' => array (
					array (
						'key' => 'field_wb_slideshow_height',
						'label' => 'Slideshow Height',
						'name' => 'slideshow_height',
						'prefix' => '',
						'type' => 'number',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 400,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_wb_slideshow_height_mobile',
						'label' => 'Slideshow Height Mobile',
						'name' => 'slideshow_height_mobile',
						'prefix' => '',
						'type' => 'number',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 400,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_wb_slideshow_items',
						'label' => 'Items',
						'name' => 'slideshow_items',
						'prefix' => '',
						'type' => 'number',
						'instructions' => '',
						'required' => 0,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'default_value' => 1,
						'placeholder' => '',
						'prepend' => '',
						'append' => '',
						'min' => '',
						'max' => '',
						'step' => '',
						'readonly' => 0,
						'disabled' => 0,
					),
					array (
						'key' => 'field_wb_slideshow_nav',
						'label' => 'Navigation',
						'name' => 'slideshow_navigation',
						'prefix' => '',
						'type' => 'select',
						'instructions' => '',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array (
							'true' => 'True',
							'false' => 'False',
						),
						'default_value' => array (
							'true' => 'True',
						),
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 0,
						'ajax' => 0,
						'placeholder' => '',
						'disabled' => 0,
						'readonly' => 0,
					),
					array (
						'key' => 'field_wb_slideshow_dots',
						'label' => 'Dots',
						'name' => 'slideshow_dots',
						'prefix' => '',
						'type' => 'select',
						'instructions' => '',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array (
							'true' => 'True',
							'false' => 'False',
						),
						'default_value' => array (
							'false' => 'False',
						),
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 0,
						'ajax' => 0,
						'placeholder' => '',
						'disabled' => 0,
						'readonly' => 0,
					),
					array (
						'key' => 'field_wb_slideshow_loop',
						'label' => 'Loop',
						'name' => 'slideshow_loop',
						'prefix' => '',
						'type' => 'select',
						'instructions' => '',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array (
							'true' => 'True',
							'false' => 'False',
						),
						'default_value' => array (
							'true' => 'True',
						),
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 0,
						'ajax' => 0,
						'placeholder' => '',
						'disabled' => 0,
						'readonly' => 0,
					),
					array (
						'key' => 'field_wb_slideshow_type',
						'label' => 'Slideshow Type',
						'name' => 'slideshow_type',
						'prefix' => '',
						'type' => 'select',
						'instructions' => '',
						'required' => 1,
						'conditional_logic' => 0,
						'wrapper' => array (
							'width' => '',
							'class' => '',
							'id' => '',
						),
						'choices' => array (
							'fixed' => 'Fixed',
							'fluid' => 'Fluid',
						),
						'default_value' => array (
							'fixed' => 'Fixed',
						),
						'allow_null' => 0,
						'multiple' => 0,
						'ui' => 0,
						'ajax' => 0,
						'placeholder' => '',
						'disabled' => 0,
						'readonly' => 0,
					),
				),
				'location' => array (
					array (
						array (
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'slideshow',
						),
					),
				),
				'menu_order' => 0,
				'position' => 'side',
				'style' => 'default',
				'label_placement' => 'top',
				'instruction_placement' => 'label',
				'hide_on_screen' => '',
			));

		endif;
        // Register shortcode
        add_shortcode( "wbslideshow" , array($this,'shortcode') );
        // Add metabox for shortcode usage
        add_action( 'add_meta_boxes_slideshow', array($this,'register_metabox') );
	}

    public function widgets(){
        register_widget("WabootSlideshowWidget");
    }

    public function shortcode($atts){
        if(isset($atts['id'])){
            $this->display_slideshow($atts['id']);
        }
    }

    public function register_metabox(){
        add_meta_box( 'wbslieshow-usage', __("Usage","waboot"), array($this,"usage_metabox"), "slideshow", "side", "low");
    }

    public function usage_metabox($post){
        ?>
        <p><?php _e("Copy & paste the shortcode directly into any WordPress post or page.","waboot"); ?></p>
        <p>
            <code>[wbslideshow id=<?php echo $post->ID; ?>]</code>
        </p>
        <?php
    }

	public function scripts(){
		wp_enqueue_script('owlcarousel-custom-script', $this->directory_uri . '/owl.carousel-custom.js', array('jquery','owlcarousel-js'), false, false);
	}

	public function styles(){
		wp_enqueue_style('owlcarousel-css');
	}

	static function has_images($post_id = 0){
        global $post;
        if($post_id == 0){
            $post_id = $post->ID;
        }
		if(get_field('slideshow_images', $post_id)){
			return true;
		}
		return false;
	}

	static function display_slideshow($post_id = 0){
		global $post;
        if($post_id == 0){
            $post_id = $post->ID;
        }
        $slideshow_post = get_post($post_id);
        $slideshow_type = get_field('slideshow_type', $post_id);
        if(!isset($slideshow_type)) $slideshow_type = "fixed";
        ?>
		<?php if(self::has_images($post_id)): ?>
			<div class="waboot-slideshow">
				<?php
				$images = get_field('slideshow_images', $post_id);
				if( $images ):
				?>

					<?php if ($slideshow_type == 'fixed') : ?>

					<div id="owl-<?php echo $slideshow_post->post_name; ?>" class="owl-carousel">
						<?php foreach( $images as $image ): ?>
							<div style="background-image: url('<?php echo $image['sizes']['large']; ?>'); height:<?php
							if (wb_is_mobile()) { echo get_field('slideshow_height_mobile', $post_id); }
							else { echo get_field('slideshow_height', $post_id); }
							?>px">
								<span class="slideshow-caption"><?php echo $image['caption']; ?></span>
							</div>
						<?php endforeach; ?>
					</div>

					<?php elseif ($slideshow_type == 'fluid') : ?>

					<div id="owl-<?php echo $slideshow_post->post_name; ?>" class="owl-carousel">
						<?php foreach( $images as $image ): ?>
							<div style="overflow:hidden; max-height:<?php
							if (wb_is_mobile()) { echo get_field('slideshow_height_mobile', $post_id); }
							else { echo get_field('slideshow_height', $post_id); }
							?>px">
								<img src="<?php echo $image['sizes']['banner']; ?>" />
								<span class="slideshow-caption"><?php echo $image['caption']; ?></span>
							</div>
						<?php endforeach; ?>
					</div>

					<?php endif; ?>

				<?php endif; ?>
			</div>
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    jQuery("#owl-<?php echo $slideshow_post->post_name; ?>").owlCarousel({
                        items: <?php echo get_field('slideshow_items', $post_id); ?>,
                        loop: <?php echo get_field('slideshow_loop', $post_id); ?>,
                        nav: <?php echo get_field('slideshow_navigation', $post_id); ?>,
                        navText: ['<i class="fa fa-chevron-left"></i>','<i class="fa fa-chevron-right"></i>'],
                        dots: <?php echo get_field('slideshow_dots', $post_id); ?>
                    });
                });
            </script>
		<?php endif; ?>
		<?php
	}
}

class WabootSlideshowWidget extends WP_Widget{
    function WabootSlideshowWidget() {
        // Instantiate the parent object
        parent::__construct( false, 'Waboot Slideshow Widget' );
    }

    function widget( $args, $instance ) {
        if(SlideshowComponent::has_images($instance['post_id'])){
            SlideshowComponent::display_slideshow($instance['post_id']);
        }
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['post_id'] = strip_tags( $new_instance['post_id'] );
        return $instance;
    }

    function form( $instance ) {
        /* Set up the default form values. */
        $defaults = array(
            'post_id' => '0',
        );
        /* Merge the user-selected arguments with the defaults. */
        $instance = wp_parse_args( (array) $instance, $defaults );
        $all_slideshows = get_posts(array(
            "post_type" => "slideshow",
            "posts_per_page" => -1
        ));
        ?>
        <?php if(!empty($all_slideshows)) : ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'post_id' ); ?>"><?php _e( 'Choose the slideshow to display:', 'waboot' ); ?></label>
            <select id="<?php echo $this->get_field_id( 'post_id' ); ?>" name="<?php echo $this->get_field_name( 'post_id' ); ?>" class="select">
                <option value="0" <?php selected( $instance['post_id'], '0' ); ?>><?php _e( 'Select a slideshow:', 'waboot' ); ?></option>
                <?php foreach($all_slideshows as $k=>$v) : ?>
                    <option value="<?php echo $v->ID; ?>" <?php selected( $instance['post_id'], $v->ID ); ?>><?php echo $v->post_title; ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php else: ?>
        <p>
           <?php _e("No slideshow defined"); ?>
        </p>
        <?php endif; ?>
        <?php
    }
}