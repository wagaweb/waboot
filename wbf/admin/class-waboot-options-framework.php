<?php

class Waboot_Options_Framework extends Options_Framework {
    /**
     * Wrapper for optionsframework_options()
     *
     * Allows for manipulating or setting options via 'of_options' filter
     * For example:
     *
     * <code>
     * add_filter( 'of_options', function( $options ) {
     *     $options[] = array(
     *         'name' => 'Input Text Mini',
     *         'desc' => 'A mini text input field.',
     *         'id' => 'example_text_mini',
     *         'std' => 'Default',
     *         'class' => 'mini',
     *         'type' => 'text'
     *     );
     *
     *     return $options;
     * });
     * </code>
     *
     * Also allows for setting options via a return statement in the
     * options.php file.  For example (in options.php):
     *
     * <code>
     * return array(...);
     * </code>
     *
     * @return array (by reference)
     */
    static function &_optionsframework_options() {
        static $options = null;

        if ( !$options ) {
            // Load options from options.php file (if it exists)
            $location = apply_filters( 'options_framework_location', array('options.php') );
            if ( $optionsfile = locate_template( $location ) ) {
                $maybe_options = require_once $optionsfile;
                if ( is_array( $maybe_options ) ) {
                    $options = $maybe_options;
                } else if ( function_exists( 'optionsframework_options' ) ) {
                    $options = optionsframework_options();
                }
            }

            Waboot_ComponentsManager::addRegisteredComponentOptions(); //todo: maybe use the filter instead?

            // Allow setting/manipulating options via filters
            $options = apply_filters( 'of_options', $options );
        }

        return $options;
    }
}