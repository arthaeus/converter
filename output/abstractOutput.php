<?php

	/**
	 * responsible for things having to do with the outputting of data
	 *
	 * right now i think that the iOutput should be responsible for deciding the path and
	 * filename of the output file.  It has access to its containing object (the iDatasource)
	 * so there may be no reason to have the datasource pass in (what I am... maybe was doing)
	 * path/filename information to the openFile function.  Why would/should the datasource care
	 * about the filename/path?
	 *
	 * more thinking.  an iOutput create and owns information that other parts of the system need.  
	 */
	namespace Converter\Output;

	require_once( "/home/caddevac/Projects/converter/interfaces.php" );
	require_once( "/home/caddevac/Projects/converter/exception/exceptions.php" );
	
	use Converter\Interfaces\iDatasource                               as iDatasource;
	use Converter\Interfaces\iMls                                      as iMls;
	use Converter\Interfaces\iSql                                      as iSql;
	use Converter\Interfaces\iContained                                as iContained;
	use Converter\Interfaces\iOutput					                         as iOutput;



	/**
	 * responsible for everything to do with outputting.  jsoning/dejsoning, figuring out headers
	 */
	abstract class abstractOutput 
	{

		/**
		 * after the DI container creates the iOutput, it will set the state of the iOutput to state file ready
		 */
		const STATE_FILE_READY     = 1;
		const STATE_FILE_OPEN      = 2;
		const STATE_FILE_CLOSED    = 3;


		/**
		 * the container is an iDatasource (or property type)
		 * in other words, an outputter belongs to property resource because each property resource will
		 * need to output its data for export to v1
		 */
		protected $container  = null;

		protected $fileHandle = null;
		protected $fileName   = null;
		protected $state      = null;

		/**
		 * path is set in the DI container (in base.php) when the iOutput is created
		 */
		protected $path       = null;

		public function __construct()
		{
		}


		/**
		 * creating the output filename will be decided by what is trying to create an output file.  data? images?
		 */
		protected abstract function createOutputFilename();

		/**
		 * getters and setters
		 */

		public function getState()
		{
			return $this->state;
		}

		public function setState( $s )
		{
			$this->state = $s;
			return;
		}

		public function getPath()
		{

			return $this->path;
		}
		
		public function setPath( $p )
		{
			$this->path = $p;
			return;
		}

		/**
		 * getters and setters
		 */
		protected function getFileHandle()
		{

			return $this->fileHandle;
		}
		
		protected function setFileHandle( $f )
		{
			$this->fileHandle = $f;
			return;
		}

		public function setFileName( $f )
		{
			$this->state = self::STATE_FILE_READY;
			$this->fileName = $f;
			return;
		}

		public function getFileName()
		{
			return $this->fileName;
		}


		public function openFile( $fileName=null , $mode="w" )
		{


			$this->fileName = $this->createOutputFilename();
			/**
			 * if we don't have a filename, we can't open the file.  the filename in this context
			 * is the filename that will be used on the datafile for this resource
			 */
			if( $this->state < self::STATE_FILE_READY )
			{
				/**
				 * todo
				 * exception handling
				 */
				echo "i don't know a file name for this pt";
				return;
			}

			/**
			 * the setter has been called for the filename
			 */
			if( $this->fileName )
			{
				$this->fileHandle = fopen( $this->fileName , $mode );
			}
			/**
			 * the setter has not been called for the filename, and the filename
			 * is passed in as a param to this function
			 */
			else if( !$this->fileName )
			{
				$this->fileHandle = fopen( $fileName , $mode );
			}
			else
			{
				//todo exception
				echo "error";
			}

			$this->state = self::STATE_FILE_OPEN;
			return;
		}

		public function closeFile()
		{
			fclose( $this->fileHandle );
			$this->state = self::STATE_FILE_CLOSED;
			return;
		}
		
		public function setIDatasource( iDatasource $d )
		{
			$this->iDatasource = $d; 
		}

		public function getIDatasource()
		{
			return $this->iDatasource; 
		}

		public function setContainer(&$c)
		{
			$this->container = $c;
			return;
		}

		public function getContainer()
		{
			return $this->container;
		}

	}

?>
