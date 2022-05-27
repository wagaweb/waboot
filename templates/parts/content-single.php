<?php if( is_singular( 'post' ) ) : ?>
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
        <?php comments_popup_link( __( ' Nessun commento', LANG_TEXTDOMAIN ), __( ' 1 Commento', LANG_TEXTDOMAIN ), __( ' % commenti', LANG_TEXTDOMAIN ) ); ?>
    </div>
    <?php if( has_post_thumbnail() ) : ?>
        <figure class="entry__image">
            <?php
            $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
            echo get_the_post_thumbnail( get_the_id(), 'large', array( 'class' => 'img-responsive', 'title' => "" ) );
            ?>
        </figure>
    <?php endif ?>
    
    <div class="entry__content">
        <?php the_content(); ?>
    </div>
    <?php get_template_part('templates/view-parts/entry-related'); ?>
<?php else : ?>
    <?php the_content(); ?>
<?php endif; ?>
