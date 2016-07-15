<?php

	namespace Converter\System\Datasource\Model;

	require_once( "/home/caddevac/Projects/converter/interfaces.php" );
	require_once( "/home/caddevac/Projects/converter/system/datasource/datasourceBase.php" );
	require_once( "/home/caddevac/Projects/converter/util/util.php" );
	
	use Converter\Interfaces\iDatasource                               as iDatasource;
	use Converter\Interfaces\iMls                                      as iMls;
	use Converter\Interfaces\iSql                                      as iSql;
	use Converter\Interfaces\iMlsSql                                   as iMlsSql;
	use Converter\System\Datasource\abstractDatasourceBase             as abstractDatasourceBase;
	use Converter\Interfaces\iContained                                as iContained;
	use Converter\Util\util                                            as util;

	abstract class abstractModel extends abstractDatasourceBase implements iSql , iContained
	{

		const NOT_READY   = "NOT_READY";
		const BUILD_READY = "BUILD_READY";

		final public function getIDatasource() { return $this->getContainer(); }
		final public function setIDatasource(iDatasource $d) { $this->setContainer($d ); return; }

		/**
		 * this function will grab the field names from fieldDefinitions for this table
		 */
		protected function getIDatasourceFields()
		{
				$propertyTypeInfo = $this->getIDatasource()
					->getPropertyTypeInfo();

				$propertyTypeSql = "SELECT";
		}


		/**
		 * this function asks its iDatasource for its data, and then inspects the data to extract the headers.
		 * the headers are returned as an array
		 *
		 * there are 2 types of headers.  
		 * 1. the mls provides headers in the data
		 * 2. cad can choose to (or choose not to) map these fields, giving them an 'cad' header
		 *
		 * the header type param determines which type of headers are returned
		 *
		 * here is an example of the array that the headerType param will control.  
				[id] => 136
				[mlsName] => 690
				[name] => acres
				[location] => core
				[mlsPtID] => 4
				[parentPtID] => 5
		 *
		 * if you pass the value 'name' to the function, the function will return an array of cad headers (the value 'acres' in this examplt)
		 * if you pass the value 'mlsName' to the function, the function will return an array of mls headers (the value '690' in this example)
		 *
		 * and obviously if you pass in one of the other values, something unexpected will happen, so don't do that
		 */
		public function getHeaders( $headerType )
		{


			/**
			 * write the headers.  do this by looping through the metadata for the resultset
			 * (this->data) and matching the field name (what we call the field) with what
			 * the mls calls the field.  Remember, we have access to this->fieldDefinitions
			 *
			 * mysql_fetch_field will return objects like:
			 *
			 * stdClass Object
					(
							[name] => vowComments
							[table] => 20130507-5
							[def] => 
							[max_length] => 3
							[not_null] => 1
							[primary_key] => 0
							[multiple_key] => 0
							[unique_key] => 0
							[numeric] => 0
							[blob] => 0
							[type] => string
							[unsigned] => 0
							[zerofill] => 0
						)

					an entry in this->fieldDefinitions (this->fieldDefinitions is a stdClass) will look like:
					[acres] => stdClass Object
							(
									[id] => 136
									[mlsName] => 690
									[name] => acres
									[location] => core
									[mlsPtID] => 4
									[parentPtID] => 5
							)

			 */

			$fieldCount = 0;
			$headers = array();

			/**
			 * get the data from the iDatasource
			 */
			$data = $this->getIDatasource()
				->getData();

			while ($fieldCount < mysql_num_fields($data))
			{
				$meta = mysql_fetch_field($data, $fieldCount);
				$headers[] = $this->getIDatasource()
					->getFieldDefinitions()
					->{$meta->name}
					->{$headerType};

				$fieldCount++;
			}
			/**
			$heads = print_r( $this->getIDatasource()->getFieldDefinitions() , true );
			$this->getIDatasource()
			 ->getIMls()
			 ->getILoggerByID('mls')
			 ->logNotice( "headers for " . $this->propertyTypeInfo->v1Pt . $heads );
			 */
			return $headers;

		}

		public function getFieldDefinitions()
		{
			/**
			 * get at the cadID by getting the datasource, which gets the mls, which owns the cadID
			 */
			$cadID = $this->getIDatasource()
				->getIMls()
				->getCadID();

			/**
			 * get the property type by querying the iDatasource for it
			 */
			$propertyType = $this->getIDatasource()
				->getPropertyTypeInfo()
				->v2Pt;
			
			$fieldDefSql = "SELECT id, mlsName , name , location , mlsPtID , parentPtID FROM `$cadID`.`fieldDefinitions` WHERE mlsPtID = '$propertyType'";

			/**
			 * the query function returns the actual query resultset object, which has not been iterated over.
			 */
			$fieldDefResults = self::$util->query( $fieldDefSql , $cadID , 'name');
			return $fieldDefResults;
		}


		public function getData( $propertyTypeInfo )
		{

			/**
			 * get at the cadID by getting the datasource, which gets the mls, which owns the cadID
			 */
			$cadID = $this->getIDatasource()
				->getIMls()
				->getCadID();


			/**
			 * get info that will be used to fetch the data.  get this info from propertyTypeInfo
			 */
			$propertyType = $propertyTypeInfo->v2Pt;
			$activeTable  = $propertyTypeInfo->activeTable;
			$allTable     = $propertyTypeInfo->allTable;

			//select active.* from `20130415-1` active left outer join `20130415-all` al on active.listingID = al.listingID;	
			$getDataSql = "SELECT `$cadID`.`$activeTable`.* , `$cadID`.`$allTable`.* FROM `$cadID`.`$activeTable` LEFT OUTER JOIN `$cadID`.`$allTable` ON `$cadID`.`$activeTable`.`listingID` = `$cadID`.`$allTable`.`listingID`";

			return	self::$util->query( $getDataSql , $cadID , 'listingID');

		}

	}

?>
