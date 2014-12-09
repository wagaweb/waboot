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
						'library' => 'uploadedTo',
					),
				),
				'location' => array (
					array (
						array (
							'param' => 'options_page',
							'operator' => '==',
							'value' => 'slideshow-settings',
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
				),
				'location' => array (
					array (
						array (
							'param' => 'options_page',
							'operator' => '==',
							'value' => 'slideshow-settings',
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

		add_action("admin_menu",array($this,"add_menu"));
	}

	public function add_menu(){
		if( function_exists('acf_add_options_sub_page') ) {
			acf_add_options_sub_page(array(
				'title' => 'Slideshow',
				'slug' => 'slideshow-settings',
				'parent' => 'waboot_options',
				'capability' => 'edit_theme_options'
			));
		}
	}

	public function scripts(){
		wp_enqueue_script('owlcarousel-custom-script', $this->directory_uri . '/owl.carousel-custom.js', array('jquery','owlcarousel-js'), false, false);
	}

	public function styles(){
		wp_enqueue_style('owlcarousel-css');
	}

	static function display_slideshow(){
		?>
		<?php if(get_field('slideshow_images', 'option')): ?>
			<div class="waboot-slideshow">
				<?php
				$images = get_field('slideshow_images', 'option');
				if( $images ):
					?>
					<div class="owl-carousel">
						<?php foreach( $images as $image ): ?>
							<div style="background-image: url('<?php echo $image['sizes']['large']; ?>'); height:<?php
							if (wb_is_mobile()) { echo get_field('slideshow_height_mobile', 'option'); }
							else { echo get_field('slideshow_height', 'option'); }
							?>px">
								<span class="slideshow-caption"><?php echo $image['caption']; ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery(".owl-carousel").owlCarousel({
						items: <?php echo get_field('slideshow_items', 'option'); ?>,
						loop: <?php echo get_field('slideshow_loop', 'option'); ?>,
						nav: <?php echo get_field('slideshow_navigation', 'option'); ?>,
						navText: ['<i class="fa fa-chevron-left"></i>','<i class="fa fa-chevron-right"></i>'],
						dots: <?php echo get_field('slideshow_dots', 'option'); ?>
					});
				});
			</script>
		<?php endif; ?>
		<?php
	}
}