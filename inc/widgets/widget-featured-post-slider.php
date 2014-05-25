<?php

class Waboot_Feaured_Post_Slider extends WP_Widget{

    function __construct() {
        parent::__construct('waboot_featured_post_slider',
            __( 'Post Slider', 'waboot' ),
            array(
                'description' => __( 'Featured posts displayed in a slider', 'waboot' )
            )
        );
    }

    function widget( $args, $instance ) {

        extract( $args );

        $featured_query = new WP_Query( array(
            'tag_id'         => $instance['tag'],
            'posts_per_page' => $instance['maxnum'],
        ));

        if ( $featured_query->have_posts() ) : ?>
            <div class="row">
                <div class="col-sm-12">
                    <div id="featured-carousel" class="carousel slide">

                        <?php if ( $instance['show_indicators'] ) : ?>
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
                            <?php while ( $featured_query->have_posts() ) : $featured_query->the_post();
                                get_template_part( '/templates/parts/content', 'featured' );
                            endwhile; ?>
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
            'show_indicators' => 'on'
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
            <input class="checkbox" type="checkbox" <?php checked( $instance['show_indicators'], 'on' ); ?> id="<?php echo $this->get_field_id( 'show_indicators' ); ?>" name="<?php echo $this->get_field_name( 'show_indicators' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'show_indicators' ); ?>"><?php _e('Display slider indicators?', 'waboot'); ?></label>
        </p>
        <?php
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        $instance['tag'] = strip_tags( $new_instance['tag'] );
        $instance['maxnum'] = strip_tags( $new_instance['maxnum'] );
        $instance['show_indicators'] = strip_tags( $new_instance['show_indicators'] );

        return $instance;
    }

}