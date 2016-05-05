<?php
/*
 * Waboot View
 */
?>
<footer class="container">
	<div class="row">
		Waboot Footer
		<?php Waboot()->layout->do_zone_action($name); ?>
		<?php do_action("waboot/footer"); ?>
	</div>
</footer>