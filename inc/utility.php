<?php
if ( ! function_exists( 'waboot_locate_template_uri' ) ):
    /**
     * Snatched from future release code in WordPress repo.
     *
     * Retrieve the URI of the highest priority template file that exists.
     *
     * Searches in the stylesheet directory before the template directory so themes
     * which inherit from a parent theme can just override one file.
     *
     * @param string|array $template_names Template file(s) to search for, in order.
     * @return string The URI of the file if one is located.
     */
    function waboot_locate_template_uri( $template_names ) {

        $located = '';
        foreach ( (array) $template_names as $template_name ) {
            if ( ! $template_name )
                continue;

            if ( file_exists( get_stylesheet_directory() . '/' . $template_name ) ) {
                $located = get_stylesheet_directory_uri() . '/' . $template_name;
                break;
            } else if ( file_exists( get_template_directory() . '/' . $template_name ) ) {
                $located = get_template_directory_uri() . '/' . $template_name;
                break;
            }
        }

        return $located;
    }
endif;

/**
 * Replace the $old_prefix with $new_prefix in Theme Options id
 * @param $old_prefix
 * @param $new_prefix
 * @since 1.0
 */
function prefix_theme_options($old_prefix,$new_prefix){
    $options_field = get_option( 'optionsframework' );

    if(!$options_field || empty($options_field)) return;

    $options = get_option($options_field['id']);
    $new_options = array();

    if(!empty($options) && $options != false){
        foreach($options as $k=>$v){
            $new_k = preg_replace("|^".$old_prefix."_|",$new_prefix."_",$k);
            $new_options[$new_k] = $v;
        }
    }else{
        return;
    }

    update_option($options_field['id'],$new_options);
}

/**
 * Transfer theme options from a theme to another
 * @param string $from_theme theme the name of the theme from which export
 * @param (optional) null string $to_theme the name of the theme into which import (current theme if null)
 * @totest
 * @since 1.0
 */
function transfer_theme_options($from_theme,$to_theme = null){
    $from_theme_options = get_option($from_theme);
    if(!isset($to_theme))
        import_theme_options($from_theme_options);
    else
        update_option($to_theme,$from_theme_options);
}

/**
 * Copy a theme options array into current theme options option. Old theme options will be replaced.
 * @param array $exported_options
 * @totest
 * @since 1.0
 */
function import_theme_options($exported_options){
    $options_field = get_option( 'optionsframework' );
    update_option($options_field['id'],$exported_options);
}

/**
 * Live compiling less file. If $params is empty sources/less/themeName.less will be compiled in assets/css/style.css
 * @params (optional) array $params the input\output\mapfile name to use
 * @uses vendor/Less
 * @usage
 *  waboot_compile_less()
 *  OR
 *  waboot_compile_less(array('input' => 'path\to\input.less', 'output' => 'path\to\output.css', 'map' => 'map file name'))
 * @since 1.0
 */
function waboot_compile_less($params = array()){
    require_once("vendor/Lessphp/Less.php");
    require_once("Waboot_Cache.php");
    try{
        $theme = apply_filters("waboot_stylesheet_name",wp_get_theme()->stylesheet);

        if(empty($params)){
            $inputFile = get_stylesheet_directory()."/sources/less/{$theme}.less";
            $outputFile = get_stylesheet_directory()."/assets/css/{$theme}.css"; //precedente: "/assets/css/style.css", modificato per far funzionare respond.js\css3mediaqueries
            $mapFileName = "{$theme}.css.map";
        }else{
            $inputFile = $params['input'];
            if(!file_exists($inputFile)) throw new Exception("Input file {$inputFile} not found");
            $outputFile = $params['output'];
            $mapFileName = $params['map'];
        }

        $cachedir = get_stylesheet_directory()."/assets/cache";

        $less_files = array(
            $inputFile => get_stylesheet_directory_uri(),
        );
        $parser_options = array(
            'cache_dir'         => $cachedir,
            'compress'          => false,
            'sourceMap'         => true,
            'sourceMapWriteTo'  => get_stylesheet_directory().'/assets/css/'.$mapFileName,
            'sourceMapURL'      => get_stylesheet_directory_uri().'/assets/css/'.$mapFileName,
        );

        if(Waboot_Cache::needs_to_compile($less_files,$cachedir)){
            update_option('waboot_compiling_less_flag',1) or add_option('waboot_compiling_less_flag',1,'',true);
            $css_file_name = Less_Cache::Get(
                $less_files,
                $parser_options
            );
            $css = file_get_contents( $cachedir.'/'.$css_file_name );
            file_put_contents($outputFile, $css);
            update_option('waboot_compiling_less_flag',0);
            if ( current_user_can( 'manage_options' ) ) {
                echo '<div class="alert alert-success"><p>Less files compiled successfully</p></div>';
            }
        }
    }catch (exception $e) {
        $wpe = new WP_Error( 'less-compile-failed', $e->getMessage() );
        if ( current_user_can( 'manage_options' ) ) {
            echo '<div class="alert alert-warning"><p>'.$wpe->get_error_message().'</p></div>';
        }
    }
}