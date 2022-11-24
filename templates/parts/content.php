<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="article__inner">
        <?php if(has_post_thumbnail()) : ?>
        <figure class="article__image">
            <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', LANG_TEXTDOMAIN ), the_title_attribute( 'echo=0' ) ); ?>">
                <?php echo get_the_post_thumbnail( $post->ID, 'large') ?>
            </a>
        </figure>
        <?php endif; ?>
        <div class="article__content">
            <?php 
            $categories = get_the_category(); 
            if($categories) : ?>
                <ul class="article__categories">
                    <?php foreach ($categories as $category) : ?>
                        <li><a href="<?php echo get_category_link($category->term_id); ?>"><?php echo $category->name; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            
            <span class="article__date"><?php echo get_the_date(); ?></span>
            <p>
                <?php \Waboot\inc\trimmedExcerpt(20, '...'); ?>
            </p>
            <a class="more__link" href="<?php the_permalink() ?>">
                <?php _e('Continue reading', LANG_TEXTDOMAIN) ?>
            </a>
            
        </div>
    </div>
</article>
