<div id="header-splitted-wrapper" class="header-splitted-wrapper">
    <div class="header-splitted-inner">
        <!-- Header splitted -->

        <div id="logo" class="hidden-sm hidden-xs">
            <?php if ( \Waboot\template_tags\get_desktop_logo() != "" ) : ?>
                <?php \Waboot\template_tags\desktop_logo(true); ?>
            <?php else : ?>
                <?php
                \Waboot\template_tags\site_title();
                \Waboot\template_tags\site_description();
                ?>
            <?php endif; ?>
        </div>


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
                    'depth' => 0,
                    'container' => false,
                    'menu_class' => apply_filters('waboot/navigation/main/class', 'navbar-nav'),
                    'walker' => class_exists('WabootNavMenuWalker') ? new WabootNavMenuWalker() : "",
                    'fallback_cb' => 'waboot_nav_menu_fallback'
                ]);
                ?>


                <!--<ul>
                    <?php /*
                    $menu_items = wp_get_nav_menu_items('splitted');
                    $count = count($menu_items);
                    $i = 1;
                    foreach ($menu_items as $menu_item) {
                        ?>
                        <li <?php if ($i==floor($count/2)) { ?>
                            class="split"<?php } ?>
                        ><?php echo $menu_item->title; ?></li>
                        <?php $i++; ?>
                    <?php } */ ?>

                </ul>-->

            </div>
        </nav>
        <!-- End Main Nav -->

        <!-- End Header splitted -->
    </div>
</div><!-- #header-wrapper -->


<div class="row">
    <div class="col-sm-6">
        <div class="well">
            ciao
        </div>
    </div>
    <div class="col-sm-6">
        <div class="well">
            ciao
        </div>
    </div>
</div>