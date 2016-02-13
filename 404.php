<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package Waboot
 * @since Waboot 1.0
 */

get_header(); ?>
	<div id="primary" class="content-area col-sm-12">
		<main id="main" class="site-main" role="main">
			<section class="post error-404 not-found text-center">

				<!--
				<div class="img404">
					<img src="<?php // echo get_site_url(); ?>/wp-content/themes/waboot/assets/dist/images/404.png">
				</div>
				-->

				<header class="entry-header">
					<h1 class="entry-title">
						<span class="title404">404</span><br/>
						<?php _e( 'Oops! That page can&rsquo;t be found.', 'waboot' ); ?>
					</h1>
				</header>

				<div class="entry-content">
					<div class="col-sm-8 col-sm-offset-2">
						<p><?php _e( 'It looks like nothing was found at this location. Maybe try a search or one of the links below?', 'waboot' ); ?></p>
						<?php get_search_form(); ?>
						<p><?php _e( 'Let\'s return to the', 'waboot' ); ?> <a href="<?php echo get_site_url(); ?>">Homepage</a> </p>
					</div>

				</div><!-- .entry-content -->
			</section><!-- .error-404 -->

		</main><!-- #main -->
	</div><!-- #primary -->
<?php
get_footer();