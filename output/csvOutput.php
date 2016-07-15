<?php

	namespace Converter\Output;

	require_once( "/home/caddevac/Projects/converter/interfaces.php" );
	require_once( "/home/caddevac/Projects/converter/output/abstractOutput.php" );
	
	use Converter\Interfaces\iDatasource                               as iDatasource;
	use Converter\Interfaces\iMls                                      as iMls;
	use Converter\Interfaces\iSql                                      as iSql;
	use Converter\Interfaces\iContained                                as iContained;
	use Converter\Interfaces\iOutput					                         as iOutput;
	use Converter\Output\abstractOutput				                         as abstractOutput;



	class csvOutput extends abstractOutput implements iOutput
	{


		public function __construct()
		{

		}

		/**
		 * this function is declared as abstract in abstractOutput.  Must be implemented by child classes
		 */
		protected function createOutputFilename()
		{
			/**
			 * get the v1 property type.  as of now, each resource will write its own file.
			 */

			$outputFilePath = null;

			/**
			 * the DI container should have set a global folder for the outputters to write to
			 */
			if( $this->state >= self::STATE_FILE_READY )
			{
				$outputFilePath = $this->path;
			}
			
			
			/**
			 * the output path should be:
			 * [global.data.file.path] + cadID + v1Pt
			 */


			/**
			 * get the cadID
			 */
			$cadID = $this->container
				->getIMls()
				->getCadID();

			/**
			 * if the data directory does not exist, create the directory, and then log the event
			 */
			if (!file_exists( $outputFilePath . $cadID)) 
			{
				mkdir($outputFilePath . $cadID);

				/**
				 * log the fact that a new directory has been created
				 */
				$iLogger = $this->container
				  ->getIMls()
				  ->getILoggerByID('mls');

				$outputPathNoExist = "The data path for $cadID did not exist ($outputFilePath . $cadID).  It has been created";
				$iLogger->logNotice( $outputPathNoExist );

			}

			$iDatasourceClass = end( explode('\\', get_class($this->container)) );


			/**
			 * get the v1 proptype id
			 */

			if( $iDatasourceClass == 'propData' )
			{
				$outputterFileName = $this->container
					->getPropertyTypeInfo()
					->v1Pt;
			}
			else
			{
				$outputterFileName = $iDatasourceClass;
			}
			/**
			 * create and return the csv path filename
			 */
			return $outputFilePath . "/" . $cadID . "/" . $outputterFileName . ".csv";

		}
		
		
		public function output( $content )
		{
			/**
			 * before writing the line, unjson the fields that are json'd
			 */
			fputcsv( $this->fileHandle , $content );
      return;
		}

	}

?>
