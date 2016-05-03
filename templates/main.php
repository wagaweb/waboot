<?php
/*
 * Waboot View
 */
?>
<main id="main" class="site-main" role="main">
	<?php do_action("waboot/main/before"); ?>
	<?php Waboot()->layout->do_zone_action($name); ?>
	<?php do_action("waboot/main/after"); ?>
</main><!-- #main -->