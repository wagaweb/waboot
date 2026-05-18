<?php
/**
 * The template for displaying Comments.
 */

if ( post_password_required() )
	return;
?>

<?php if ( have_comments() ) : ?>
	<div id="comments" class="comments__area">
		<h2 class="comments__title">
			<?php
			printf( _nx( 'One thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', LANG_TEXTDOMAIN ),
			number_format_i18n( get_comments_number() ), '<span>' . get_the_title() . '</span>' );
			?>
		</h2>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
			<nav id="comment__nav--above" class="comment__navigation" aria-label="<?php esc_attr_e( 'Navigazione commenti', LANG_TEXTDOMAIN ); ?>">
				<ul class="pager">
					<li class="previous"><?php previous_comments_link( '<span aria-hidden="true">&laquo;</span> ' . esc_html__( 'Older Comments', LANG_TEXTDOMAIN ) ); ?></li>
					<li class="next"><?php next_comments_link( esc_html__( 'Newer Comments', LANG_TEXTDOMAIN ) . ' <span aria-hidden="true">&raquo;</span>' ); ?></li>
				</ul>
			</nav><!-- #comment-nav-above -->
		<?php endif; // check for comment navigation ?>

		<ol class="comment__list">
		<?php
			/*
			 * Display comment list
			 */
			wp_list_comments( array( 'callback' => '\Waboot\inc\renderComment' ) );
		?>
		</ol><!-- .comment-list -->

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
			<nav id="comment__nav--below" class="comment__navigation" aria-label="<?php esc_attr_e( 'Navigazione commenti', LANG_TEXTDOMAIN ); ?>">
				<ul class="pager">
					<li class="prev__link"><?php previous_comments_link( esc_html__( 'Older Comments', LANG_TEXTDOMAIN ) . ' <span aria-hidden="true">&raquo;</span>' ); ?></li>
					<li class="next__link"><?php next_comments_link( '<span aria-hidden="true">&laquo;</span> ' . esc_html__( 'Newer Comments', LANG_TEXTDOMAIN ) ); ?></li>
				</ul>
			</nav><!-- #comment-nav-below -->
		<?php endif; ?>
	</div><!-- #comments -->
<?php endif; // have_comments() ?>

<?php
// If comments are closed and there are comments, let's leave a little note, shall we?
if ( ! comments_open() && get_comments_number() != 0 && post_type_supports( get_post_type(), 'comments' ) ) : ?>
	<p class="no-comments"><?php _e( 'Comments are closed.', LANG_TEXTDOMAIN ); ?></p>
<?php endif; ?>

<?php
$commentFormArgs = [
	'comment_notes_before' => '<p class="comment-notes" id="comment-required-note" role="note">' . esc_html__( 'I campi contrassegnati con', LANG_TEXTDOMAIN ) . ' <span aria-hidden="true">*</span> ' . esc_html__( 'sono obbligatori.', LANG_TEXTDOMAIN ) . '</p>',
	'comment_field' => '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Comment', LANG_TEXTDOMAIN ) . ' <span class="required" aria-hidden="true">*</span></label> <textarea id="comment" name="comment" cols="35" rows="12" required aria-required="true" aria-describedby="comment-required-note"></textarea></p>',
	'fields' => [
		'author' => '<p class="comment-form-author"><label for="author">' . esc_html__( 'Name', LANG_TEXTDOMAIN ) . ' <span class="required" aria-hidden="true">*</span></label> <input class="input-comment-author" id="author" name="author" type="text" value="" size="30" required aria-required="true" autocomplete="name" aria-describedby="comment-required-note"></p>',
		'email'  => '<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', LANG_TEXTDOMAIN ) . ' <span class="required" aria-hidden="true">*</span></label> <input class="input-comment-email" id="email" name="email" type="email" value="" size="30" required aria-required="true" autocomplete="email" aria-describedby="comment-required-note"></p>',
		'url'    => '<p class="comment-form-url"><label for="url">' . esc_html__( 'Website', LANG_TEXTDOMAIN ) . '</label> <input class="input-comment-url" id="url" name="url" type="url" value="" size="30" autocomplete="url"></p>',
	],
	'class_submit' => 'btn'
];
comment_form(apply_filters('waboot/layout/comment_form_args',$commentFormArgs));
?>
