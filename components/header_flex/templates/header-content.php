<div class="header__logo">
    <a href="<?php echo home_url( '/' ); ?>">
        <?php if ( \Waboot\template_tags\get_desktop_logo() != "" ) : ?>
            <?php \Waboot\template_tags\desktop_logo(); ?>
        <?php else : ?>
            <?php \Waboot\template_tags\site_title(); ?>
        <?php endif; ?>
    </a>
</div>