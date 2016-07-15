<?php

	namespace Converter\System\Datasource;

	require_once( "/home/caddevac/Projects/converter/interfaces.php" );
	require_once( "/home/caddevac/Projects/converter/system/datasource/abstractPropertyType.php" );
	require_once( "/home/caddevac/Projects/converter/util/util.php" );
	
	use Converter\Interfaces\iDatasource                    as iDatasource;
	use Converter\System\Datasource\abstractDatasource      as abstractDatasource;
	use Converter\Interfaces\iContained                     as iContained;
	use Converter\Util\util                                 as util;

	/**
	 * handles the processing for one iDatasource (or property resource - sfr or ld2 or bo5 )
	 */
	class propData extends abstractPropertyType implements iDatasource
	{
		public function __construct() 
		{
			abstractPropertyType::__construct();
		}

		/**
		 * this function will actually write the data to a file
		 */
		public function perform()
		{
			/**
			 * get the property type
			 */

			$propertyType = $this->getPropertyTypeInfo()
				->v1Pt;


			/**
			 * maybe opening the file should be a part of perform method?  open and close the file right
			 * here?
			 * HARD CODING .CSV IN
			 */
			$this->iOutput
				->openFile();

			$count = 0;
			if( $this->data )
			{  

				/**
				 * log how many rows will be processed
				 */
				$this->getIMls()
				 ->getILoggerByID('mls')
				 ->logNotice( "Will PROCESS " . mysql_num_rows( $this->data ) . " rows for " . $this->getPropertyTypeInfo()->v1Pt );			
 
				/**
				 * grab the headers from the model and output them
				 * there is a list of options for the getHeaders function in the abstractModel.php file
				 */
				$mlsHeaders = $this->iDatasourceModel
					->getHeaders('mlsName');

				$this->iOutput
					->output( $mlsHeaders );

				/**
				 * loop through this->data
				 */
				while( $listing = mysql_fetch_assoc($this->data) )
				{   

					//print_r( $this->iCallbacks );
					//die;
					/** 
					 * write the row to the file
					 */
					$iCallbacks = $this->getICallbacks();

					if( $iCallbacks )
					{
						foreach( $iCallbacks as $iCallback )
						{
							$iCallback->perform( $listing );
						}
					}


					$this->iOutput
						->output( $listing );

					//$count++;
					//$this->cout( DYELLOW." --**Now doing LISTING number for $propertyType " . $count .NC.CLEAR.CR );
				}   
				echo "\n\n";
			}   
			else
			{   

				/**
				 * log the fact that there are no properties
				 */
				$this->getIMls()
				 ->getILoggerByID('mls')
				 ->logNotice( "no properties for " . $this->getPropertyTypeInfo()->v1Pt);			
			}   


			/**
			 * log the fact that the property type has finished processing
			 */
			$this->getIMls()
			 ->getILoggerByID('mls')
			 ->logNotice( "done with $propertyType for " . $this->getIMls()->getCadID() );
			
			
			echo "done with $propertyType for " . $this->getIMls()->getCadID();
			echo "\n";
		}

	}

?>
