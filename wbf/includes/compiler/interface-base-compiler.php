<?php

namespace WBF\includes\compiler;

interface Base_Compiler{
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
	 *          'exclude_from_global_compile => false
	 *          'compile_pre_callback => null
	 *          'compile_post_callback => null
	 *      ),
	 *      'set-name-2' => ...
	 * )
	 */
	function __construct($args);
	public function compile();
	public function compile_set($name,$args);
	public function add_set($name,$args);
	public function remove_set($name);
}