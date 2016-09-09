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

                <?php
                wp_nav_menu([
                    'theme_location' => 'main',
                    'items_wrap' => '<div id="%1$s">%3$s</div>',
                    'container' => false,
                    'menu_class' => apply_filters('waboot/navigation/main/class', 'splitted-nav'),
                    'walker' => $walker
                ]);
                ?>
                <?php
                /*$theme_location = 'main';

                if ( ($theme_location) && ($locations = get_nav_menu_locations()) && isset($locations[$theme_location]) ) {

                    $menu_list = '<ul class="nav navbar-nav navbar-split-left">' ."\n";

                        $menu = get_term( $locations[$theme_location], 'nav_menu' );
                        $menu_items = wp_get_nav_menu_items($menu->term_id);

                        $position = \Waboot\functions\get_option($name.'_item_select');
                        if ($position == 'middle') {
                            $count = call_user_func(function () use ($menu_items) {
                                $count = 0;
                                foreach ($menu_items as $menu_item) {
                                    if ($menu_item->menu_item_parent == 0) {
                                        $count++;
                                    }
                                }
                                return floor($count/2);
                            });
                        } else {
                            $count = intval($position);
                        }
                        $i = 0;

                        foreach( $menu_items as $menu_item ) {

                            if( $menu_item->menu_item_parent == 0 ) {

                                $parent = $menu_item->ID;

                                $menu_array = array();
                                foreach( $menu_items as $submenu ) {
                                    if( $submenu->menu_item_parent == $parent ) {
                                        $bool = true;
                                        $menu_array[] = '<li><a href="' . $submenu->url . '">' . $submenu->title . '</a></li>' ."\n";
                                    }
                                }

                                if( isset($bool) && $bool == true && count( $menu_array ) > 0 ) {

                                $menu_list .= '<li class="dropdown">' ."\n";
                                $menu_list .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">' . $menu_item->title . ' <span class="caret"></span></a>' ."\n";

                                $menu_list .= '<ul class="dropdown-menu">' ."\n";
                                $menu_list .= implode( "\n", $menu_array );
                                $menu_list .= '</ul>' ."\n";
                                $menu_list .= '</li>' ."\n";

                                } else {

                                $menu_list .= '<li>' ."\n";
                                $menu_list .= '<a href="' . $menu_item->url . '">' . $menu_item->title . '</a>' ."\n";
                                $menu_list .= '</li>' ."\n";

                                }

                                $i++;

                                if ($i==$count) {

                                    if ( \Waboot\template_tags\get_desktop_logo() != "" ) {
                                        $logo_menu_list = '<img src="' . \Waboot\template_tags\get_desktop_logo() . '"/>';
                                    } else {
                                        $logo_menu_list = get_bloginfo("name");
                                    }

                                    $menu_list .='</ul>';
                                    $menu_list .='<div class="logonav hidden-sm hidden-xs"><a href="' . get_bloginfo('url') . '">' . $logo_menu_list . '</a></div>';
                                    $menu_list .='<ul class="nav navbar-nav navbar-split-right">';

                                }

                            }

                        }

                    $menu_list .= '</ul>' ."\n";

                } else {
                    $menu_list = '<!-- no menu defined in location "'.$theme_location.'" -->';
                }

                echo $menu_list;*/
                ?>

            </div>
        </nav>
        <!-- End Main Nav -->

        <!-- End Header splitted -->
    </div>
</div><!-- #header-wrapper -->