<?php if ( $comment->comment_type == "pingbacl" || $comment->comment_type == "trackback" ) : ?>
<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
	<div class="comment__body">
		<?php _e( 'Pingback:', 'waboot' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( 'Edit', 'waboot' ), '<span class="edit-link">', '</span>' ); ?>
	</div>
</li>
<?php else : ?>
<li id="comment-<?php comment_ID(); ?>" <?php comment_class( $additional_comment_class ); ?>>
	<article id="div-comment-<?php comment_ID(); ?>" class="comment__body">
		<footer class="comment__meta">
			<div class="comment__author vcard">
				<?php if ( $has_avatar ) echo $avatar; ?>
				<?php printf( __( '%s <span class="says">says:</span>', 'waboot' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
			</div><!-- .comment-author -->

			<div class="comment__metadata">
				<a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
					<time datetime="<?php comment_time( 'c' ); ?>">
						<?php printf( _x( '%1$s at %2$s', '1: date, 2: time', 'waboot' ), get_comment_date(), get_comment_time() ); ?>
					</time>
				</a>
				<?php edit_comment_link( __( 'Edit', 'waboot' ), '<span class="edit-link">', '</span>' ); ?>
			</div><!-- .comment-metadata -->

			<?php if ( !$is_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'waboot' ); ?></p>
			<?php endif; ?>
		</footer><!-- .comment-meta -->
		<div class="comment__content">
			<?php comment_text(); ?>
		</div><!-- .comment-content -->
		<?php
		comment_reply_link( array_merge($args, [
			'add_below' => 'div-comment',
			'depth' => $depth,
			'max_depth' => $args['max_depth'],
			'before' => '<div class="reply">',
			'after' => '</div>',
		]));
		?>
	</article>
</li>
<?php endif; ?>