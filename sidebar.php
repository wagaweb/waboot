<?php if ( is_active_sidebar( 'blog-sidebar' ) && ( is_single() || is_home() || is_archive() ) ): ?>
<aside class="main__aside" role="complementary" data-zone="aside">
    <div class="aside__wrapper">
        <?php do_action('waboot/layout/aside'); ?>
    </div>
</aside>
<?php endif; ?>
