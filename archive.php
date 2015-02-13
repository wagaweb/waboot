<?php
/**
 * The template for displaying Archive pages.
 *
 * @package Waboot
 * @since Waboot 0.1
 */

global $wp_query;
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
get_header();
$blog_style = waboot_get_blog_layout();
$blog_class = waboot_get_blog_class($blog_style);
?>
	<section id="main-wrap" class="<?php echo apply_filters( 'waboot_mainwrap_container_class', 'content-area col-sm-8' ); ?>">
		<main id="main" class="site-main" role="main">
			<?php if (of_get_option('waboot_blogpage_title_position') == "bottom" && of_get_option('waboot_blogpage_displaytitle') == "1") : ?>
				<header class="page-header">
				<?php
					do_action( 'waboot_archive_page_title', "<h1 class=\"page-title\">", "</h1>" );
					$term_description = term_description();
					if ( ! empty( $term_description ) )
						printf( '<div class="taxonomy-description">%s</div>', $term_description );
				?>
				</header>
			<?php endif; ?>
			<?php waboot_archive_sticky_posts(); // sticky post query ?>
			<?php
				if(have_posts()){
					//rewind_posts();
					waboot_content_nav( 'nav-above' );
					// do the main query without stickies
					$sticky = get_option('sticky_posts');
					if(is_category() && ! empty($sticky)) {
						$cat_ID = get_query_var('cat');
						$args = array(
							'cat'                 => $cat_ID,
							'post_status'         => 'publish',
							'post__not_in'        => get_option( 'sticky_posts' ),
							'paged'               => $paged
						);
						$wp_query = new WP_Query($args);
					}
					elseif(is_tag() && ! empty($sticky)){
						$current_tag = get_queried_object_id();
						$args = array(
							'tag_id'              => $current_tag,
							'post_status'         => 'publish',
							'post__not_in'        => get_option( 'sticky_posts' ),
							'paged'               => $paged
						);
						$wp_query = new WP_Query($args);
					}
					?>
					<div class="<?php echo $blog_class; ?>">
					<?php
					// Start the Loop
					while(have_posts()){
						the_post();
						/* Include the Post-Format-specific template for the content.
						 * If you want to override this in a child theme then include a file
						 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
						 */
						if($blog_style != "classic"){
							get_template_part( '/templates/parts/content', "blog-".$blog_style );
						}else{
							get_template_part( '/templates/parts/content', get_post_format() );
						}
					}
					?>
					</div>
					<?php
					// Show navigation below post content
					waboot_content_nav( 'nav-below' );
				}else{
					// No results
					get_template_part( '/templates/parts/content', 'none' );
				} //have_posts ?>
		</main><!-- #main -->
	</section><!-- #main-wrap -->
<?php
get_sidebar();
get_footer(); ?>