<?php

class Waboot_Less_Compiler{
    public $compile_sets = array();

	/**
	 * @param array $compile_sets the "sets" of styles to compile. The array must have this form:
	 * array(
	 *      'set-name-1' => array(
	 *			'input' => '', //the path to the input less file
	 *          'output' => '', //the path to the output css file
	 *          'map' => '', //the path to the map file
	 *          'map_url' => '', //the url to the map file
	 *          'cache' => '' //the path to the cache directory
	 *          'import_url' => '' //the path to the imports directory
	 *      ),
	 *      'set-name-2' => ...
	 * )
	 */
    function __construct($compile_sets){
        $this->compile_sets = $compile_sets;
    }

    function compile(){
        try{
            foreach($this->compile_sets as $set_name => $set_args){
                $this->compile_set($set_name,$set_args);
            }
            if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
                echo 1;
                die();
            }else{
                return true;
            }
        }catch(exception $e){
            if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
                echo 0;
                die();
            }else{
                return false;
            }
        }
    }

    function compile_set($name,$args){
        try{
	        global $wp_filesystem;
	        require_once( "Waboot_Cache.php" );
	        require_once( get_template_directory()."/wbf/includes/compiler/compiler-utils.php" );

	        $args['input'] = parse_input_file($args['input']);
            $less_files = array(
	            $args['input'] => $args['import_url'],
            );

            $parser_options = array(
                'cache_dir'         => $args['cache'],
                'compress'          => false,
                'sourceMap'         => true,
                'sourceMapWriteTo'  => $args['map'],
                'sourceMapURL'      => $args['map_url'],
            );

	        if(!is_dir($args['cache'])){
		        if(!mkdir($args['cache'])){
			        throw new Exception("Cannot create ({$args['cache']})");
		        }
	        }

            if(!is_writable($args['cache'])){
	            if(!chmod($args['cache'],0777)) {
		            throw new Exception( "Cache dir ({$args['cache']}) is not writeable" );
	            }
            }

            $css_file_name = Less_Cache::Get(
                $less_files,
                $parser_options
            );

            $css = file_get_contents( $args['cache'].'/'.$css_file_name );

            if(!is_writable($args['output'])){
	            if(!chmod($args['output'],0777))
	                throw new Exception("Output dir ({$args['output']}) is not writeable");
            }

            //$wp_filesystem->put_contents( $args['output'], $css, FS_CHMOD_FILE );
            file_put_contents($args['output'], $css);
            return true;
        }catch(Exception $e){
            throw $e;
        }
    }

	function needs_to_compile($set){
		if(!is_array($set)){
			$set = $this->compile_sets[$set];
		}

		$less_files = array(
			$set['input'] => $set['import_url'],
		);

		if(Waboot_Cache::needs_to_compile($less_files,$set['cache'])){
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
				echo 1;
				die();
			}else{
				return true;
			}
		}else{
			if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
				echo 0;
				die();
			}else{
				return false;
			}
		}
	}
}