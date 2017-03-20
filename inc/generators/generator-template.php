<?php

/*
 * This is a simple generator file. If the json file specifies the "actions" property, Waboot tries to include a php file with the same name as the json file.
 *
 * If this file declare a class name in "classname" property, Waboot tries to create a new instance from this class name and calls the method specified ad actions.
 */

class GeneratorTemplate{
	public function action_a(){
		//... do anything you want
	}

	public function action_b(){
		//... do anything you want
	}
}