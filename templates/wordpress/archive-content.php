<?php $vars = \Waboot\functions\get_archives_template_vars(); ?>
<?php if($vars['display_title']): ?>
	<?php \Waboot\template_tags\wrapped_title('<h1 class="page-title">','</h1>',$vars['page_title']); ?>
<?php endif; ?>
<?php if(have_posts()) : ?>
	<?php if($vars['display_nav_above']) \Waboot\template_tags\post_navigation('nav-above'); ?>
	<div class="<?php echo $vars['blog_class']; ?>">
		<?php //waboot_archive_sticky_posts($blog_style); // Display the sticky posts first... ?>
		<?php while(have_posts()): ?>
			<?php the_post(); ?>
			<?php
			/* Include the Post-Format-specific template for the content.
			 * If you want to override this in a child theme then include a file
			 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
			 */
			if($vars['blog_style'] == "classic"){
				get_template_part('/templates/parts/content', get_post_format());
			}else{
				get_template_part('/templates/parts/content', "blog-".$vars['blog_style']);
			}
			?>
		<?php endwhile; ?>
	</div>
	<?php if($vars['display_nav_below']) \Waboot\template_tags\post_navigation('nav-below'); ?>
<?php endif; ?>