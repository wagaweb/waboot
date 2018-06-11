<div id="site-main" class="site-main">
    <?php
    /*
     * main-top zone
     */
    \Waboot\template_tags\render_zone("main-top");
    ?>

    <?php
    /*
     * Here we print the singular title when "title_position" option is on "top".
     * @see: posts_and_pages.php
     */
    do_action("waboot/site-main/before");
    ?>

    <div class="<?php \Waboot\template_tags\container_classes(); ?>">
        <main id="main" role="main" class="<?php \Waboot\template_tags\main_classes(); ?>">
            <div class="main__wrapper">
                <?php do_action("waboot/main/before"); ?>