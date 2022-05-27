<?php
$related_query = new WP_Query([
	'post_type' 			=> 'post',
    'category__in' 			=> wp_get_post_categories(get_the_ID()),
    'post__not_in' 			=> [get_the_ID()],
    'posts_per_page' 		=> 3,
    'orderby' 				=> 'date',
]);
if ( $related_query->have_posts() ) : ?>
	<section class="entry__related">
		<h2><?php _e( 'Articoli correlati', LANG_TEXTDOMAIN ); ?></h2>
		<div class="entry__columns">
			<?php while ( $related_query->have_posts() ) : $related_query->the_post(); ?>
			<?php get_template_part( 'templates/parts/content' ); ?>
			<?php endwhile; ?>
		</div>
	</section>
<?php wp_reset_postdata(); endif; ?>