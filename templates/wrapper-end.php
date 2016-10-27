			<?php do_action("waboot/main/after"); ?>
			</div><!-- .content-inner -->
			</main><!-- #main -->
			<!-- sidebars: BEGIN -->
			<?php get_sidebar(); ?>
			<!-- sidebars: END -->
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