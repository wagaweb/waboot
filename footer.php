    <?php
        $footerClass = '';
        $footerBlockId = get_option('footerSelect');
        
        if ($footerBlockId) {
            $blockTitle = get_the_title($footerBlockId);
            $footerClass = str_replace('footer-widgets-', 'footer--', sanitize_title($blockTitle));
        }
    ?>

    <footer class="footer <?php echo $footerClass; ?>">

        <?php do_action('waboot/layout/footer'); ?>

    </footer>

    <?php do_action('waboot/layout/page-after'); ?>

    <?php wp_footer(); ?>

    </body>
</html>
