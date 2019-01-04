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
			printf( _nx( 'One thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', get_comments_number(), 'comments title', 'waboot' ),
			number_format_i18n( get_comments_number() ), '<span>' . get_the_title() . '</span>' );
			?>
		</h2>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
			<nav id="comment__nav--above" class="comment__navigation" role="navigation">
				<h1 class="screen-reader-text"><?php _e( 'Comment navigation', 'waboot' ); ?></h1>
				<ul class="pager">
					<li class="previous"><?php previous_comments_link( __( '&laquo; Older Comments', 'waboot' ) ); ?></li>
					<li class="next"><?php next_comments_link( __( 'Newer Comments &raquo;', 'waboot' ) ); ?></li>
				</ul>
			</nav><!-- #comment-nav-above -->
		<?php endif; // check for comment navigation ?>

		<ol class="comment__list">
		<?php
			/*
			 * Display comment list
			 */
			wp_list_comments( array( 'callback' => '\Waboot\functions\render_comment' ) );
		?>
		</ol><!-- .comment-list -->

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : // are there comments to navigate through ?>
			<nav id="comment__nav--below" class="comment__navigation" role="navigation">
				<h1 class="screen-reader-text"><?php _e( 'Comment navigation', 'waboot' ); ?></h1>
				<ul class="pager">
					<li class="prev__link"><?php previous_comments_link( __( 'Older Comments &raquo;', 'waboot' ) ); ?></li>
					<li class="next__link"><?php next_comments_link( __( '&laquo; Newer Comments', 'waboot' ) ); ?></li>
				</ul>
			</nav><!-- #comment-nav-below -->
		<?php endif; ?>
	</div><!-- #comments -->
<?php endif; // have_comments() ?>

<?php
// If comments are closed and there are comments, let's leave a little note, shall we?
if ( ! comments_open() && get_comments_number() != 0 && post_type_supports( get_post_type(), 'comments' ) ) : ?>
	<p class="no-comments"><?php _e( 'Comments are closed.', 'waboot' ); ?></p>
<?php endif; ?>

<?php
$commentFormArgs = [
	'comment_field' => '<p class="comment-form-comment"><label for="comment">Comment</label> <textarea id="comment" name="comment" cols="35" rows="12" aria-required="true"></textarea></p>',
	'fields' => [
		'author' => '<p class="comment-form-author"><label for="author">Name <span class="required">*</span></label> <input class="input-comment-author" id="author" name="author" type="text" value="" size="30" aria-required="true"></p>',
		'email' => '<p class="comment-form-email"><label for="email">Email <span class="required">*</span></label> <input class="input-comment-email" id="email" name="email" type="text" value="" size="30" aria-required="true"></p>',
		'url' => '<p class="comment-form-url"><label for="url">Website</label> <input class="input-comment-url" id="url" name="url" type="text" value="" size="30"></p>',
	],
	'class_submit' => 'btn'
];
comment_form(apply_filters('waboot/layout/comment_form_args',$commentFormArgs));
?>
