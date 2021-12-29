<?php if (
    is_active_sidebar( 'aside')
    // || is_shop()
): ?>
<aside class="main__aside" role="complementary" data-zone="aside">
    <div class="aside__wrapper">
        <?php do_action('waboot/layout/aside'); ?>
    </div>
</aside>
<?php endif; ?>
