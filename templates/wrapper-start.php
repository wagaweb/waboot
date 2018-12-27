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
    do_action("waboot/main-content/before");
    ?>

    <main role="main" class="main-content <?php \Waboot\template_tags\container_classes(); ?>">
        <div class="site-content <?php \Waboot\template_tags\main_classes(); ?>">
            <div class="site-content__inner">
                <?php do_action("waboot/content/before"); ?>