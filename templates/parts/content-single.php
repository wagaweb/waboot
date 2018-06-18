<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php do_action( 'waboot/entry/header' ); ?>

    <?php if(has_post_thumbnail()) : ?>
        <div class="entry__image">
            <?php
            $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
            echo '<a href="' . $large_image_url[0] . '" title="' . the_title_attribute( 'echo=0' ) . '">';
            echo get_the_post_thumbnail( $post->ID, 'large', array( 'class' => 'img-responsive', 'title' => "" ) );
            echo '</a>';
            ?>
        </div>
    <?php endif ?>

    <div class="entry__content">
        <?php
        the_content();
        wp_link_pages();
        ?>
    </div>

    <?php do_action( 'waboot/entry/footer' ); ?>

</article>