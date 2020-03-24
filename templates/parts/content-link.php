<?php if(is_singular()) : ?>
    <?php the_content(); ?>
    <?php do_action( 'waboot/article/footer' ); ?>
<?php else : ?>
    <article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <div class="article__inner">
            <div class="article__content">
                <h2>
                    <a title="<?php printf( esc_attr__( 'Link to %s', LANG_TEXTDOMAIN ), the_title_attribute( 'echo=0' ) ); ?>" rel="bookmark" href="<?php echo \Waboot\inc\getFilteredLinkPostContent( 'link' ); ?>" target="_blank">
                    <?php the_title(); ?> &rarr;
                    </a>
                </h2>
                <?php echo \Waboot\inc\getFilteredLinkPostContent( 'post_content' ); ?>
                <?php do_action( 'waboot/article/list/footer' ); ?>
            </div>
        </div>
    </article>
<?php endif; ?>
