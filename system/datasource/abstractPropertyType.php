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


	abstract class abstractPropertyType extends abstractDatasourceBase implements iDatasource
	{


		/**
		 * a datasource corresponds with a property type.  If an mls has 5 property types, there 
		 * will be 5 sources of data.
		 */
		protected $propertyTypeInfo = null;

		/**
		 */
		protected $iCallbacks        = null;

		/**
		 * field definitions entries for this datasource
		 */
		protected $fieldDefinitions  = null;

		/**
		 * upon construct, the datasource should query the database, and populate itself with data.
		 * also remember that the datasource is contained within the mls.  set a ref to the mls.
		 */
		public function __construct( )
		{
			$this->state = self::STATE_NULL;
			$this->iCallbacks = new \stdClass;
			abstractDatasourceBase::__construct();
		}
    
		public function setICallbacks($c) {  $this->iCallbacks = $c; }
    public function getICallbacks()        { return $this->iCallbacks; }
		
		public function setFieldDefinitions($f) 
		{  
			$this->fieldDefinitions = $f;
			$this->state = self::STATE_FIELDDEFS;
			return;
		}
		public function getFieldDefinitions()   
		{ 
			return $this->fetchFieldDefinitions(); 
		}

		/**
		 * actually fetch the field definitions from the database.
		 */
		public function fetchFieldDefinitions()
		{
			if( $this->fieldDefinitions )
			{
				return $this->fieldDefinitions;
			}

			/**
			 * remember, the model classes return a mysql resource.  in this case, it is the datasources
			 * responsibility to act upon the resultset
			 */
			$fieldDefinitions = $this->getIDatasourceModel()
				->getFieldDefinitions(); 

			$count = 0;

			$fieldDefs = null;
			while( $fd = mysql_fetch_object($fieldDefinitions) )
			{   
				$fieldDefs->{$fd->name} = $fd;
				//$count++;
				//$this->cout( DRED." --Now doing field Def number " . $count .NC.CLEAR.CR );
			}   

			//echo "\n\n";
			$this->setFieldDefinitions( $fieldDefs );
			return $this->fieldDefinitions;
		}
		
		public function setIMls(iMls &$m) { $this->setContainer( $m ); }
    public function getIMls()         { return $this->getContainer(); }

		public function setPropertyTypeInfo($t) 
		{ 
			$this->propertyTypeInfo = $t; 
			$this->state = self::STATE_PROPTYPEINFO;
			return; 
		}

		public function getPropertyTypeInfo()   { return $this->propertyTypeInfo; }

    /** 
     * fill this datasource with data.
     */
    public function populate()
    {   
      $this->data = $this->iDatasourceModel->getData( $this->propertyTypeInfo );
      $this->state = self::STATE_POPULATED;
      return;
    }   


	}

?>
