<div class="entry-image col-sm-4 ">
	<?php
		$thumb_preset = apply_filters('waboot/layout/entry/thumbnail/preset','thumbnail');
		$thumb_classes = apply_filters('waboot/layout/entry/thumbnail/class','img-responsive');
	?>
	<a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__( 'Link to %s', 'waboot' ), the_title_attribute( 'echo=0' ) ); ?>">
		<?php echo get_the_post_thumbnail( $post->ID, $thumb_preset, array( 'class' => $thumb_classes, 'title' => "" ) ); ?>
	</a>
</div>