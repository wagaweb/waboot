<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="entry__wrapper">
        <div class="entry__content">
            <?php the_content(); ?>
            <?php do_action( 'waboot/entry/footer' ); ?>
        </div>
	</div>
</article>