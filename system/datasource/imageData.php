<?php

	namespace Converter\System\Datasource;

	require_once( "/home/caddevac/Projects/converter/interfaces.php" );
	require_once( "/home/caddevac/Projects/converter/system/datasource/specialData.php" );
	require_once( "/home/caddevac/Projects/converter/util/util.php" );
	
	use Converter\Interfaces\iDatasource                               as iDatasource;
	use Converter\Interfaces\iMls                                      as iMls;
	use Converter\Interfaces\iSql                                      as iSql;
	use Converter\System\Datasource\specialData                        as specialData;
	use Converter\Interfaces\iContained                                as iContained;
	use Converter\Util\util                                            as util;



	class imageData extends specialData implements iDatasource
	{

		public function __construct()
		{
			abstractDatasourceBase::__construct();
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
		 * populate the images
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

			$util->recreate();	
			/**
			 * remember, a subset is an iDatasource (or a property type).  Each iDatasource
			 * has a model.  use the model to get the data.  pass in the iDatasources property type info
			 */
			$this->data = $this->getIDatasourceModel()
				->getData(); 

			$this->state = self::STATE_POPULATED;
			return;
		}

		public function perform()
		{

			$data = $this->getData();
			$count = 0;
			

			/**
			 * get the outputter
			 */
			$iOutput = $this->getIOutput();

			/**
			 * open the file that will be outputted to.  the iOutput is responsible for making decisions
			 * pertaining to the file name / path.  it is able to inspect its surroundings and figure out
			 * these things
			 */
			$iOutput->openFile();

			/**
			 * log how many rows will be processed
			 */
			$this->getIMls()
			 ->getILoggerByID('mls')
			 ->logNotice( "Will PROCESS " . mysql_num_rows( $data ) . " rows for images" );			


			/**
			 * grab the headers from the model and output them
			 * there is a list of options for the getHeaders function in the abstractModel.php file
			 */
			
			$cadHeaders = $this->getIDatasourceModel()
				->getHeaders('images');


			$this->getIOutput()
				->output( $cadHeaders );
	
			while( $listing = mysql_fetch_assoc($data) )
			{
				/** 
				 * write the row to the file
				 */
				$iOutput->output( $listing );
			}

			/**
			 * close the file
			 */
			$procDoneMessage = "done with image processing for " . $this->getIMls()->getCadID();
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
