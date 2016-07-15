<?php

	namespace Converter\System\Callback;
	
	require_once( "/home/caddevac/Projects/converter/interfaces.php" );
	use Converter\Interfaces\iCallback as iCallback;

	class unjson implements iCallback
	{

		protected $lookupAdvancedFields = null;

		/**
		 * the iDatasource that the unjson belongs to
		 */
		protected $iDatasource = null;

		public function __construct()
		{
			/**
			 * only the lookupAdvanced fields need to be unjson'e.  figure out these fields and store them.
			 */
		}

		/**
		 * todo rethink this init method.  init should either go on the interface, should be an abstract
		 * function, or something similar.
		 */
		public function init()
		{

			//if this class is already init'd, just return the lookup advanced fields
			if( $this->lookupAdvancedFields )
			{
				return;
			}

			$this->lookupAdvancedFields = new \stdClass;
			$fieldDefs = $this->getIDatasource()
				->getFieldDefinitions();

			foreach( $fieldDefs as $fieldDef )
			{
				if( $fieldDef->location == 'lookupAdvanced' )
				{
					$this->lookupAdvancedFields->{$fieldDef->mlsName} = $fieldDef->name;
				}
			}
		}

		public function getLookupAdvancedFields()
		{
			return $this->lookupAdvancedFields;
		}

		public function setLookupAdvancedFields( $l )
		{
			$this->lookupAdvancedFields = $l;
			return;
		}

		public function getIDatasource()
		{
			return $this->iDatasource;
		}

		public function setIDatasource( $d )
		{
			$this->iDatasource = $d;
			return;
		}


		public function perform( &$listing )
		{
			/**
			 * init it if it has not been
			 */
			$this->init();

			foreach( $this->lookupAdvancedFields as $key => $field )
			{
				$djsonified = json_decode( $listing[$field] );
				if( is_array( $djsonified ) )
				{
					$listing[$field] = implode( "," , $djsonified );
				}
			}
		}
	}

?>
