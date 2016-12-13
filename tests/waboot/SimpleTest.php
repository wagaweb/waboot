<?php

namespace tests;

class SimpleTest extends \PHPUnit_Framework_TestCase{
	public function testSimple(){
		$a = true;
		$b = true;
		// Assert
		$this->assertEquals($a, $b);
	}
}