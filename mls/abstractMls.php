<?php

	namespace Converter\Mls;

	require_once( "/home/caddevac/Projects/converter/interfaces.php" );
	require_once( "/home/caddevac/Projects/converter/base.php" );
	
	use Converter\Interfaces\iProcess               as iProcess;
	use Converter\Interfaces\iMls                   as iMls;
	use Converter\Interfaces\iTranslate             as iContained;
	use Converter\base                              as base;


	/**
	 * mls's have property types, and property types have listings.
	 * The translateProcess (generic) corresponds with the property type level.
	 */
	abstract class abstractMls extends base implements iProcess
	{

		protected $state         = null;

		/**
		 * a queue of iDataSource objects.  Each property type is an iDataSource and a iProcess.
		 * iTranslates will run in parallel
		 */
		protected $iDatasources   = null;

		public function __construct()
		{
			$this->iDatasources = new \stdClass();
			return;
		}

		public function getCadID() { return $this->cadID; }
		public function setCadID($i) { $this->cadID = $i; return; }
		
		//public function getCadID() { return $this->cadID; }
		//public function setCadID($i) { $this->cadID = $i; return; }


		public function getIDatasources() { return $this->iDatasources; }

		public function perform()
		{



			/**
			 * remember this class extends abstract main.  this function is in the abstract class because all mls' have propertyTypes (datasources)
			 */
			$iDatasources  = $this->getIDatasources();


			/**
			 * populate the datasources.  do the following:
			 * 1. get field defs
			 * 2. get propTypeInfo
			 * 3. populate with data.
			 */
			
			$numChildren = 0;
			foreach( $iDatasources as $iDatasource  )
			{


				/**
				 * propTypeInfo is an object that will look something like this:
				 * stdClass Object
				 *	(
				 *			[v1Pt] => sfr
				 *			[v2Pt] => 1
				 *			[activeTable] => 20130129-1
				 *			[allTable] => 20130129-all
				 *	)
				 */

				/**
				 * in this story, I am going to set field definitions, and populate the datasource from the outside (as opposed to inside of the class...maybe after the constructor.)
				 * right now it seems that it is more flexible to not have one method/process trying to do too many things.
				 */

				//create an iLogger outside of the fork.  all children can use this iLogger
				$iLogger = self::getILoggerByID('mls');

				$pids[$numChildren] = pcntl_fork();

				/**
				 * in the child process
				 */
				if( !$pids[$numChildren] )
				{
					/**
					 * each child process should recreate the database connection
					 */
					base::$util->recreate();

					/**
					 * get the class name without the namespace.  Only get field definitions if dealing with the class of propData or propDataGlob
					 */
					$iDatasourceClass = end( explode('\\', get_class($iDatasource)) ); 
					
					/**
					 * getFieldDefinitions only makes sense if the iDatasource is an instance of propData or propDataGlob
					 */
					if( $iDatasourceClass == "propData" || $iDatasourceClass == "propDataGlob" )
					{

						$iLogger->logNotice( "FORKED off new proc for " . $iDatasource->getPropertyTypeInfo()->v1Pt );
						echo "FORKED for " . $iDatasource->getPropertyTypeInfo()->v1Pt ."\n";
						/**
						 * get the field definitions for this datasource
						 */
						$iDatasource->getFieldDefinitions();

					}

					/**
					 * get the data for this datasource the model for this datasource executes a query that joins the property type and the all table
					 * ( for example `20130303-1` and `20130303-all` )
					 */
					$iDatasource->populate();
					/**
					 * we're all loaded with data.  Process the data in parallel.
					 * print_r( mysql_fetch_object( $iDatasources->pop()->getData() ) );
					 */
					$iDatasource->perform();
					exit;
				}
				/**
				 * in the parent process
				 */
				else
				{
					/**
					 * create a new database connection
					 */
					//$GLOBALS['pageWideDbConnections']  = null;

					$numChildren++;
				}
			}

		}

	}

?>
