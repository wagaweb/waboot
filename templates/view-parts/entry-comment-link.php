<span class="comments-link">
	<svg
			width="18"
			height="18"
			fill="none"
			stroke="currentColor"
			stroke-width="1.5"
			stroke-linecap="round"
			stroke-linejoin="round"
		>
			<use href="<?php echo get_template_directory_uri() ?>/assets/images/default/icons/feather-sprite.svg#message-square"/>
	</svg>
	
	<?php comments_popup_link( __( ' Leave a comment', LANG_TEXTDOMAIN ), __( ' 1 Comment', LANG_TEXTDOMAIN ), __( ' % Comments', LANG_TEXTDOMAIN ) ); ?>
</span>
