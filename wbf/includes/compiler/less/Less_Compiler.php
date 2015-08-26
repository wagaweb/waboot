<?php

namespace WBF\includes\compiler\less;
use WBF\includes\compiler\Base_Compiler;
use \Exception;

require_once( get_template_directory()."/wbf/includes/compiler/interface-base-compiler.php" );

class Less_Compiler implements Base_Compiler{
    public $compile_sets = array();
	public $sources_path = "";
	public $property = array();

	/**
	 * @param array $args must have a "sets" key that specifies styles to compile. The array must have this form:
	 * array(
	 *      'set-name-1' => array(
	 *			'input' => '', //the path to the input less file
	 *          'output' => '', //the path to the output css file
	 *          'map' => '', //the path to the map file
	 *          'map_url' => '', //the url to the map file
	 *          'cache' => '' //the path to the cache directory
	 *          'import_url' => '' //the path to the imports directory
	 *          @since 0.12.9:
	 *          'exclude_from_global_compile' => false
	 *          'compile_pre_callback' => null
	 *          'compile_post_callback' => null
	 *      ),
	 *      'set-name-2' => ...
	 * )
	 */
    function __construct($args){
	    foreach($args['sets'] as $k => $v){
		    $args['sets'][$k] = wp_parse_args($v,[
			    'input' => '',
			    'output' => '',
			    'map' => '',
			    'map_url' => '',
			    'cache' => '',
			    'import_url' => '',
			    //@since 0.12.9:
			    'exclude_from_global_compile' => false,
			    'compile_pre_callback' => null,
			    'compile_post_callback' => null
		    ]);
	    }
        $this->compile_sets = $args['sets'];
	    unset($args['sets']);
	    if(isset($args['sources_path'])){
		    $this->sources_path = $args['sources_path'];
		    unset($args['sources_path']);
	    }
	    if(!empty($args)){
	        $this->property = $args;
	    }
    }

    function compile($name,$args = []){
        try{
	        global $wp_filesystem;
	        require_once( WBF_DIRECTORY."/includes/compiler/less/Less_Cache.php" );
	        require_once( get_template_directory()."/wbf/includes/compiler/compiler-utils.php" );

	        $args = wp_parse_args($args,$this->compile_sets[$name]);

	        $args['input'] = \WBF\includes\compiler\parse_input_file($args['input']);
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

	        return $css;
        }catch(Exception $e){
            throw $e;
        }
    }

	function add_set($name,$args){
		$args = wp_parse_args($args,[
			'input' => '',
			'output' => '',
			'map' => '',
			'map_url' => '',
			'cache' => '',
			'import_url' => '',
			//@since 0.12.9:
			'exclude_from_global_compile' => false,
			'compile_pre_callback' => null,
			'compile_post_callback' => null
		]);
		$this->compile_sets[$name] = $args;
	}

	function remove_set($name){
		if(array_key_exists($name,$this->compile_sets)){
			unset($this->compile_sets[$name]);
		}
	}

	function needs_to_compile($set){
		if(!is_array($set)){
			$set = $this->compile_sets[$set];
		}

		$less_files = array(
			$set['input'] => $set['import_url'],
		);

		if(Less_Cache::needs_to_compile($less_files,$set['cache'])){
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