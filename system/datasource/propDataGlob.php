<?php

	namespace Converter\System\Datasource;

	require_once( "/home/caddevac/Projects/converter/interfaces.php" );
	require_once( "/home/caddevac/Projects/converter/system/datasource/datasourceBase.php" );
	require_once( "/home/caddevac/Projects/converter/util/util.php" );
	
	use Converter\Interfaces\iDatasource                               as iDatasource;
	use Converter\Interfaces\iMls                                      as iMls;
	use Converter\Interfaces\iSql                                      as iSql;
	use Converter\System\Datasource\abstractDatasourceBase             as abstractDatasourceBase;
	use Converter\Interfaces\iContained                                as iContained;
	use Converter\Util\util                                            as util;



	/**
	 * im extending abstractDatasourceBase instead of abstractDatasource
	 * because abstractDatasource has many things that the glob does not need
	 * and the abstractDatasourceBase contains the behaviors (container/contained)
	 * that any regular datasource will have
	 */
	class propDataGlob extends abstractDatasourceBase implements iDatasource
	{

		protected $dataSubsets       = null;
		protected $iDatasourceModel  = null;
		protected $propertyTypeInfo  = null;

		public function __construct( $dataSubsets )
		{
			/**
			 * set the data subsets
			 */
			$this->dataSubsets = $dataSubsets;

			/**
			 * since this is a glob datatype, the propertyTypeInfo on the glob level will be more generic than each of the individual subsets
			 * all we know about the glob in general is the v1 property type for example:
			 * property type = sfr if the datasubsets are sfr,sfr2,sfr3.  
			 * property type = ld if the subsets are ld, ld2,ld3. 
			 * etc.
			 * etc.
			 * there should be more than one iDataset in dataSubsets.  just grab the first since we should be sure that it exists
			 */

			$v1Pt = $dataSubsets[0]->getPropertyTypeInfo()
				->v1Pt;

			$this->propertyTypeInfo->v1Pt = preg_replace('/\d/', '', $v1Pt);

			$this->state = self::STATE_NULL;
			abstractDatasourceBase::__construct();
		}
    
		public function getPropertyTypeInfo()
		{
			return $this->propertyTypeInfo;
		}
		
		/**
		 * the data subsets are a collection of iDatasources
		 * this function returns them
		 */
		public function getDataSubsets()
		{
			return $this->dataSubsets;
		}

		/**
		 * pass in an array(?) of iDatasources, and set it to this object
		 */
		public function setDataSubsets( $s )
		{
			$this->dataSubsets = $s;
			return;
		}

		public function setIMls(iMls &$m) { $this->setContainer( $m ); }
    public function getIMls()         { return $this->getContainer(); }

		/**
		 * setter
		 */
		public function setIDatasourceModel($m)
		{
			$this->iDatasourceModel = $m;
			return;
		}

		/**
		 * the glob will have get all of the models and store them in a structure
		 */
		public function getIDatasourceModel()
		{
			return $this->iDatasourceModel;
		}

		/**
		 * since this is a globbed data type (sfr,sfr2,sfr3,etc), this function will
		 * simply loop through all of the datasubsets (sfr,sfr2,sfr3,etc), and set field defs for
		 * each of them.
		 */
		public function getFieldDefinitions()
		{
			$this->iDatasourceModel
				->getFieldDefinitions();
			return;
		}

		/**
		 * populate each of the datasources in the glob
		 */
		public function populate()
		{

			/**
			 * get the util.  The mls will have access to the recreate function.  mls extends base
			 * get the mls of this property type, and then get the util from the mls (by way of base)
			 *
			 * will need util for the recreate statement.  util extends defaults
			 */

			$util = $this->getIMls()
				->getUtil();

			/**
			 * loop through the subsets and call the getData method on the models
			 */

      foreach( $this->dataSubsets as $dataSubset )
			{  


				$util->recreate();	
				/**
				 * remember, a subset is an iDatasource (or a property type).  Each iDatasource
				 * has a model.  use the model to get the data.  pass in the iDatasources property type info
				 */
				$data = $dataSubset->getIDatasourceModel()
					->getData($dataSubset->getPropertyTypeInfo() ); 

				$dataSubset->setData( $data );
      }   

			$this->state = self::STATE_POPULATED;
			return;
		}

		public function perform()
		{
			/**
			 * since this is a glob, we can process the iDatasources in parallel
			 */

			$numChildren = 0;
      foreach( $this->dataSubsets as $dataSubset )
			{  
          $pids[$numChildren] = pcntl_fork();
  
          /**
           * in the child process.  do the data
           */
          if( !$pids[$numChildren] )
          {
						$data = $dataSubset->getData();
						$subsetType = $dataSubset->getPropertyTypeInfo()->v1Pt;
						$count = 0;
						

						/**
						 * get the outputter
						 */
						$iOutput = $dataSubset->getIOutput();

						/**
						 * open the file that will be outputted to.  the iOutput is responsible for making decisions
						 * pertaining to the file name / path.  it is able to inspect its surroundings and figure out
						 * these things
						 */
						$iOutput->openFile();

						/**
						 * log how many rows will be processed
						 */
						$dataSubset->getIMls()
						 ->getILoggerByID('mls')
						 ->logNotice( "Will PROCESS " . mysql_num_rows( $data ) . " rows for " . $dataSubset->getPropertyTypeInfo()->v1Pt );			


						/**
						 * grab the headers from the model and output them
						 * there is a list of options for the getHeaders function in the abstractModel.php file
						 */
						$mlsHeaders = $dataSubset->getIDatasourceModel()
							->getHeaders('mlsName');
						
						$dataSubset->getIOutput()
							->output( $mlsHeaders );

				
						while( $listing = mysql_fetch_assoc($data) )
						{
							/** 
							 * write the row to the file
							 */
							$iCallbacks = $dataSubset->getICallbacks();

							if( $iCallbacks )
							{
								foreach( $iCallbacks as $iCallback )
								{
									$iCallback->perform( $listing );
								}
							}


							$iOutput->output( $listing );
							
							//$this->cout( DGREEN." --**GLOB GLOB LISTING number for $subsetType " . $count .NC.CLEAR.CR );
							//$count++;
						}

						/**
						 * close the file
						 */
						$iOutput->closeFile();
						exit;
					}
					/**
					 * in the parent process
					 */
					else
					{
						$numChildren++;
					}
			}


			$procDoneMessage = "done with glob processing for " . $this->getIMls()->getCadID() . " property type " . $this->propertyTypeInfo->v1Pt;
      /**
       * log the fact that the property type has finished processing
       */
      $this->getIMls()
       ->getILoggerByID('mls')
       ->logNotice( $procDoneMessage ); 
      
	
			echo $procDoneMessage . "\n";

		}
	}

?>
