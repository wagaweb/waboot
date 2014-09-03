<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package Waboot
 * @since Waboot 1.0
 */

get_header(); ?>
	<div id="primary" class="<?php echo apply_filters( 'waboot_primary_container_class', 'content-area col-sm-8' ); ?>">

		<?php do_action( 'waboot_main_before' ); ?>
		<main id="main" class="site-main" role="main">

			<section class="post error-404 not-found">
				<header class="entry-header">
					<h1 class="entry-title"><?php _e( 'Oops! That page can&rsquo;t be found.', 'waboot' ); ?></h1>
				</header>

				<div class="entry-content">
					<p><?php _e( 'It looks like nothing was found at this location. Maybe try a search or one of the links below?', 'waboot' ); ?></p>

					<?php
					get_search_form();
					the_widget( 'WP_Widget_Recent_Posts' );
					?>

					<div class="widget">
						<h2 class="widget-title"><?php _e( 'Most Used Categories', 'waboot' ); ?></h2>
						<ul>
							<?php wp_list_categories( array( 'orderby' => 'count', 'order' => 'DESC', 'show_count' => 1, 'title_li' => '', 'number' => 10 ) ); ?>
						</ul>
					</div>

					<?php
					/* translators: %1$s: smiley */
					$archive_content = '<p>' . sprintf( __( 'Try looking in the monthly archives. %1$s', 'waboot' ), convert_smilies( ':)' ) ) . '</p>';
					the_widget( 'WP_Widget_Archives', 'dropdown=1', "after_title=</h2>$archive_content" );
					the_widget( 'WP_Widget_Tag_Cloud' );
					?>

				</div><!-- .entry-content -->
			</section><!-- .error-404 -->

		</main><!-- #main -->
		<?php do_action( 'waboot_main_after' ); ?>

	</div><!-- #primary -->
<?php
get_sidebar();
get_footer(); ?>