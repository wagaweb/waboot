<?php if(have_posts()) : ?>
	<?php if($tpl_vars['display_nav_above']) \Waboot\template_tags\post_navigation('nav-above'); ?>
	<div class="<?php echo $tpl_vars['blog_class']; ?>">
		<?php //waboot_archive_sticky_posts($blog_style); // Display the sticky posts first... ?>
		<?php while(have_posts()): ?>
			<?php the_post(); ?>
			<?php \Waboot\functions\get_template_part( '/templates/parts/content', get_post_format() ); ?>
		<?php endwhile; ?>
	</div>
	<?php if($tpl_vars['display_nav_below']) \Waboot\template_tags\post_navigation('nav-below'); ?>
<?php endif; ?>