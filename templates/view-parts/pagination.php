<nav class="pagination" aria-label="<?php esc_attr_e( 'Navigazione pagine', LANG_TEXTDOMAIN ); ?>">
	<ul>
		<?php if(is_single()): ?>
			<?php previous_post_link(
				'<li class="prev__link">%link</li>',
				'<span class="meta-nav" aria-hidden="true">&laquo;</span> %title'
			); ?>
			<?php next_post_link(
				'<li class="next__link">%link</li>',
				'%title <span class="meta-nav" aria-hidden="true">&raquo;</span>'
			); ?>
		<?php else : ?>
            <?php echo $pagination; ?>
		<?php endif; ?>
	</ul>
</nav>
