<?php if($display_title): ?>
	<?php echo $title ?>
<?php endif; ?>
<?php if(have_posts()) : ?>
	<?php //waboot_content_nav('nav-above'); ?>
	<div class="<?php echo $blog_class; ?>">
		<?php //waboot_archive_sticky_posts($blog_style); // Display the sticky posts first... ?>
		<?php while(have_posts()): ?>
			<?php the_post(); ?>
			<?php
			/* Include the Post-Format-specific template for the content.
			 * If you want to override this in a child theme then include a file
			 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
			 */
			if($blog_style == "classic"){
				get_template_part( '/templates/parts/content', get_post_format() );
			}else{
				get_template_part( '/templates/parts/content', "blog-".$blog_style );
			}
			?>
		<?php endwhile; ?>
	</div>
	<?php //waboot_content_nav('nav-below'); ?>
<?php endif; ; ?>
