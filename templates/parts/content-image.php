<?php if(is_singular()) : ?>
    <?php if(has_post_thumbnail()): ?>
        <?php echo get_the_post_thumbnail( $post->ID, 'medium', array( 'class' => 'alignnone', 'title' => "" ) ); ?>
    <?php endif; ?>
    <?php the_content(); ?>
    <?php do_action( 'waboot/article/footer' ); ?>
<?php else : ?>
    <article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <div class="article__inner">
            <div class="article__content">
                <?php if(has_post_thumbnail()): ?>
                    <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', LANG_TEXTDOMAIN ), the_title_attribute( 'echo=0' ) ); ?>">
                        <?php echo get_the_post_thumbnail( $post->ID, 'medium', array( 'class' => 'alignnone', 'title' => "" ) ); ?>
                    </a>
                <?php endif; ?>
                <?php do_action( 'waboot/article/list/footer' ); ?>
            </div>
        </div>
    </article>
<?php endif; ?>
