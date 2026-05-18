<div class="header__inner">

    <div class="header__logo">

        <a href="<?php echo home_url('/'); ?>">
            <?php if (\Waboot\inc\getLogo() !== '') : ?>
                <?php \Waboot\inc\theLogo(false, 'header__logo'); ?>
            <?php else : ?>
                <?php echo get_bloginfo('name'); ?>
            <?php endif; ?>
        </a>

    </div>

    <button type="button" class="header__toggle header__link--nav slidein-nav__toggle" aria-label="<?php esc_attr_e('Apri menu di navigazione', LANG_TEXTDOMAIN); ?>" aria-haspopup="dialog" aria-expanded="false">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
    </button>

    <nav class="header__navigation" aria-label="<?php esc_attr_e('Menu principale', LANG_TEXTDOMAIN); ?>">
        <?php wp_nav_menu([
            'theme_location' => 'main',
            'depth' => 0,
            'fallback_cb' => '__return_false',
            'container' => false,
            'menu_class' => apply_filters('waboot/navigation/main/class', 'navigation navbar-nav')
        ]); ?>
    </nav>

</div>