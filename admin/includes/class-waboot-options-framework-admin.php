<?php

require_once "class-options-framework-admin.php";

class Waboot_Options_Framework_Admin extends Options_Framework_Admin{
    /**
     * Builds out the options panel.
     *
     * If we were using the Settings API as it was intended we would use
     * do_settings_sections here.  But as we don't want the settings wrapped in a table,
     * we'll call our own custom optionsframework_fields.  See options-interface.php
     * for specifics on how each individual field is generated.
     *
     * Nonces are provided using the settings_fields()
     *
     * @since 1.7.0
     */
    function options_page() { ?>

        <div id="optionsframework-wrap" class="wrap">
            <?php $menu = $this->menu_settings(); ?>

            <div class="optionsframework-header">
                <h2><?php echo esc_html( $menu['page_title'] ); ?></h2>
            </div>

            <div id="optionsframework-content-wrapper">
                <div class="nav-tab-wrapper">
                    <ul>
                        <?php echo Waboot_Options_Framework_Interface::optionsframework_tabs(); ?>
                    </ul>
                </div>

                <?php settings_errors( 'options-framework' ); ?>

                <div id="optionsframework-metabox" class="metabox-holder">
                    <div id="optionsframework" class="postbox">
                        <form action="options.php" method="post">
                            <?php settings_fields( 'optionsframework' ); ?>
                            <?php Options_Framework_Interface::optionsframework_fields(); /* Settings */ ?>
                            <div id="optionsframework-submit">
                                <input type="submit" class="button-primary" name="update" value="<?php esc_attr_e( 'Save Options', 'textdomain' ); ?>" />
                                <input type="submit" class="reset-button button-secondary" name="reset" value="<?php esc_attr_e( 'Restore Defaults', 'textdomain' ); ?>" onclick="return confirm( '<?php print esc_js( __( 'Click OK to reset. Any theme settings will be lost!', 'textdomain' ) ); ?>' );" />
                                <div class="clear"></div>
                            </div>
                        </form>
                    </div> <!-- / #container -->
                </div>
            </div> <!-- / #content-wrapper -->
            <?php do_action( 'optionsframework_after' ); ?>
        </div> <!-- / .wrap -->
    <?php
    }
}