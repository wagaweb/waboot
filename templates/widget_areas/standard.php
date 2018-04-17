<div class="widgetarea">
	<?php
	do_action("waboot/widget_area/before");
	do_action("waboot/widget_area/{$area_id}/before");
	dynamic_sidebar($area_id);
	do_action("waboot/widget_area/{$area_id}/after");
	do_action("waboot/widget_area/after");
	?>
</div>