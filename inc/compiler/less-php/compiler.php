<?php
/**
 * Live compiling less file. If $params is empty sources/less/themeName.less will be compiled in assets/css/style.css
 * @params (optional) array $params the input\output\mapfile name to use
 * @uses vendor/Less
 * @usage
 *  waboot_compile_less()
 *  OR
 *  waboot_compile_less(array('input' => 'path\to\input.less', 'output' => 'path\to\output.css', 'map' => 'map file name'))
 * @since 0.1.0
 */
function waboot_compile_less($params = array()){
    /** This filter is documented in wp-admin/admin.php */
    @ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );

    try{
        $theme = apply_filters("waboot_compiled_stylesheet_name",wp_get_theme()->stylesheet);
        /*if(is_child_theme()){
            $theme = "waboot-child";
        }else{
            $theme = "waboot";
        }*/

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
            'compress'          => defined(WABOOT_ENV) && WABOOT_ENV == "dev" ? false : true,
            'sourceMap'         => true,
            'sourceMapWriteTo'  => get_stylesheet_directory().'/assets/css/'.$mapFileName,
            'sourceMapURL'      => get_stylesheet_directory_uri().'/assets/css/'.$mapFileName,
        );

        if(!is_dir($cachedir)){
            if(!mkdir($cachedir)){
                throw new Exception("Cannot create ({$cachedir})");
            }
        }

        if(!is_writable($cachedir)){
            if(!chmod($cachedir,0777)){
                throw new Exception("Cache dir ({$cachedir}) is not writeable");
            }
        }

        //if(Waboot_Cache::needs_to_compile($less_files,$cachedir)){ //since we use the "Compile" button, we dont need this check anymore
            update_option('waboot_compiling_less_flag',1) or add_option('waboot_compiling_less_flag',1,'',true);

            $css_file_name = Less_Cache::Get(
                $less_files,
                $parser_options
            );

            $css = file_get_contents( $cachedir.'/'.$css_file_name );

            if(!is_writable($outputFile)){
                if(!chmod($outputFile,0777))
                    throw new Exception("Output file ({($outputFile}) is not writeable");
            }

            file_put_contents($outputFile, $css);

            update_option('waboot_compiling_less_flag',0);
            if ( current_user_can( 'manage_options' ) ) {
                echo '<div class="alert alert-success"><p>Less files compiled successfully</p></div>';
            }
        //}
    }catch (exception $e) {
        $wpe = new WP_Error( 'less-compile-failed', $e->getMessage() );
        if ( current_user_can( 'manage_options' ) ) {
            echo '<div class="alert alert-warning"><p>'.$wpe->get_error_message().'</p></div>';
        }
    }
}