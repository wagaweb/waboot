                <?php do_action("waboot/main/after"); ?>
                </div><!-- .main__wrapper -->
            </main>

        <?php get_sidebar(); ?>

        </div><!-- .site-main__wrapper -->

    <?php do_action("waboot/site-main/after"); ?>

    <?php
    /*
     * main-bottom zone
     */
    \Waboot\template_tags\render_zone("main-bottom");
    ?>
</div><!-- .site-main -->