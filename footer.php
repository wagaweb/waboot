			<!-- BEGIN: footer -->
			<footer id="footer-wrapper" class="footer-wrapper" data-zone="footer">
				<div class="footer-inner">
					<?php WabootLayout()->render_zone("footer"); ?>
					<?php do_action("waboot/footer"); ?>
				</div>
			</footer>
			<!-- END: footer -->
			<?php wp_footer(); ?>
		</div><!-- END: page-inner -->
	</div><!-- END: page-wrapper -->
    <?php if(function_exists("Waboot")) WabootLayout()->render_zone("page-after"); ?>
	</body>
</html>