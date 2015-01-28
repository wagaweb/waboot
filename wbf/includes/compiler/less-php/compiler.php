<?php

/**
 * THIS FILE IS DEPRECATED, NOW WE USE class-waboot-styles-compiler.php
 */

/**
 * Live compiling less file. If $params is empty sources/less/themeName.less will be compiled in assets/css/themeName.css
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
        $theme = wbf_get_compiled_stylesheet_name();
        /*if(is_child_theme()){
            $theme = "waboot-child";
        }else{
            $theme = "waboot";
        }*/

        if(empty($params)){
            $inputFile = parse_input_file(get_stylesheet_directory()."/sources/less/{$theme}.less");
            $outputFile = get_stylesheet_directory()."/assets/css/{$theme}.css"; //precedente: "/assets/css/style.css", modificato per far funzionare respond.js\css3mediaqueries
            $mapFileName = "{$theme}.css.map";
        }else{
            $inputFile = $params['input'];
            if(!file_exists($inputFile)) throw new Exception("Input file {$inputFile} not found");
            $outputFile = $params['output'];
            $mapFileName = $params['map'];
        }

        $cachedir = get_stylesheet_directory()."/assets/cache";

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

        if(can_compile()){
            //if(Waboot_Less_Cache::needs_to_compile($less_files,$cachedir)){ //since we use the "Compile" button, we dont need this check anymore
            update_option('waboot_compiling_less_flag',1) or add_option('waboot_compiling_less_flag',1,'',true); //lock the compiler
            update_option('waboot_compiling_less_last_attempt',time()) or add_option('waboot_compiling_less_last_attempt',time(),'',true); //keep note of the current time

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

            update_option('waboot_compiling_less_flag',0); //release the compiler
            if ( current_user_can( 'manage_options' ) ) {
	            if(is_admin()){
		            if(isset($GLOBALS['option_page']) && $GLOBALS['option_page'] == 'optionsframework'){
			            add_settings_error('options-framework', 'save_options', __('Less files compiled successfully.', 'wbf'), 'updated fade');
		            }else{
		                add_action( 'admin_notices', 'less_compiled_admin_notice');
		            }
	            }else{
                    echo '<div class="alert alert-success"><p>'.__('Less files compiled successfully.', 'wbf').'</p></div>';
	            }
            }
            //}
        }else{
	        throw new WabootCompilerBusyException();
        }
    }catch (Exception $e) {
	    if(!$e instanceof WabootCompilerBusyException) update_option('waboot_compiling_less_flag',0); //release the compiler
        $wpe = new WP_Error( 'less-compile-failed', $e->getMessage() );
        if ( current_user_can( 'manage_options' ) ) {
	        if(is_admin()){
		        if(isset($GLOBALS['option_page']) && $GLOBALS['option_page'] == 'optionsframework'){
			        add_settings_error('options-framework', 'save_options', $wpe->get_error_message(), 'error fade');
		        }else{
		            add_action( 'admin_notices', 'less_compile_error_admin_notice');
		        }
	        }else{
		        echo '<div class="alert alert-warning"><p>'.$wpe->get_error_message().'</p></div>';
	        }
        }
    }
}

function waboot_clear_less_cache(){
    update_option('waboot_compiling_less_flag',0); //release the compiler
    $cachedir = get_stylesheet_directory()."/assets/cache";
    if(is_dir($cachedir)){
        $files = glob($cachedir."/*");
        foreach($files as $file){ // iterate files
            if(is_file($file))
                unlink($file); // delete file
        }
        if(is_admin()){
            add_action( 'admin_notices', 'cache_cleared_admin_notice');
        }else{
            echo '<div class="alert alert-success"><p>'.__('Theme cache cleared successfully!', 'wbf').'</p></div>';
        }
    }
}

/**
 * Releases the compiler lock if is passed too much time since last compilation attempt
 * @param int $timelimit (in minutes)
 */
function waboot_maybe_release_compiler_lock($timelimit = 10){
    if(!can_compile()){
        $last_attempt = get_option("waboot_compiling_less_last_attempt");
        if(!$last_attempt){
            update_option('waboot_compiling_less_flag',0); //release the compiler just to be sure
        }else{
            $current_time = time();
            $time_diff = ($current_time - $last_attempt)/60;
            if($time_diff > $timelimit){ //10 minutes
                update_option('waboot_compiling_less_flag',0); //release the compiler
            }
        }
    }
}

function less_compiled_admin_notice() {
	?>
	<div class="updated">
		<p><?php _e( 'Less Compiled Successfully!', 'wbf' ); ?></p>
	</div>
	<?php
}

function less_compile_error_admin_notice() {
	?>
	<div class="error">
		<p><?php _e( 'Less files not compiled!', 'wbf' ); ?></p>
	</div>
<?php
}

function cache_cleared_admin_notice() {
    ?>
    <div class="updated">
        <p><?php _e( 'Theme cache cleared successfully!', 'wbf' ); ?></p>
    </div>
<?php
}

/**
 * Check if the compiler is not already busy
 */
function can_compile(){
	$busyflag = get_option("waboot_compiling_less_flag",0);
	if($busyflag && $busyflag != 0){
		return false;
	}

	return true;
}

/**
 * Generate a temp file parsing commented include tags in the $filepath less file.
 *
 * @param $filepath (the absolute path to the file to parse (usually waboot.less or waboot-child.less)
 *
 * @return string filepath to temp file
 *
 * @since 0.7.0
 */
function parse_input_file($filepath){
	$inputFile = new SplFileInfo($filepath);
	if($inputFile->isReadable()){
		$inputFileObj = $inputFile->openFile();
		$tmpFile = new SplFileInfo($inputFile->getPath()."/tmp_".$inputFile->getFilename());
		$tmpFileObj = $tmpFile->openFile("w+");
		if($tmpFileObj->isWritable()){
			while (!$inputFileObj->eof()) {
				$line = $inputFileObj->fgets();
				if(preg_match("|\{@import '([a-zA-Z0-9\-/_.]+)'\}|",$line,$matches)){
					$fileToImport = new SplFileInfo(dirname($filepath)."/".$matches[1]);
					if($fileToImport->isFile() && $fileToImport->isReadable()){
						$line = "@import '{$fileToImport->getRealPath()}';\n";
					}/*else{
						//If we are in the child theme, search the file into parent directory
						if(is_child_theme()){
							$fileToImport = new SplFileInfo(get_template_directory()."/sources/less/".$matches[1]);
							if($fileToImport->isFile() && $fileToImport->isReadable()){
								$line = "@import '{$fileToImport->getFilename()}';\n";
							}
						}
					}*/
				}
				$tmpFileObj->fwrite($line);
			}
			$filepath = $tmpFile->getRealPath();
		}
	}

	return $filepath;
}

class WabootCompilerBusyException extends Exception{
	public function __construct($message = null, $code = 0, Exception $previous = null) {
		if(!isset($message)){
			$message = __("The compiler is busy","wbf");
		}
		parent::__construct($message, $code, $previous);
	}
}