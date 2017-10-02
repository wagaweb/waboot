<article role="article" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<?php do_action( 'waboot/entry/header' ); ?>
	<div class="entry-content row">
		<?php if(has_post_thumbnail()) : ?>
			<div class="entry-image col-sm-12">
				<?php
				$large_image_url = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' );
				echo '<a href="' . $large_image_url[0] . '" title="' . the_title_attribute( 'echo=0' ) . '">';
				echo get_the_post_thumbnail( $post->ID, 'large', array( 'class' => 'img-responsive', 'title' => "" ) );
				echo '</a>';
				?>
			</div>
		<?php endif ?>
		<div class="col-sm-12">
			<?php
			the_content();
			wp_link_pages();
			?>
			<?php do_action( 'waboot/entry/footer' ); ?>
		</div>
	</div><!-- .entry-content -->
</article>
<!-- #post-<?php the_ID(); ?> -->