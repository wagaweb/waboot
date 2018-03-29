<div class="footer-flex">
<div class="<?php echo $footer_width ?>">
    <div class="footer-widgetarea-wrap">
        <?php do_action('waboot/component/footer-flex/before_widgets')?>

        <?php if ( is_active_sidebar( 'footer-flex-1' ) ) : ?>
            <div class="footer-widgetarea footer-widgetarea-1">
                <?php dynamic_sidebar( 'footer-flex-1' ); ?>
            </div>
        <?php endif; ?>
        <?php if ( is_active_sidebar( 'footer-flex-2' ) ) : ?>
            <div class="footer-widgetarea footer-widgetarea-2">
                <?php dynamic_sidebar( 'footer-flex-2' ); ?>
            </div>
        <?php endif; ?>
        <?php if ( is_active_sidebar( 'footer-flex-3' ) ) : ?>
            <div class="footer-widgetarea footer-widgetarea-3">
                <?php dynamic_sidebar( 'footer-flex-3' ); ?>
            </div>
        <?php endif; ?>
        <?php if ( is_active_sidebar( 'footer-flex-4' ) ) : ?>
            <div class="footer-widgetarea footer-widgetarea-4">
                <?php dynamic_sidebar( 'footer-flex-4' ); ?>
            </div>
        <?php endif; ?>

        <?php do_action('waboot/component/footer-flex/after_widgets')?>
    </div>
</div>
</div>
<div data-cookieonly class="closure site-footer " id="colophon" role="contentinfo">
    <?php echo $closure_content; ?>
</div><!-- .closure -->