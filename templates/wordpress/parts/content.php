<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php /* ---------------- SIGLE POST ---------------- */ ?>
    <?php if(is_singular()) : ?>
        <?php do_action( 'waboot/entry/header' ); ?>
        <div class="entry-content row">
            <?php if(has_post_thumbnail()) : ?>
                <div class="entry-image col-sm-12">
                    <?php
                        $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
                        echo '<a href="' . $large_image_url[0] . '" title="' . the_title_attribute( 'echo=0' ) . '">';
                        echo get_the_post_thumbnail( $post->ID, 'large', array( 'class' => 'img-responsive', 'title' => "" ) );
                        echo '</a>';
                    ?>
                </div>
            <?php endif ?>
            <div class="col-sm-12">
                <?php
                    the_content();
                    wp_link_pages();
                ?>
                <?php do_action( 'waboot/entry/footer' ); ?>
            </div>
        </div><!-- .entry-content -->
    <?php else : ?>
        <?php /* ---------------- BLOG PAGE ---------------- */ ?>
        <div class="entry-content row">
            <?php /* -------- article with thumbnail -------- */ ?>
            <?php if(has_post_thumbnail()) : ?>
                <div class="col-md-8 pull-right-md">
                    <?php do_action( 'waboot/entry/header' ); ?>
                </div>
                <div class="entry-image col-sm-4 ">
                    <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>">
                        <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail', array( 'class' => 'img-responsive', 'title' => "" ) ); ?>
                    </a>
                </div>
                <div class="col-sm-8 pull-right-sm">
                    <?php
                        the_excerpt();
                        wp_link_pages();
                    ?>
                    <?php do_action( 'waboot/entry/footer' ); ?>
                </div>
            <?php else : ?>
                <?php /* -------- article without thumbnail -------- */ ?>
                <div class="col-sm-12">
                    <?php do_action( 'waboot/entry/header' ); ?>
                    <?php
                        the_excerpt();
                        wp_link_pages();
                    ?>
                    <?php do_action( 'waboot/entry/footer' ); ?>
                </div>
            <?php endif; ?>
        </div><!-- .entry-content -->
    <?php endif; ?>
</article>
<!-- #post-<?php the_ID(); ?> -->