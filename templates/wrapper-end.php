        </div>

        <?php if(is_home() || is_archive()) {
            get_sidebar();
        } ?>

    </div>

    <?php do_action('waboot/layout/main-bottom'); ?>
</main>
