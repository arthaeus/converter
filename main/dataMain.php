<?php

	/**
	 * if a request for data is made, this is the main class that will run.
	 */
	namespace Converter;
	require_once( '../mls/mls.php' );
	require_once( '../base.php' );
	require_once( '../util/util.php' );
	require_once( '../exception/exceptions.php' );
	require_once( 'abstractMain.php' );

	use Converter\Mls\mls                        as mls;
	use Converter\Util\util                      as util;
	use Converter\Util                           as utilNamespace;
	use Converter\abstractMain                   as abstractMain;
	use Converter\Exceptions\NoTableException    as NoTableException;
	use Converter\base                           as base;

	class dataMain extends abstractMain
	{

		public function __construct($cadID)
		{
			/**
			 * pass in the cadID
			 */
			abstractMain::__construct($cadID);
			
		}
		
		public function go()
		{

			/**
			 * beginning of the story.
			 *
			 * first get the container 
			 */
			$DI = $this->getDIContainer();

			/**
			 * request an mls.  the mls will be populated with its dependencies. the abstract main
			 * class just requests an mls from the DIC when requestMls is called.
			 */
			$iMls = $this->requestMls();
			$iMls->perform();
			$successMessage = "MLS " . $this->getCadID() . " has finished processing \n";
			$this->cout( $successMessage );

		}
	}

	//todo a106 throws an exception.  need to find out why, and handle this
	//$m = new dataMain('a106');
	//$m = new dataMain('a114');
	echo "TIME IS STARTING NOW";
	for( $i = 3 ; $i < 4 ; $i++ )
	{
		$pids[$numChildren] = pcntl_fork();

		/**
		 * in the child process
		 */
		if( !$pids[$numChildren] )
		{
			$cadID = str_pad($i, 3, '0', STR_PAD_LEFT);
			$m = new dataMain("a$cadID");
			base::getUtil()->recreate();
			$m->go();
			echo "\n\n\n\n\n---------------JUST DID--------------- a$i\n\n\n\n\n";
			exit;
		}
		/**
		 * in the parent process
		 */
		else
		{
			$numChildren++;
			sleep(10);
		}
	}
	//$m = new dataMain('a019');
	//$m = new dataMain('a001');
	echo "\n ALL HAVE BEEN LAUNCHED \n";
?>
