<div class="pagination">
	<ul>
		<?php if(is_single()): ?>
			<?php previous_post_link( '<li class="prev__link">%link</li>', '<span class="meta-nav">' . _x( '&laquo;', 'Previous post link', LANG_TEXTDOMAIN ) . '</span> %title' ); ?>
			<?php next_post_link( '<li class="next__link">%link</li>', '%title <span class="meta-nav">' . _x( '&raquo;', 'Next post link', LANG_TEXTDOMAIN ) . '</span>' ); ?>
		<?php else : ?>
            <?php echo $pagination; ?>
		<?php endif; ?>
	</ul>
</div>
