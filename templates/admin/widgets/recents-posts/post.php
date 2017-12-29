<article role="article" <?php post_class("recent-post row"); ?>>
	<?php if(has_post_thumbnail() && $settings['thumb']) : ?>
		<div class="entry-image col-xs-4 ">
			<a href="<?php the_permalink(); ?>" title="<?php echo $link_title; ?>"><?php echo get_the_post_thumbnail( get_the_ID(), $settings['thumb_size'], ['class' => 'img-responsive'] ); ?></a>
		</div>
		<div class="col-xs-8">
			<header>
				<h4><a href="<?php the_permalink(); ?>" title="<?php echo $link_title; ?>"><?php echo apply_filters("waboot_entry_title_text",get_the_title()); ?></a></h4>
			</header>
			<?php if($footer): ?>
				<footer class="entry-footer">
					<?php echo $footer; ?>
				</footer>
			<?php endif; ?>
			<?php if($excerpt): ?>
				<div class="entry-content">
					<?php echo $excerpt; ?>
				</div>
			<?php endif; ?>
		</div>

	<?php else: ?>
		<div class="col-sm-12">
			<header>
				<h4><a href="<?php the_permalink(); ?>" title="<?php echo $link_title; ?>"><?php echo apply_filters("waboot_entry_title_text",get_the_title()); ?></a></h4>
			</header>
			<?php if($footer): ?>
			<footer class="entry-footer">
				<?php echo $footer; ?>
			</footer>
			<?php endif; ?>
			<?php if($excerpt): ?>
			<div class="entry-content">
				<?php echo $excerpt; ?>
			</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</article>