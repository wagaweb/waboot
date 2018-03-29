<?php
/*
 * The template used when no results are found. Used in archive.php, author.php, index.php, and search.php
 */
?>
<article id="post-0" class="no-results not-found">
	<header class="entry-header">
		<h1 class="entry-title"><?php _e( 'Nothing Found', 'waboot' ); ?></h1>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<p><?php _e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'waboot' ); ?></p>
		<?php get_search_form(); ?>
	</div><!-- .entry-content -->
</article><!-- #post-0 -->