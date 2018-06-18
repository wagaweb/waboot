<?php if(\Waboot\functions\get_option("blog_title_position") === "bottom") : ?>
<header class="entry__header">
    <h1 class="entry__title archive__title">
        <?php printf( __( 'Search Results for: %s', 'waboot' ), '<span>' . get_search_query() . '</span>' ); ?>
    </h1>
</header>
<?php endif; ?>

<?php if ( have_posts() ) : ?>
	<?php \Waboot\template_tags\post_navigation( 'nav-above' ); // display content nav above posts? ?>
	<?php
	// Start the Loop
	while ( have_posts() ) : the_post();
		get_template_part( '/templates/parts/content', 'search' );
	endwhile;
	?>
	<?php \Waboot\template_tags\post_navigation( 'nav-below' ); // display content nav below posts? ?>
<?php else : ?>
	<?php
	// No results
	get_template_part( '/templates/parts/content', 'none' );
	?>
<?php endif; ?>