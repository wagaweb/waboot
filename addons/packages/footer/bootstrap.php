<?php
	namespace Waboot\addons\packages\footer;
	
	use Waboot\inc\core\mvc\HTMLView;
	use function Waboot\addons\getAddonDirectory;
	use \WP_Query;
	
	add_action('customize_register', function (\WP_Customize_Manager $wpCustomize) {
		$wpCustomize->add_section('footer', array(
								'title' => __('Footer'),
								'priority' => 15,
		));
		
		$footerBlocks = get_footer_blocks();
		
		$wpCustomize->add_setting('footerSelect', array(
								'type' => 'option',
								'transport' => 'refresh',
		));
		
		$wpCustomize->add_control('footerSelect', array(
								'type' => 'select',
								'section' => 'footer',
								'settings' => 'footerSelect',
								'label' => __('Select Reusable Block for Footer', LANG_TEXTDOMAIN),
								'priority' => 10,
								'choices' => $footerBlocks
		));
	});
	
	add_action('waboot/layout/footer', function(){
		$footerBlock = get_option('footerSelect');
		
		$v = new HTMLView(getAddonDirectory('footer').'/templates/footerTemplate.php', false);
		$v->clean()->display([
								'footerBlock' => is_string($footerBlock) ? $footerBlock : ''
		]);
	}, 9);
	
	function get_footer_blocks() {
		$footerBlocks = array();
		
		$reusableBlocks = new WP_Query(array(
								'post_type' => 'wp_block',
								'posts_per_page' => -1,
								'orderby' => 'title',
								'order' => 'ASC',
								'tax_query' => array(
														array(
																				'taxonomy' => 'wp_pattern_category',
																				'field' => 'slug',
																				'terms' => 'footer',
														),
								),
		));
		
		if ($reusableBlocks->have_posts()) {
			while ($reusableBlocks->have_posts()) {
				$reusableBlocks->the_post();
				$footerBlocks[get_the_ID()] = get_the_title();
			}
			wp_reset_postdata();
		}
		
		return $footerBlocks;
	}
