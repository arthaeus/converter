<?php

	namespace Converter;

	require_once( "lib/pimple/pimple.php" );
	require_once( "lib/loggar/loggar.php" );
	require_once( 'mls/mls.php' );
	require_once( 'interfaces.php' );
	require_once( '/home/caddevac/Projects/converter/system/datasource/datasourceBase.php' );
	require_once( '/home/caddevac/Projects/converter/system/datasource/imageData.php' );
	require_once( '/home/caddevac/Projects/converter/system/datasource/propData.php' );
	require_once( '/home/caddevac/Projects/converter/system/datasource/propDataGlob.php' );
	require_once( '/home/caddevac/Projects/converter/system/datasource/globModel.php' );
	require_once( '/home/caddevac/Projects/converter/system/datasource/imageModel.php' );
	require_once( '/home/caddevac/Projects/converter/system/datasource/sql.php' );
	require_once( '/home/caddevac/Projects/converter/cache/memcache.php' );
	require_once( '/home/caddevac/Projects/converter/config/config.php' );
	require_once( '/home/caddevac/Projects/converter/output/csvOutput.php' );
	require_once( '/home/caddevac/Projects/converter/exception/exceptions.php' );
	require_once( 'util/util.php' );

	use Converter\Interfaces\iMls													 as iMls;
	use Converter\Mls\mls																	 as mls;
	use Converter\Output\csvOutput                         as csvOutput;
	use Converter\System\Datasource\propData               as propData;
	use Converter\System\Datasource\imageData              as imageData;
	use Converter\System\Datasource\propDataGlob           as propDataGlob;
	use Converter\System\Datasource\Sql\globModel          as globModel;
	use Converter\System\Datasource\Model\imageModel       as imageModel;
	use Converter\System\Datasource\Sql\model							 as model;
	use Converter\Lib\Pimple															 as Pimple;
	use Converter\Util\util																 as util ;
	use Converter\Cache\cadMemcache												 as cadMemcache;
	use Converter\Logger\cadLogger											   as cadLogger;
	use Converter\Config\config														 as config;
	use Converter\Exceptions\V1PropTypeException           as V1PropTypeException;
	use Converter\Exceptions\V2PropTypeException           as V2PropTypeException;
	use Converter\Exceptions\V2ActiveTableException        as V2ActiveTableException;
	use Converter\Exceptions\V2AllTableException           as V2AllTableException;
	use Converter\Exceptions\MisconfigException            as MisconfigException;
	use Converter\System\Datasource\abstractDatasourceBase as abstractDatasourceBase;

	class base
	{
		/**
		 * dependency injection container.  All classes should have the di container avaliable for use
		 */
		protected static  $DI       = null;
		protected static  $util     = null;
		protected static  $iCache   = null;
		protected static  $iConfig  = null;

		/**
		 * this is the system level logger.  it logs to the /log directory
		 */
		protected static  $iLogger  = null;

		/**
		 * Right now I'm thinking that the cadID should be a part of the base.  At the base level, we are dealing with units, and those units are mls'.  I think
		 * is it ok for all children of this class to also have an cadID?  I think that is logical.  A datasource, translator, and lookup should have access to
		 * the cadID variable because they will probably need it.
		 */
		
		protected        $cadID    = null;
		protected        $state    = null;

		/**
		 * at the root level, we are creating all of the converter system's elements (datasource, translator, and lookup) for an mls.  Therefore, I think it makes
		 * sense to pass the cadID to the base constructor
		 */

		public function __construct( $cadID )
		{

			self::$iConfig = new config();
			self::$iConfig->buildConfig();

			/**
			 * set the cadID for this base instance
			 */
			$this->cadID   = $cadID;

			$globalConfig    = self::$iConfig->getSetting('global');


			/**
			 * SETUP THE CACHE
			 */


			/**
			 * using variable class names while in a namespace will not work unless you specify the full namespace
			 * to the variable class that you are trying to instantiate
			 */
			$cacheClass    = "Converter\\Cache\\" . $globalConfig->cache
			->class
			->value;

			/**
			 * read the config file, and set the cache class
			 */
			self::$iCache  = new $cacheClass();
			
			/**
			 * interface method to the cache class.  when using memcache, for example, you have to call the 'addServer'
			 * method before using the cache.  other caches probably have different ways of initializing and setting up.
			 * Most caches probably require setup of some kind before use.  I think This function should be abstract.
			 */
			self::$iCache->setupCache();

			/**
			 * cache the configuration
			 */
			self::$iCache->set( "apiConfig" , self::$iConfig );

			$testConfig = self::$iCache->get( "apiConfig" );


			/**
			 * SETUP LOGGING
			 */

			/**
			 * logger class
			 */
			$loggerClass    = "Converter\\Logger\\" . $globalConfig->logger
			->class
			->value;

			/**
			 * logger file path
			 */
			$loggerPath     = $globalConfig->logger
			->file
			->path
			->value;

			/**
			 * by default, i provide 2 loggers.  the global logger (the path points to the global log file),
			 * and the mls level logger (the path points to the data directory for this mls)
			 */
			self::$iLogger = new \stdClass;
			self::$iLogger->global = new $loggerClass($loggerPath);

			/**
			 * log the startup time
			 */

			self::$iLogger->global
				->logNotice("request made to " . $this->cadID);


			/**
			 * SETUP THE DI CONTAINER
			 */

			/**
			 * create the DI container
			 */
			self::$DI      = new Pimple();

			/**
			 * configure the DI container
			 */
			$this->configureDependencies($this->cadID);
			
			self::$util    = new util();
		}

		/**
		 * getter and setter
		 */
		final public function setState( $state ) { $this->state = $state; return; }
		final public function getState() { return $this->state; }
		
		public static function setIConfig( $c ) { self::$iConfig = $c; return; }
		public static function getIConfig() { return self::$iConfig; }
		
		public function getCadID() { return $this->cadID; }
		
		public static function setILogger( $l ) { self::$iLogger = $c; return; }

		/**
		 * there can be multiple loggers.  I'm making multiple loggers possible because each logger has a separate file.
		 */
		public static function getILogger() { return self::$iLogger; }
		public static function getILoggerByID($loggerName) { return self::$iLogger->$loggerName; }

		public static function getUtil() { return self::$util; }



		/**
		 * build the DI container
		 * @todo more research on how others use/configure DI containers.
		 */
		private function configureDependencies()
		{
				$cadID      = $this->cadID;
				/**
				 * closure for the iDatasource
				 */
				self::$DI['iMls'] = function($c) use( $cadID ) {

					/**
					 * instantiate the mls object
					 */
					$iMls = new mls( $cadID );

					/**
					 * after instantiation, the mls object is populated with its property type information.  this information is an object
					 * and each of its properties corresponds with an mls property type.  so mls->propertyTypes would look like this for
					 * example:
					 *
					 *     [1] => stdClass Object
           *     (
           *         [v1Pt] => sfr
           *         [v2Pt] => 1
           *         [activeTable] => 20130401-1
           *         [allTable] => 20130401-all
           *     )
					 *
					 * i think we should loop through these and create a datasource for each property type.
					 */
					
					try 
					{   
						$propertyTypes = $iMls->getPropertyTypeInfo();
					}   
					catch( NoTableException $e )
					{   
						echo "error bla";
					}

					$iDatasources  = $iMls->getIDatasources();

					/**
					 * loop through the property types.  each iteration, save the instantiated datasource.
					 * after the loop we decide if a property type (datasource) is a regular datasource
					 * (for example, sfr with no sfr2, sfr3, etc), or if it a glob datasource
					 * (for example has sfr,sfr2,sfr3,etc)
					 */

					/**
					 * tempDatasourceArray will hold the instantiated datasourcese as the loop runs.
					 */
					$tempDatasourceArray = array();
					foreach( $propertyTypes as $pt => $ptInfo )
					{
						/**
						 * grab an iDatasource from the container
						 * when creating an iDatasource, the propertyTypeInfo for this property type (iDatasource)
						 * should be passed to the closure that creates the iDatasource.  The iDatasource closure
						 * may need to create a customized iDatasource class
						 */

						$c['propertyTypeInfo'] = $ptInfo;
						$iDatasource = $c['propData'];

						/**
						 * the datasource is contained by the mls.  give the datasource a refernce back to the mls.  this will allow 2 way 
						 * communication between the container and the contained
						 */
						$iDatasource->setIMls( $iMls );
						
						/**
						 * create the sql builder and make it aware of the datasource
						 */
						$iDatasourceModel = $c['iDatasourceModel'];

						/**
						 * make the model and the datasource aware of each other by calling the setters
						 */
						$iDatasourceModel->setIDatasource( $iDatasource );
						$iDatasource->setIDatasourceModel( $iDatasourceModel );
						
						$iDatasource->setPropertyTypeInfo( $ptInfo );

						/**
						 * first, check if this datasource is a globbed property type.  If it is, make sure to create iDatasource
						 * as a propDataGlob
						 * 
						 * ======================================
						 *
						 * add the datasource (property type) to the mls.  iDatasources belongs to the mls. (property types belong to the mls)
						 */

						/**
						 * if this is sfr2 or ld5 etc (a globbed property type)
						 */
						$propertyTypeBase = null;
						if( is_numeric( substr( $ptInfo->v1Pt , -1 ) ) )
						{
							$propertyTypeBase = substr( $ptInfo->v1Pt , 0 , -1 );
						}
						else
						{
							$propertyTypeBase = $ptInfo->v1Pt;
						}
						/**
						 * group all of the property types together if there are more than one (for example sfr,sfr2,sfr3,etc)
						 */
						$tempDatasourceArray[$propertyTypeBase][] = $iDatasource;
					}

					/**
					 * loop through each property type.
					 */
					foreach( $tempDatasourceArray as $parentType => &$propTypeSubsets )
					{
						/**
						 * check for errors in propTypeInfo.  for instance, I had this row in the dataabase
						 *
									mysql> SELECT * 
											-> FROM  `v1PropTypeTranslation` 
											-> WHERE  `v2cadID` LIKE  'a106'
											-> LIMIT 0 , 30;
									+---------+---------+------+------+
									| v1cadID | v2cadID | v1Pt | v2Pt |
									+---------+---------+------+------+
									|     245 | a106    | 1    |    0 |
									|     245 | a106    | sfr  |    1 |
									|     245 | a106    | com  |    2 |
									|     245 | a106    | ld   |    3 |
									|     245 | a106    | sfr2 |    4 |
									+---------+---------+------+------+

							and this resulted in this object:
							[propertyTypeInfo:protected] => stdClass Object
									(
											[v1Pt] => 1
											[v2Pt] => 0
											[activeTable] => 20120802-all
											[allTable] => 20120802-all
									)

						 * notice the first row in the resultset.  this error made the program crash.  test for this condition and others
						 */

						/**
						 * ...so lets get the property type information and look for abnormalities
						 *
						 *
						 * if there is an abnormality, unset the iDatasource
						 *
						 */

						//print_r( $propTypeSubsets );
						$propertyTypeInfo = $propTypeSubsets[0]->getPropertyTypeInfo();

						/**
						 * if the v1 propertyType is a number, and not alphanum, fail.
						 */
						if( is_numeric( $propertyTypeInfo->v1Pt ) )
						{
							$printr = print_r( $propertyTypeInfo , true );
							base::getILoggerByID('global')->logWarning("There may be a problem with the v1Pt.  check the proptypetranslations table.  This is for mls $cadID \n //////////////////////////////////////////////START OF PRINT_R//////////////////////////////////////// \n $printr \n //////////////////////////////////////////////END OF PRINT_R////////////////////////////////////////\n");
							continue;
						}

						/**
						 * the v2Pt must be greater than zero.  if it is not, the test will fail
						 */
						if( $propertyTypeInfo->v2Pt <= 0 )
						{
							$printr = print_r( $propertyTypeInfo , true );
							base::getILoggerByID('global')->logWarning("There may be a problem with the v2Pt.  check the proptypetranslations table.  This is for mls $cadID \n //////////////////////////////////////////////START OF PRINT_R//////////////////////////////////////// \n $printr \n //////////////////////////////////////////////END OF PRINT_R////////////////////////////////////////\n");
							continue;
						}

						/**
						 * the active table must be set
						 */
						if ( !$propertyTypeInfo->activeTable )
						{
							$printr = print_r( $propertyTypeInfo , true );
							base::getILoggerByID('global')->logWarning("There may be a problem with the active table.  check the proptypetranslations table.  This is for mls $cadID \n //////////////////////////////////////////////START OF PRINT_R//////////////////////////////////////// \n $printr \n //////////////////////////////////////////////END OF PRINT_R////////////////////////////////////////\n");
							continue;
						}

						/**
						 * the all table must be set
						 */
						if ( !$propertyTypeInfo->allTable )
						{
							$printr = print_r( $propertyTypeInfo , true );
							base::getILoggerByID('global')->logWarning("There may be a problem with the all table.  check the proptypetranslations table.  This is for mls $cadID \n //////////////////////////////////////////////START OF PRINT_R//////////////////////////////////////// \n $printr \n //////////////////////////////////////////////END OF PRINT_R////////////////////////////////////////\n");
							continue;
						}


						/**
						 * if this is a globbed property type
						 */
						if( count( $propTypeSubsets) > 1 )
						{
							/**
							 * instantiate the glob by passing in all of the subsets for the parent type (sfr2,3,4,etc)
							 */
							$c['glob'] = $propTypeSubsets;
							$glob = $c['propDataGlob'];
							$glob->setIMls( $iMls );
							$iDatasources->$parentType = $glob;
						}
						else
						{
							/**
							 * if this datasource exists, and is not a glob
							 */
							if( $tempDatasourceArray[$parentType][0] )
							{
								$iDatasources->$parentType = $tempDatasourceArray[$parentType][0];
							}
						}
					}

					/**
					 * only for testing.  create an images datasource and add it to datasources
					 */
					$images = $c['imageData'];
					$images->setIMls( $iMls );
					$iDatasources->images = $images;
					return $iMls;
			};

				/**
				 * closure for the iDatasourceModel
				 */
				self::$DI['iDatasourceModel'] = function($c) use( $cadID ) {

					/**
					 * for custom model class.  If you use a custom model class (defined in the config), here
					 * is where that custom class is instantiated.
					 *
					 * namespace for default model
					 */
					$modelNamespace = "Converter\\System\\Datasource\\Sql\\";
					if( $mlsConfig = base::getIConfig()->getSetting( $cadID ) )
					{

						$modelPath = "/home/caddevac/Projects/converter/system/extension/$cadID/";
						/**
						 * the namespace should be System\Extension\[v2ID], but they can specify a namespace in the
						 * config file
						 */
						if( isset( $mlsConfig->model->namespace->value ) )
						{
							$modelNamespace = $mlsConfig
								->model
								->namespace
								->value;
						}

						/**
						 * if they still insist on overriding the path, it is done here.
						 */
						if( isset( $mlsConfig->model->path->value ) )
						{
							$modelPath = $mlsConfig
								->model
								->path
								->value;

						}


						/**
						 * if we are using a custom model class, set model path to look in the extension
						 * directory by default
						 */
						if( isset( $mlsConfig->model->class->value ) )
						{
							$modelNamespace = "Converter\\System\\Extension\\";
							$modelPath = "/home/caddevac/Projects/converter/system/extension/$cadID/";
							$modelClass .= $mlsConfig
								->model
								->class
								->value;
							require_once($modelPath . 'model.php');
						}
						else
						{
							$modelClass .= "model";
						}

					}
					else
					{
						$modelClass .= "model";
					}

					/**
					 * by default, look for the model in system/extension/[v2ID]/
					 * 
					 * if the model.path is not specified in the config, look in the default
					 * location
					 */

					$modelClass = $modelNamespace . $modelClass;
					$iDatasourceModel = new $modelClass();

					return $iDatasourceModel;
			};

				/**
				 * for v1 property types that are globbed together.  for example, a v1 mls that has
				 * multiple sfr types (sfr,sfr2,etc) or multiple mfr types (mfr,mfr2)
				 */
				self::$DI['propDataGlob'] = function($c) {
					$propDataGlob = new propDataGlob( $c['glob'] );
					$globModel = new globModel();
					$globModel->setIDatasource( $propDataGlob );
					$propDataGlob->setIDatasourceModel( $globModel );
					return $propDataGlob;
				};

				/**
				 * closure for the iOutput
				 */
				self::$DI['iOutput'] = function($c) {

				/**
				 * get the global config
				 */
				$globalConfig = base::getIConfig()
					->getSetting('global');
				
				$dataPath = $globalConfig->data
					->file
					->path
					->value;

				$iOutput = new csvOutput();

				/**
				 * set the data path.  the output closure should be responsible for this.  i was previously doing this
				 * in the iDatasource closure
				 */

				$iOutput->setPath( $dataPath );
				$iOutput->setState( 1 );

				return $iOutput;
			};

				/**
				 * closure for the imageData
				 */
				self::$DI['imageData'] = function($c) {

				$image = new imageData();
				$imageModel = new imageModel();

				$image->setIDatasourceModel( $imageModel );
				$imageModel->setIDatasource( $image );
				
				$iOutput = $c['iOutput'];

				/**
				 * make the outputter aware of the datasource that it will output for
				 * the outputter is a protected property on an iDatasource (or property type resource)
				 * an outputter writes the data to a file.  this is done on a per resource basis
				 * if v2 has 15 different resources (or 15 property tables in the database for example
				 * 20131313-1 , 20131313-1 , .. 20131313-15), there will be 15 outputters in the system
				 */
				$iOutput->setContainer( $image );
				$image->setIOutput( $iOutput );

				return $image;
				};


				/**
				 * closure for the iSql
				 */
				self::$DI['iSql'] = function($c) {

				$iSql = new sql();

				return $iSql;
			};

				/**
				 * closure for the iCallbacks.  will instantiate and return all of the iCallbacks for this property type
				 * both default and custom
				 */
				self::$DI['iCallbacks'] = function($c) {


				/**
				 * test to see if callbacks exist.  if not, do not set the callbacks
				 */
				$callbacksExist = base::getIConfig()
					->getSetting('global')
					->callback;
				if( !$callbacksExist )
				{
					return;
				}

				/**
				 * get the global callbacks
				 */
				$iCallbacks = new \stdClass;
				$globalCallbacks = base::getIConfig()
					->getSetting('global')
					->callback
					->postprocess
					->class;

				/**
				 * get the global callbacks
				 */

				$callbackNamespace = "Converter\\System\\Callback\\";
				foreach( $globalCallbacks as $callbackKey => $callbackConfig )
				{

					/**
					 * include and instantiate the callbacks
					 */
					$callbackName = $callbackConfig->value;
					include_once( "/home/caddevac/Projects/converter/callback/$callbackName.php" );
					$fullCallbackName = $callbackNamespace . $callbackName;
					$iCallbacks->{$callbackName} = new $fullCallbackName();
				}

				return $iCallbacks;

			};

				/**
				 * closure for the iDatasource
				 */
				self::$DI['propData'] = function($c) use ( $cadID ) {


					/**
					 * maybe should use a factory or something else in here.  hardcoding the datasource will limit flexibility
					 */

					$mlsSettings = base::getIConfig()
						->getSetting($cadID);

					/**
					 * get the global settings.  will use this for
					 * 1. setting the data file path for this property type
					 */
					$globalConfig = base::getIConfig()
						->getSetting('global');

					/**
					 * property type for this datasource
					 */
					$propertyType = $c['propertyTypeInfo']->v1Pt;

					/**
					 * remember, the config has settings such as:
					 *
					 * [a001]
					 * datasource.propertytype.sfr.class  = singleFamily
					 * datasource.propertytype.mfr.class  = multiFamily
					 *
					 * Here, I am checking the propertytype config to see if the datasource (remember, a datasource == a property type )
					 * has a custom class
					 */


					if( isset( $mlsSettings->datasource->propertytype->$propertyType ) )
					{


						/**
						 * require the file with the custom class.  Remember, the system is expecting
						 * this datasource file to be located at /extension/<v2Id>/propData.php
						 */
						$fullClassName = "";
						if( file_exists( "/home/caddevac/Projects/converter/system/extension/$cadID/propData.php" ) )
						{
							$customClassName = $mlsSettings
								->datasource
								->propertytype
								->{$propertyType}
								->class
								->value;

							$fullClassName = 'Converter\\System\\Extension\\' . $customClassName;
							require_once( "/home/caddevac/Projects/converter/system/extension/$cadID/propData.php" );
						}
						/**
						 * if they specify a custom class and the datasource.php file does not exist, use the default
						 */
						else
						{
							/**
							 * @todo
							 * should do some sort of logging here
							 */

							$noCustomClassWarning = "Custom datasource specified in the config, but propData.php does not exist for $propertyType";
							base::getILoggerByID('global')->logWarning( $noCustomClassWarning . $cadID);
							$fullClassName = "Converter\\System\\Datasource\\propData";
						}

						/**
						 * they have specified a custom class, and the class does exist
						 */
						if( class_exists( $fullClassName ) )
						{
							$iDatasource = new $fullClassName();
						}
						else
						{
							/**
							 * @todo
							 * should do some sort of logging here
							 *
							 * the custom class does not exist.  load the default class
							 */
							$fullClassName = "Converter\\System\\Datasource\\propData";
							$iDatasource = new $fullClassName();
							$noCustomClassWarning = "Custom datasource specified in the config, but the class does not exist for $propertyType";
							base::getILoggerByID('global')->logWarning( $noCustomClassWarning . " - " . $cadID);
							base::cout( "custom class specified, but the class does not exist. Warning logged. \n" );
						}

					}
					/**
					 * if a custom datasource class has not been specified, use the default datasource
					 */
					else
					{
						$iDatasource = new propData();
					}

					/**
					 * grab the appropriate outputter for this datasource
					 */
					$iOutput = $c['iOutput'];

					/**
					 * make the outputter aware of the datasource that it will output for
					 * the outputter is a protected property on an iDatasource (or property type resource)
					 * an outputter writes the data to a file.  this is done on a per resource basis
					 * if v2 has 15 different resources (or 15 property tables in the database for example
					 * 20131313-1 , 20131313-1 , .. 20131313-15), there will be 15 outputters in the system
					 */
					$iOutput->setContainer( $iDatasource );



					$loggerClass    = "Converter\\Logger\\" . $globalConfig->logger
					->class
					->value;
					
					$dataPath = $globalConfig->data
						->file
						->path
						->value;

					/**
					 * by default, i provide 2 loggers.  the global logger (the path points to the global log file),
					 * and the mls level logger (the path points to the data directory for this mls)
					 */

					$mlsLogger = new $loggerClass($dataPath . $cadID . "/");
					base::getILogger()->mls = $mlsLogger;

					/**
					 * give the datasource an outputter
					 */
					$iDatasource->setIOutput( $iOutput );

					/**
					 * get the callbacks for this property type
					 */
					$iCallbacks = $c['iCallbacks'];

					/**
					 * only call setters for callbacks if there are callbacks to set
					 */
					if( $iCallbacks )
					{
						/**
						 * make each of the callbacks aware of its iDatasource
						 */
						foreach( $iCallbacks as $iCallback )
						{
							$iCallback->setIDatasource( $iDatasource );
						}

						/**
						 * set the iCallbacks on the iDatasource
						 */
						$iDatasource->setICallbacks( $iCallbacks );
					}
					
					return $iDatasource;
			};
		}
		
		/**
		 * console output function.  prints message to the console
		 */
		public function cout($message)
		{
			echo $message;
			return;
		}

		public function getDI()
		{
			return self::$DI;
		}
	}

?>
