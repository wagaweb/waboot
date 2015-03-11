<?php
/**
 * The template for displaying Archive pages.
 *
 * @package Waboot
 * @since Waboot 0.1
 */

get_header();
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
			<?php
				if(have_posts()){
					waboot_content_nav( 'nav-above' );
					/*
					 * Get the variables that can change the layout
					 */
					$blog_style = waboot_get_blog_layout();
					$blog_class = waboot_get_blog_class($blog_style);
					?>
					<div class="<?php echo $blog_class; ?>">
						<?php waboot_archive_sticky_posts($blog_style); // Display the sticky posts first... ?>
						<?php
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