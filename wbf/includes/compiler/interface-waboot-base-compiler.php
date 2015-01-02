<?php

interface Waboot_Base_Compiler{
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
	function __construct($compile_sets);
	public function compile();
	public function compile_set($name,$args);
}