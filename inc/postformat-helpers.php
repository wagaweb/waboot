<?php

/* Video all'interno del content ma in un post che non è post format video */
function waboot_video_embed_html($video) {
    return "<div class='wb-video-container'>{$video}</div>";
}
add_filter( 'embed_oembed_html', 'waboot_video_embed_html' );

/* Post Format Gallery */
if ( ! function_exists('waboot_gallery_format')) :
    function waboot_gallery_format() {
        $output = $images_ids = '';

        if ( function_exists( 'get_post_galleries' ) ) {
            $galleries = get_post_galleries( get_the_ID(), false );

            if ( empty( $galleries ) ) return false;

            if ( isset( $galleries[0]['ids'] ) ) {
                foreach ( $galleries as $gallery ) {
                    // Grabs all attachments ids from one or multiple galleries in the post
                    $images_ids .= ( '' !== $images_ids ? ',' : '' ) . $gallery['ids'];
                }

                $attachments_ids = explode( ',', $images_ids );
                // Removes duplicate attachments ids
                $attachments_ids = array_unique( $attachments_ids );
            } else {
                $attachments_ids = get_posts( array(
                    'fields'         => 'ids',
                    'numberposts'    => 999,
                    'order'          => 'ASC',
                    'orderby'        => 'menu_order',
                    'post_mime_type' => 'image',
                    'post_parent'    => get_the_ID(),
                    'post_type'      => 'attachment',
                ) );
            }
        } else {
            $pattern = get_shortcode_regex();
            preg_match( "/$pattern/s", get_the_content(), $match );
            $atts = shortcode_parse_atts( $match[3] );

            if ( isset( $atts['ids'] ) )
                $attachments_ids = explode( ',', $atts['ids'] );
            else
                return false;
        }

        echo '<div id="carousel-gallery-format" class="carousel slide" data-ride="carousel">';
        echo '	<div class="carousel-inner" role="listbox">';
        $i = 0;
        foreach ( $attachments_ids as $attachment_id ) {
            if($i == 0){
                printf( '<div class="item active">%s</div>',
                    // esc_url( get_permalink() ),
                    wp_get_attachment_image( $attachment_id, 'medium' )
                );
            }else{
                printf( '<div class="item">%s</div>',
                    // esc_url( get_permalink() ),
                    wp_get_attachment_image( $attachment_id, 'medium' )
                );
            }
            $i++;
        }
        echo '	</div> <!-- .carousel-inner -->';
        echo '<!-- Controls -->
              <a class="left carousel-control" href="#carousel-gallery-format" role="button" data-slide="prev">
                <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
              </a>
              <a class="right carousel-control" href="#carousel-gallery-format" role="button" data-slide="next">
                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
              </a>
        ';
        echo '</div> <!-- #carousel-gallery-format -->';

        return $output;
    }
endif;
/* End Post Format Gallery */



/* Post Format Video */
if(!function_exists('waboot_get_first_video' )):
    function waboot_get_first_video() {
        $first_oembed  = '';
        $custom_fields = get_post_custom();

        foreach ( $custom_fields as $key => $custom_field ) {
            if ( 0 !== strpos( $key, '_oembed_' ) ) continue;
            if ( $custom_field[0] == '{{unknown}}' ) continue;

            $first_oembed = $custom_field[0];

            $video_width  = (int) apply_filters( 'wb_video_width', 100 );
            $video_height = (int) apply_filters( 'wb_video_height', 480 );

            $first_oembed = preg_replace( '/<embed /', '<embed wmode="transparent" ', $first_oembed );
            $first_oembed = preg_replace( '/<\/object>/','<param name="wmode" value="transparent" /></object>', $first_oembed );

            $first_oembed = preg_replace( "/width=\"[0-9]*\"/", "width={$video_width}%", $first_oembed );
            $first_oembed = preg_replace( "/height=\"[0-9]*\"/", "height={$video_height}", $first_oembed );

            break;
        }

        return ( '' !== $first_oembed ) ? $first_oembed : false;
    }
endif;
/* End Post Format Video */


/* Post Format Link */
if ( ! function_exists('waboot_link_format_helper')) :
    /**
     * Returns the first post link and/or post content without the link.
     * Used for the "Link" post format.
     *
     * @since 0.1.0
     * @param string $output "link" or "post_content"
     * @return string Link or Post Content without link.
     */
    function waboot_link_format_helper( $output = false ) {

        if ( ! $output )
            _doing_it_wrong( __FUNCTION__, __( 'You must specify the output you want - either "link" or "post_content".', 'waboot' ), '1.0.1' );

        $post_content = get_the_content();

        $link = preg_match( '/<a\s[^>]*?href=[\'"](.+?)[\'"][^>]*>[^>]*>/is', $post_content, $matches );
        if($link){
            $link_url = $matches[1];
            $post_content = substr( $post_content, strlen( $matches[0] ) );
            if(!$post_content) $post_content = "";
        }

        // Return the first link in the post content
        if ( 'link' == $output ){
            if($link){
                return $link_url;
            }else{
                return "";
            }
        }

        // Return the post content without the first link
        if ( 'post_content' == $output )
            return $post_content;
    }
endif;
/* End Post Format Link */