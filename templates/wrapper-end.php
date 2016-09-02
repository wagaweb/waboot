				<?php
				/*
				 * sidebars
				 */
				?>
				<?php get_sidebar(); ?>
			</div><!-- .row -->
		</div><!-- site-main -->
	<?php do_action("waboot/site-main/after"); ?>
	<?php
	/*
	 * main-bottom zone
	 */
	Waboot()->layout->render_zone("main-bottom");
	?>
	</div><!-- .main-inner -->
</div><!-- #main-wrapper -->