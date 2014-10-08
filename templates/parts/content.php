<?php
/**
 * @package Waboot
 * @since Waboot 1.0
 */

do_action( 'waboot_post_before' ); ?>
    <article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <?php
        do_action( 'waboot_entry_content_before' );
        ?>


        <?php if(is_singular()) : ?>

            <?php do_action( 'waboot_entry_header' ); ?>

            <div class="entry-content row">

                <?php if(has_post_thumbnail()) : ?>

                    <div class="entry-image col-sm-12">
                        <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>">
                            <?php echo get_the_post_thumbnail( $post->ID, 'large', array( 'class' => 'img-responsive', 'title' => "" ) ); ?>
                        </a>
                    </div>

                <?php endif ?>

                <div class="col-sm-12">
                    <?php
                    the_content();
                    wp_link_pages();
                    do_action( 'waboot_entry_footer' );
                    ?>
                </div>

            </div><!-- .entry-content -->

        <?php else : ?>

            <div class="entry-content row">

                <?php if(has_post_thumbnail()) : ?>

                    <div class="col-md-8 pull-right-md">
                        <?php
                        do_action( 'waboot_entry_header' );
                        ?>
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
                        do_action( 'waboot_entry_footer' );
                        ?>
                    </div>

                <?php else : ?>

                    <div class="col-sm-12">
                        <?php
                        do_action( 'waboot_entry_header' );
                        the_excerpt();
                        wp_link_pages();
                        do_action( 'waboot_entry_footer' );
                        ?>
                    </div>

                <?php endif ?>

            </div><!-- .entry-content -->

        <?php endif ?>


        <?php
        do_action( 'waboot_entry_content_after' );
        /* do_action( 'waboot_entry_footer' ); */
        do_action( 'waboot_post_bottom' );
        ?>

    </article>
    <!-- #post-<?php the_ID(); ?> -->
<?php do_action( 'waboot_post_after' ); ?>