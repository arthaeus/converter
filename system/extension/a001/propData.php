<?php

	namespace Converter\System\Extension;
	require_once( '/home/caddevac/Projects/converter/system/datasource/propData.php' );

	use Converter\System\Datasource\propData  as propData;

	class multiFamily extends propData
	{
		public function __construct()
		{
			echo __CLASS__ . "\n\n";
		}

		public function perform()
		{
			echo "in mfr";
			$this->foo();
		}

		public function foo()
		{
		}
	}

	/*
	class singleFamily extends datasource
	{
		public function __construct()
		{
			echo __CLASS__ . "\n\n";
		}
	}
	*/
?>
