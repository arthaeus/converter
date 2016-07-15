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

	abstract class abstractSpecialModel extends abstractDatasourceBase implements iSql , iContained
	{

		const NOT_READY   = "NOT_READY";
		const BUILD_READY = "BUILD_READY";

		final public function getIDatasource() { return $this->getContainer(); }
		final public function setIDatasource(iDatasource $d) { $this->setContainer($d ); return; }

		public function getHeaders( $headerType )
		{

			$data = $this->getIDatasource()
				->getData();

			$headers = array();
			$fieldCount = 0;
			while ($fieldCount < mysql_num_fields($data))
			{
				$meta = mysql_fetch_field($data, $fieldCount);
				$headers[] = $meta->name;

				$fieldCount++;
			}
			return $headers;

		}


		/**
		 * i am making this abstract because the queries for the special data
		 * (images,office,open,agent,virtual) will be different.
		 */
		//abstract public function getData();

		/**
		 * function for getting special data.  Special data can be
		 * 1. images
		 * 2. open
		 * 3. virtual
		 * 4. agent
		 * 5. office
		public function getData()
		{

			$iDatasourceClass = end( explode( '\\' , get_class( $this->getIDatasource() ) ) );

			$specialTable = null;
			switch( $iDatasourceClass )
			{
				case "imageData":
					$specialTable = "images";
				break;

				default:
					$specialTable = "images";
				break;
			}

			**
			 * get at the cadID by getting the datasource, which gets the mls, which owns the cadID
			 *
			$cadID = $this->getIDatasource()
				->getIMls()
				->getCadID();

			//select active.* from `20130415-1` active left outer join `20130415-all` al on active.listingID = al.listingID;	
			$getDataSql = "SELECT `$cadID`.`$specialTable`.*  FROM `$cadID`.`$specialTable`";

			return	self::$util->query( $getDataSql , $cadID );

		}

		 */
	}

?>
