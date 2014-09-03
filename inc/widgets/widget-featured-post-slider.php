<?php

class Waboot_Feaured_Post_Slider extends WP_Widget{

    function __construct() {
        parent::__construct('waboot_featured_post_slider',
            __( 'Waboot: Post Slider', 'waboot' ),
            array(
                'description' => __( 'Featured posts displayed in a slider', 'waboot' )
            )
        );
    }

    function widget( $args, $instance ) {
        global $post,$wp_query;
        extract( $args );

        $featured_query = new WP_Query( array(
            'tag_id'         => $instance['tag'],
            'posts_per_page' => $instance['maxnum'],
        ));

        /**
         * Show or hide featured posts in the main post index
         * todo: vedere se è implementabile
         */
        // Do not duplicate featured posts in the post index
        /*if ( of_get_option( 'waboot_featured_posts_show_dupes' ) == "0" ) {
            global $wp_query;
            $wp_query->set( 'tag__not_in', array( of_get_option( 'waboot_featured_posts_tag' ) ) );
            $wp_query->get_posts();
        }*/

        // Duplicate featured posts in the post index
        /*if ( of_get_option( 'waboot_featured_posts_show_dupes' ) == "1" ) {
            global $wp_query;
            $wp_query->set( 'post_status', 'publish' );
            $wp_query->get_posts();
        }*/

        if ( $featured_query->have_posts() ) : ?>
            <div class="row">
                <div class="col-sm-12">
                    <div id="featured-carousel" class="carousel slide">

                        <?php if ( $instance['show_indicators'] == "on" ) : ?>
                            <ol class="carousel-indicators">
                                <?php
                                $indicators = $featured_query->post_count;
                                $count = 0;
                                while ( $count != $indicators ) {
                                    echo '<li data-target="#featured-carousel" data-slide-to="' . $count . '"></li>';
                                    $count++;
                                }
                                ?>
                            </ol>
                        <?php endif; ?>

                        <div class="carousel-inner">
                            <?php $temp_query = clone $wp_query; ?>
                            <?php while ( $featured_query->have_posts() ) : $featured_query->the_post(); ?>
                                <div class="item">
                                    <a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>">
                                        <?php echo get_the_post_thumbnail( ''. $post->ID .'', array( $instance['images_width'], $instance['images_height'] ), array( 'title' => "" ) ); ?>
                                    </a>
                                    <?php // Featured post captions?
                                    if ( $instance['show_captions'] == "on" ) { ?>
                                        <div class="carousel-caption">
                                            <h3><?php the_title(); ?></h3>
                                        </div><!-- .carousel-caption -->
                                    <?php } ?>
                                </div><!-- .item -->
                            <?php endwhile; ?>
                            <?php $wp_query = clone $temp_query; ?>
                        </div><!-- .carousel-inner -->
                        <a class="left carousel-control" href="#featured-carousel" data-slide="prev"><span class="icon-prev"></span></a>
                        <a class="right carousel-control" href="#featured-carousel" data-slide="next"><span class="icon-next"></span></a>
                    </div><!-- #featured-carousel -->
                </div><!-- .col-sm-12 -->
            </div><!-- .row -->

            <script type="text/javascript">
                jQuery(function() {
                    // Activate the first carousel item //
                    jQuery("div.item:first").addClass("active");
                    jQuery("ol.carousel-indicators").children("li:first").addClass("active");
                    // Start the Carousel //
                    jQuery('.carousel').carousel();
                });
            </script>
        <?php endif; // if(have_posts())
    }

    function form( $instance ) {
        $defaults = array(
            'tag'    => '',
            'maxnum' => '5',
            'show_indicators' => 'on',
            'show_captions' => 'on',
            'images_width' => '850',
            'images_height' => '350'
        );

        $instance = wp_parse_args( (array) $instance, $defaults);

        // Pull all the tags into an array
        $all_tags = array();
        $all_tags_obj = get_tags( array('hide_empty' => false) );
        foreach ($all_tags_obj as $tag) {
            $all_tags[$tag->term_id] = $tag->name;
        }

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'tag' ); ?>"><?php _e( 'Featured Posts Tag:', 'waboot' ); ?></label>
            <select id="<?php echo $this->get_field_id( 'tag' ); ?>" name="<?php echo $this->get_field_name( 'tag' ); ?>" class="select">
                <option value="" <?php selected( $instance['tag'], '' ); ?>><?php _e( 'Select a tag:', 'waboot' ); ?></option>
                <?php foreach($all_tags as $k=>$v) : ?>
                    <option value="<?php echo $k ?>" <?php selected( $instance['tag'], $k ); ?>><?php echo $v ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'maxnum' ); ?>"><?php _e( 'Maximum # of Featured Posts to display', 'waboot' ); ?></label>
            <input type="text" class="widefat code" id="<?php echo $this->get_field_id( 'maxnum' ); ?>" name="<?php echo $this->get_field_name( 'maxnum' ); ?>" value="<?php echo $instance['maxnum']; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'images_width' ); ?>"><?php _e( 'Featured post image width', 'waboot' ); ?></label>
            <input type="text" class="widefat code" id="<?php echo $this->get_field_id( 'images_width' ); ?>" name="<?php echo $this->get_field_name( 'images_width' ); ?>" value="<?php echo $instance['images_width']; ?>" />
            <label for="<?php echo $this->get_field_id( 'images_height' ); ?>"><?php _e( 'Featured post image height', 'waboot' ); ?></label>
            <input type="text" class="widefat code" id="<?php echo $this->get_field_id( 'images_height' ); ?>" name="<?php echo $this->get_field_name( 'images_height' ); ?>" value="<?php echo $instance['images_height']; ?>" />
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_indicators'], 'on' ); ?> id="<?php echo $this->get_field_id( 'show_indicators' ); ?>" name="<?php echo $this->get_field_name( 'show_indicators' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'show_indicators' ); ?>"><?php _e('Display slider indicators?', 'waboot'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_captions'], 'on' ); ?> id="<?php echo $this->get_field_id( 'show_captions' ); ?>" name="<?php echo $this->get_field_name( 'show_captions' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'show_captions' ); ?>"><?php _e('Show post titles as captions with slider images?', 'waboot'); ?></label>
        </p>
        <?php
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['tag'] = strip_tags( $new_instance['tag'] );
        $instance['maxnum'] = strip_tags( $new_instance['maxnum'] );
        $instance['show_indicators'] = strip_tags( $new_instance['show_indicators'] );
        $instance['show_captions'] = strip_tags( $new_instance['show_captions'] );
        $instance['images_width'] = strip_tags( $new_instance['images_width'] );
        $instance['images_height'] = strip_tags( $new_instance['images_height'] );

        return $instance;
    }

}