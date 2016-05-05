<?php
/*
 * Waboot View
 */
?>
<header class="container">
	<div class="row">
		Waboot Header
		<?php Waboot()->layout->do_zone_action($name); ?>
		<?php do_action("waboot/header"); ?>
	</div>
</header>