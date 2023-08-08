<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="article__inner">
        <div class="article__content">
            <?php the_content(); ?>
            <?php do_action( 'waboot/article/list/footer' ); ?>
        </div>
	</div>
</article>
