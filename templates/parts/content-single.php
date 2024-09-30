<?php if(is_singular('post')) : ?>
	<?php do_action( 'waboot/article/meta' ); ?>
    <?php if(has_post_thumbnail()) : ?>
        <figure class="article__image">
            <?php
            $large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
            echo '<a href="' . $large_image_url[0] . '" title="' . the_title_attribute( 'echo=0' ) . '">';
            echo get_the_post_thumbnail( get_the_ID(), 'large', array( 'class' => 'img-responsive', 'title' => "" ) );
            echo '</a>';
            ?>
        </figure>
    <?php endif ?>
    <div class="article__content">
        <?php the_content(); ?>
    </div>
<?php else : ?>
    <?php the_content(); ?>
<?php endif;

$args = array(
    'post_type'              => array('post'),
    'posts_per_page'         => 3,
    'post__not_in'           => array(get_queried_object_id()),
);
$related = new WP_Query( $args );

if($related->have_posts()) : ?>
    <section class="related">
        <h2><?php _e('Ti potrebbe interessare anche:', LANG_TEXTDOMAIN); ?></h2>

        <div class="<?php echo apply_filters('waboot/layout/posts_wrapper/class','article__grid article__grid--related'); ?>">
            <?php while($related->have_posts()) : $related->the_post(); ?>
                <?php get_template_part( '/templates/parts/content' ); ?>
            <?php endwhile; wp_reset_query(); ?>
        </div>
    </section>
<?php endif;
