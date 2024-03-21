<?php
	use function Waboot\inc\displayModal;
	
	$args = array(
							'post_type' => 'wp_block',
							'posts_per_page' => -1,
							'tax_query' => array(
													array(
																			'taxonomy' => 'wp_pattern_category',
																			'field' => 'slug',
																			'terms' => 'modal',
													)
							)
	);
	
	$query = new WP_Query($args);
	
	if ($query->have_posts()) {
		$modals = array();
		
		while ($query->have_posts()) {
			$query->the_post();
			$modalTitle = get_the_title();
			$modalLabel = lcfirst(str_replace(' ', '', $modalTitle));
			$modals[] = array(
									'title' => $modalTitle,
									'id' => get_the_ID(),
									'label' => $modalLabel
			);
		}
		wp_reset_postdata();
	}
	
	if (!empty($modals)) {
		foreach ($modals as $modal) {
			$modalContent = get_post_field('post_content', $modal['id']);
			displayModal($modal['label'], $modalContent);
		}
	}
