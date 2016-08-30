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
			<?php \Waboot\functions\get_template_part( '/templates/wordpress/parts/content', get_post_format() ); ?>
		<?php endwhile; ?>
	</div>
	<?php if($vars['display_nav_below']) \Waboot\template_tags\post_navigation('nav-below'); ?>
<?php endif; ?>