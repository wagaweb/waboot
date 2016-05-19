		<footer class="container">
			<div class="row">
				Waboot Footer
				<?php Waboot()->layout->render_zone("footer"); ?>
				<?php do_action("waboot/footer"); ?>
			</div>
		</footer>
		<?php wp_footer(); ?>
	</body>
</html>