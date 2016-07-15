<?php

	namespace Converter;
	require_once( '../mls/mls.php' );
	require_once( '../base.php' );
	require_once( '../exception/exceptions.php' );
	use Converter\Mls\mls  as mls;
	use Converter\base     as base;
	use Converter\Exceptions\V1PropTypeException as V1PropTypeException;

	abstract class abstractMain extends base
	{

		public function __construct($cadID)
		{
			/**
			 * pass in the cadID
			 */
			base::__construct($cadID);
			
		}

		/**
		 * return the DI container
		 */
		protected function getDIContainer()
		{
			return base::getDI();
		}

		protected function requestMls()
		{
			/**
			 * request an mls from the container.  all of its dependencies will be returned along with the mls
			 */
			$iMls = base::$DI['iMls']; 
			return $iMls;
		}

		/**
		 * in order to make the system go, you must implement this function
		 */
		abstract public function go();

	}
?>
