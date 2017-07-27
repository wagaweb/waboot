<?php if(\Waboot\functions\get_option("blog_title_position") === "bottom") : ?>
<div class="title-wrapper">
    <h1 class="page-title entry-title">
        <?php printf( __( 'Search Results for: %s', 'waboot' ), '<span>' . get_search_query() . '</span>' ); ?>
    </h1>
</div>
<?php endif; ?>

<?php if ( have_posts() ) : ?>
	<?php \Waboot\template_tags\post_navigation( 'nav-above' ); // display content nav above posts? ?>
	<?php
	// Start the Loop
	while ( have_posts() ) : the_post();
		get_template_part( '/templates/wordpress/parts/content', 'search' );
	endwhile;
	?>
	<?php \Waboot\template_tags\post_navigation( 'nav-below' ); // display content nav below posts? ?>
<?php else : ?>
	<?php
	// No results
	get_template_part( '/templates/wordpress/parts/content', 'none' );
	?>
<?php endif; ?>