<?php if ( have_posts() ) : ?>
	<header id="search-results-header" class="page-header">
		<h1 id="search-results-title" class="page-title entry-title"><?php printf( __( 'Search Results for: %s', 'waboot' ), '<span>' . get_search_query() . '</span>' ); ?></h1>
	</header>
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