<nav id="<?php echo $nav_id; ?>" class="<?php echo $nav_class; ?>">
	<ul class="pagination">
		<?php if(is_single()): ?>
			<?php previous_post_link( '<li class="prev__link">%link</li>', '<span class="meta-nav">' . _x( '&laquo;', 'Previous post link', 'waboot' ) . '</span> %title' ); ?>
			<?php next_post_link( '<li class="next__link">%link</li>', '%title <span class="meta-nav">' . _x( '&raquo;', 'Next post link', 'waboot' ) . '</span>' ); ?>
		<?php elseif($can_display_pagination): ?>
			<?php if($show_pagination): ?>
				<?php echo $pagination; ?>
			<?php else: ?>
				<?php if(get_next_posts_link()): ?>
					<li class="prev__link"><?php next_posts_link(__('Next page', 'waboot'), $max_num_pages); ?><span class="meta-nav">&raquo;</span></li>
				<?php endif; ?>
				<?php if(get_previous_posts_link()): ?>
					<li class="next__link"><span class="meta-nav">&laquo;</span><?php previous_posts_link(__('Previous page', 'waboot')); ?></li>
				<?php endif; ?>
			<?php endif; ?>
		<?php endif; ?>
	</ul>
</nav>