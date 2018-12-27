			<!-- BEGIN: footer -->
			<div id="site-footer" class="site-footer" data-zone="footer">
                <?php \Waboot\template_tags\render_zone("footer"); ?>
                <?php do_action("waboot/footer"); ?>
			</div>
			<!-- END: footer -->
		</div><!-- END: site-page__wrapper -->
		<?php do_action('waboot/site-page/end'); ?>
	</div><!-- END: site-page -->
    <?php \Waboot\template_tags\render_zone("page-after"); ?>
	<?php wp_footer(); ?>
	</body>
</html>