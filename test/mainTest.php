<?php

	namespace Converter\Test;
	require_once( '../main/main.php' );
	require_once( '../base.php' );
	require_once( 'TestCase.php' );
	use \Converter\Mls\mls  as mls;
	use \Converter\base     as base;
	use \Converter\main     as main;
	use \PHPUnit_Framework_TestCase as PHPUnit_Framework_TestCase;

	class mainTest extends PHPUnit_Framework_TestCase
	{
		public function testFirst()
		{
			$m = new main('a048');
		}
	}
?>
