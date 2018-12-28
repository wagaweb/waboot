<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

    <?php do_action( 'waboot/entry/header' ); ?>

    <div class="entry__content">
		<?php the_content( __( 'Continue reading', 'waboot' ) ); ?>
		<?php wp_link_pages(); ?>
	</div>

</article>