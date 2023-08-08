<?php if ($options): ?>

    <p style="display: none;" id="sizes-notice"><?php _e('Size', LANG_TEXTDOMAIN); ?></p>

    <ul id="filter-sizes" class="filter-sizes">
        <?php foreach ($options as $v => $k): ?>
            <?php if(!array_key_exists('value',$k)) {
                continue;
            }?>
            <?php // if (!empty($k['in-stock'])): ?>
                <li data-value="<?php echo $v; ?>" data-stock="<?php if (!empty($k['in-stock']) || !array_key_exists('in-stock',$k)){echo 'in-stock';}else{echo 'out-of-stock';};?>"
                    class="filter-sizes__item"><?php echo $k['value']; ?></li>
            <?php // endif; ?>
        <?php endforeach; ?>
    </ul>

<?php endif; ?>
