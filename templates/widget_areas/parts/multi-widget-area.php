<div class="<?php echo $widget_area_prefix; ?>__row">
    <?php do_action("waboot/widget_area/before"); ?>
    <?php do_action("waboot/widget_area/{$widget_area_prefix}/before"); ?>
    <?php for($i=1;$i<=$widget_count;$i++): ?>
        <aside class="<?php echo $widget_area_prefix; ?>__aside--<?php echo $i; ?> widget">
            <?php dynamic_sidebar( $widget_area_prefix.'-'.$i ); ?>
        </aside>
    <?php endfor; ?>
    <?php do_action("waboot/widget_area/after"); ?>
    <?php do_action("waboot/widget_area/{$widget_area_prefix}/after"); ?>
</div>