<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry-content row">

        <?php if(is_singular()) : ?>
            <?php the_content(); ?>
        <?php else : ?>
            <?php $first_video = waboot_get_first_video(); ?>
            <?php if ( $first_video ) : ?>
                <div class="wb-video-container">
                    <?php echo $first_video; ?>
                </div>
            <?php endif; ?>
            <?php the_excerpt(); ?>
            <?php wp_link_pages(); ?>
        <?php endif; ?>
        
    </div><!-- .entry-content -->
</article>
<!-- #post-<?php the_ID(); ?> -->