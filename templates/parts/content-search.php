<article role="article" id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>
    <?php if( has_post_thumbnail() ) : ?>
        <figure class="entry__image">
            <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', LANG_TEXTDOMAIN ), the_title_attribute( 'echo=0' ) ); ?>">
                <?php echo get_the_post_thumbnail( $post->ID, 'large' ) ?>
            </a>
        </figure>
    <?php endif; ?>
    <div class="entry__content">
        <div class="entry__meta">
            <ul class="entry__categories">
                <?php 
                    $categories = get_the_category();
                    $separator = '';
                    $output = '';
                    if ( ! empty( $categories ) ) {
                        foreach( $categories as $category ) {
                            $output .= '<li><a href="' . esc_url( get_category_link( $category->term_id ) ) . '" alt="' . esc_attr( sprintf( __( 'View all posts in %s', 'textdomain' ), $category->name ) ) . '">' . esc_html( $category->name ) . '</a></li>' . $separator;
                        }
                        echo trim( $output, $separator );
                    }
                ?>
            </ul>
            <time class="entry__date" datetime="<?php echo $tag_date; ?>"><?php echo get_the_date( 'j F Y'); ?></time>
            <?php if( !is_single() ) {
                comments_popup_link( __( ' Nessun commento', LANG_TEXTDOMAIN ), __( ' 1 Commento', LANG_TEXTDOMAIN ), __( ' % commenti', LANG_TEXTDOMAIN ) );
            } ?>
        </div>
        <h3>
            <a href="<?php the_permalink() ?>">
                <?php the_title(); ?>
            </a>
        </h3>
        <?php if( !is_single() ) : ?>
            <p>
                <?php \Waboot\inc\trimmedExcerpt(20, '...'); ?>
                <a class="more__link" href="<?php the_permalink() ?>">
                    <?php _e('Continue reading', LANG_TEXTDOMAIN) ?>
                </a>
            </p>
        <?php endif; ?>
        <?php do_action( 'waboot/article/list/footer' ); ?>
    </div>
</article>