		<!-- BEGIN: footer -->
		<footer id="footer-wrapper" class="footer-wrapper" data-zone="footer">
			<div class="footer-inner">
				<?php Waboot()->layout->render_zone("footer"); ?>
				<?php do_action("waboot/footer"); ?>
			</div>
		</footer>
		<!-- END: footer -->
		<?php wp_footer(); ?>
	</div><!-- END: page -->
	</body>
</html>