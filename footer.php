			<!-- BEGIN: footer -->
			<div id="site-footer" class="site-footer" data-zone="footer">
                <?php \Waboot\template_tags\render_zone("footer"); ?>
                <?php do_action("waboot/footer"); ?>
			</div>
			<!-- END: footer -->
			<?php wp_footer(); ?>
		</div><!-- END: page-inner -->
	</div><!-- END: page-wrapper -->
    <?php \Waboot\template_tags\render_zone("page-after"); ?>
	</body>
</html>