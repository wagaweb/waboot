<?php if(is_singular()) : ?>
    <h1><?php the_title(); ?></h1>
    <?php echo do_shortcode( '[audio]' ); ?>
    <?php the_content(); ?>
    <?php do_action( 'waboot/article/footer' ); ?>
<?php else : ?>
    <article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <div class="article__inner">
            <div class="article__content">
                <h2><?php the_title(); ?></h2>
                <?php echo do_shortcode( '[audio]' ); ?>
                <p><?php \Waboot\inc\trimmedExcerpt(20, '...'); ?> <a class="more__link" href="<?php the_permalink() ?>"><?php _e('Continue reading', LANG_TEXTDOMAIN) ?></a></p>
                <?php do_action( 'waboot/article/list/footer' ); ?>
            </div>
        </div>
    </article>
<?php endif; ?>
