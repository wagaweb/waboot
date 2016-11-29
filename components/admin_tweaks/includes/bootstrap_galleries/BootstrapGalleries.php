<?php

/**
 * Class BootstrapGalleries
 *
 * Wraps the content of a WordPress media gallery in a Twitter's Bootstrap grid
 */
class BootstrapGalleries {

	static public function media_bootstrap_galleries($output, $attr) {
		$post = get_post();
		static $instance = 0;
		$instance++;
		if ( ! empty( $attr['ids'] ) ) {
			// 'ids' is explicitly ordered, unless you specify otherwise.
			if ( empty( $attr['orderby'] ) )
				$attr['orderby'] = 'post__in';
			$attr['include'] = $attr['ids'];
		}

		// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
		if ( isset( $attr['orderby'] ) ) {
			$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
			if ( !$attr['orderby'] )
				unset( $attr['orderby'] );
		}
		extract(shortcode_atts(array(
			'order'      => 'ASC',
			'orderby'    => 'menu_order ID',
			'id'         => $post ? $post->ID : 0,
			'itemtag'    => 'dl',
			'icontag'    => 'dt',
			'captiontag' => 'dd',
			'columns'    => 3,
			'size'       => 'thumbnail',
			'include'    => '',
			'exclude'    => '',
			'link'       => ''
		), $attr, 'gallery'));
		$id = intval($id);
		if ( 'RAND' == $order )
			$orderby = 'none';
		if ( !empty($include) ) {
			$_attachments = get_posts( array('include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
			$attachments = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[$val->ID] = $_attachments[$key];
			}
		} elseif ( !empty($exclude) ) {
			$attachments = get_children( array('post_parent' => $id, 'exclude' => $exclude, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
		} else {
			$attachments = get_children( array('post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $order, 'orderby' => $orderby) );
		}
		if ( empty($attachments) )
			return '';
		if ( is_feed() ) {
			$output = "\n";
			foreach ( $attachments as $att_id => $attachment )
				$output .= wp_get_attachment_link($att_id, $size, true) . "\n";
			return $output;
		}
		$itemtag = tag_escape($itemtag);
		$captiontag = tag_escape($captiontag);
		$icontag = tag_escape($icontag);
		$valid_tags = wp_kses_allowed_html( 'post' );
		if ( ! isset( $valid_tags[ $itemtag ] ) )
			$itemtag = 'dl';
		if ( ! isset( $valid_tags[ $captiontag ] ) )
			$captiontag = 'dd';
		if ( ! isset( $valid_tags[ $icontag ] ) )
			$icontag = 'dt';
		$columns = intval($columns);
		$itemwidth = $columns > 0 ? floor(100/$columns) : 100;
		$float = is_rtl() ? 'right' : 'left';
		$selector = "gallery-{$instance}";
		$gallery_style = $gallery_div = '';
		if ( apply_filters( 'use_default_gallery_style', true ) )
			$gallery_style = "
		<style type='text/css'>
			#{$selector} {
				margin: auto;
			}
			#{$selector} .gallery-item {
				margin-top: 10px;
				text-align: center;
			}
			#{$selector} img {
				border: 2px solid #cfcfcf;
			}
			#{$selector} .gallery-caption {
				margin-left: 0;
			}
			/* see gallery_shortcode() in wp-includes/media.php */
		</style>";
		$size_class = sanitize_html_class( $size );
		$gallery_div = "<div id='$selector' class='row gallery galleryid-{$id} gallery-size-{$size_class}'>";
		$output = apply_filters( 'gallery_style', $gallery_style . "\n\t\t" . $gallery_div );
		$i = 1;
		$classes = self::bootstrap_col_classes($columns);

		foreach ( $attachments as $id => $attachment ) {
			if ( ! empty( $link ) && 'file' === $link )
				$image_output = wp_get_attachment_link( $id, $size, false, false );
			elseif ( ! empty( $link ) && 'none' === $link )
				$image_output = wp_get_attachment_image( $id, $size, false );
			else
				$image_output = wp_get_attachment_link( $id, $size, true, false );

			$image_output = preg_replace('/height="[0-9]+"/','',$image_output);
			$image_output = preg_replace('/width="[0-9]+"/','',$image_output);
			$image_output = str_replace('class="', 'class="img-responsive ', $image_output);
			$image_meta  = wp_get_attachment_metadata( $id );
			$orientation = '';
			if ( isset( $image_meta['height'], $image_meta['width'] ) )
				$orientation = ( $image_meta['height'] > $image_meta['width'] ) ? 'portrait' : 'landscape';
			$output .= "<div class='gallery-item ".$classes."'>";
			$output .= "
			<div class='gallery-icon {$orientation}'>
				$image_output
			</div>";
			if ( $captiontag && trim($attachment->post_excerpt) ) {
				$output .= "
				<{$captiontag} class='wp-caption-text gallery-caption'>
				" . wptexturize($attachment->post_excerpt) . "
				</{$captiontag}>";
			}
			$output .= "</div>";
			/*if($columns == 6)
			{
				if(0 == ($i % 6)){$output .= '<div class="clearfix visible-md visible-lg"></div>'; }
				if(0 == ($i % 4)){$output .= '<div class="clearfix visible-sm"></div>'; }
				if(0 == ($i % 2)){$output .= '<div class="clearfix visible-xs"></div>'; }
			}
			elseif($columns == 4)
			{
				if(0 == ($i % 4)){$output .= '<div class="clearfix visible-md visible-lg"></div>'; }
				if(0 == ($i % 2)){$output .= '<div class="clearfix visible-sm"></div>'; }
			}
			elseif($columns == 3)
			{
				if(0 == ($i % 3)){$output .= '<div class="clearfix visible-md visible-lg"></div>'; }
			}
			elseif($columns == 31)
			{
				if(0 == ($i % 3)){$output .= '<div class="clearfix visible-md visible-lg"></div>'; }
				if(0 == ($i % 2)){$output .= '<div class="clearfix visible-sm"></div>'; }
			}
			elseif($columns == 2)
			{
				if(0 == ($i % 2)){$output .= '<div class="clearfix invisible-xs"></div>'; }
			}
			$i++;*/
		}
		$output .= "
		</div>\n";
		return $output;
	}

	static public function bootstrap_col_classes($number_of_columns) {
		switch($number_of_columns){

			case 6: $classes = 'col-xs-6 col-sm-3 col-md-2'; break;
			case 4: $classes = 'col-xs-12 col-sm-6 col-md-3'; break;
			case 3: $classes = 'col-xs-12 col-sm-12 col-md-4'; break;
			case 31: $classes = 'col-xs-12 col-sm-6 col-md-4'; break;
			case 2: $classes = 'col-xs-12 col-sm-6 col-md-6'; break;
			default: $classes = 'col-xs-12 col-sm-12 col-md-12';
		}
		return $classes;
	}
}