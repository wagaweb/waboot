<p>
	<?php \Waboot\template_tags\the_trimmed_excerpt(20, '...'); ?>
	<a class="more__link" href="<?php the_permalink() ?>">
		<?php _e('Continue reading', 'waboot') ?>
	</a>
</p>