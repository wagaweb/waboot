<?php if(is_singular('post')) : ?>
    <?php do_action( 'waboot/article/footer' ); ?>
    
    <?php if(has_post_thumbnail()) : ?>
        <figure class="article__image">
            <?php
            $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
            echo '<a href="' . $large_image_url[0] . '" title="' . the_title_attribute( 'echo=0' ) . '">';
            echo get_the_post_thumbnail( get_the_id(), 'large', array( 'class' => 'img-responsive', 'title' => "" ) );
            echo '</a>';
            ?>
        </figure>
    <?php endif ?>
    
    <div class="article__content">
        <?php the_content(); ?>
    </div>
<?php else : ?>
    <?php the_content(); ?>
<?php endif; ?>
