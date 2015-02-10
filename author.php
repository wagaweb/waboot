<?php
/**
 * The template for displaying Author Archive pages.
 *
 * @package Waboot
 * @since Waboot 1.0
 */

global $post;
get_header();
$blog_style = waboot_get_blog_layout();
?>
	<section id="main-wrap" class="<?php echo apply_filters( 'waboot_mainwrap_container_class', 'content-area col-sm-8' ); ?>">
		<main id="main" class="site-main" role="main">
			<?php if (of_get_option('waboot_blogpage_title_position') == "bottom") : ?>
				<header class="page-header">
					<?php
					do_action( 'waboot_archive_page_title', "<h1 class=\"page-title\">", "</h1>" );
					$author_description = get_the_author_meta("description",$post->post_author);
					if ( ! empty( $author_description ) )
						printf( '<div class="author-description">%s</div>', $author_description );
					?>
				</header>
			<?php endif; ?>
			<?php if(have_posts()){
				waboot_content_nav( 'nav-above' ); // display content nav above posts?
				?>
				<div class="blog-<?php echo $blog_style; ?>">
				<?php
				while(have_posts()){
					the_post();
					/* Include the Post-Format-specific template for the content.
					 * If you want to override this in a child theme then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					if($blog_style != "classic" && $blog_style != "blog"){
						get_template_part( '/templates/parts/content', "blog-{$blog_style}" );
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
