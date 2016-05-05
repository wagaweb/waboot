<?php
/*
 * Waboot View
 */
?>
<div class="<?php echo $widget_area_prefix; ?>-sidebar-row row">
	<?php do_action("waboot/widget_area/top"); ?>
	<?php do_action("waboot/widget_area/{$widget_area_prefix}/top"); ?>
	<?php for($i=0;$i<=$widget_count;$i++): ?>
		<aside id="<?php echo $widget_area_prefix; ?>-sidebar-1" class="sidebar widget <?php echo $sidebar_class; ?>">
			<?php dynamic_sidebar( $widget_area_prefix.'-'.$i ); ?>
		</aside>
	<?php endfor; ?>
	<?php do_action("waboot/widget_area/bottom"); ?>
	<?php do_action("waboot/widget_area/{$widget_area_prefix}/bottom"); ?>
</div>