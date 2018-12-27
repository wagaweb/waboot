                <?php do_action("waboot/content/after"); ?>
                </div><!-- .site-content__inner -->
            </div><!-- .site-content -->

        <?php get_sidebar(); ?>

        </main><!-- .main-content -->

    <?php do_action("waboot/main-content/after"); ?>

    <?php
    /*
     * main-bottom zone
     */
    \Waboot\template_tags\render_zone("main-bottom");
    ?>
</div><!-- .site-main -->