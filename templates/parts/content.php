<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="article__inner">
        <?php if(has_post_thumbnail()) : ?>
        <figure class="article__image">
            <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', LANG_TEXTDOMAIN ), the_title_attribute( 'echo=0' ) ); ?>">
                <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail') ?>
            </a>
        </figure>
        <?php endif; ?>
        <div class="article__content">
            <div class="article__categories">
              <svg
                  width="18"
                  height="18"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="1.5"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                >
                  <use href="<?php echo get_template_directory_uri() ?>/assets/images/default/icons/feather-sprite.svg#folder"/>
              </svg>

              <span><?php the_category( ',' ); ?></span>
            </div>

            <h2>
              <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', LANG_TEXTDOMAIN ), the_title_attribute( 'echo=0' ) ); ?>">
                <?php the_title(); ?>
              </a>
            </h2>
            
            <p>
                <?php \Waboot\inc\trimmedExcerpt(20, '...'); ?>
                <a class="more__link" href="<?php the_permalink() ?>">
                    <?php _e('Continue reading', LANG_TEXTDOMAIN) ?>
                </a>
            </p>
            <?php //do_action( 'waboot/article/list/footer' ); ?>

            <div class="article__footer">
              <span class="published-date">
                  <svg
                      width="18"
                      height="18"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="1.5"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                  >
                      <use href="<?php echo get_template_directory_uri() ?>/assets/images/default/icons/feather-sprite.svg#calendar"/>
                  </svg>
                  <time class="entry__date" datetime="<?php echo $tag_date; ?>"><?php echo get_the_date( 'j M'); ?></time>
              </span>

              <span class="comments-link">
                  <svg
                          width="18"
                          height="18"
                          fill="none"
                          stroke="currentColor"
                          stroke-width="1.5"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                      >
                          <use href="<?php echo get_template_directory_uri() ?>/assets/images/default/icons/feather-sprite.svg#message-square"/>
                  </svg>
                  
                  <?php comments_popup_link( __( ' 0', LANG_TEXTDOMAIN ), __( ' 1 Comment', LANG_TEXTDOMAIN ), __( ' %', LANG_TEXTDOMAIN ) ); ?>
              </span>
            </div>
        </div>
    </div>
</article>
