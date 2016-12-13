<div id="header-splitted-wrapper" class="header-splitted-wrapper">
    <div class="header-splitted-inner">
        <!-- Header splitted -->

        <nav class="navbar navbar-default main-navigation">
            <!-- Main Nav -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                    <span class="sr-only"><?php _e("Toggle navigation","waboot"); ?></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <?php if(\Waboot\template_tags\get_desktop_logo() != ""): ?>
                    <a class="navbar-brand" href="<?php echo home_url( '/' ); ?>">
                        <?php \Waboot\template_tags\desktop_logo(); ?>
                    </a>
                <?php else : ?>
                    <?php get_bloginfo("title"); ?>
                <?php endif; ?>
            </div>

            <div class="collapse navbar-collapse navbar-main-collapse">
                <?php wp_nav_menu([
                    'theme_location' => 'main',
                    'items_wrap' => '<div id="%1$s">%3$s</div>',
                    'container' => false,
                    'menu_class' => apply_filters('waboot/navigation/main/class', 'splitted-nav'),
                    'walker' => $walker
                ]); ?>
            </div>
        </nav>
        <!-- End Main Nav -->
        <!-- End Header splitted -->
    </div>
</div><!-- #header-wrapper -->