<?php

	namespace Converter\System\Datasource\Model;

	require_once( "/home/caddevac/Projects/converter/interfaces.php" );
	require_once( "/home/caddevac/Projects/converter/system/datasource/abstractSpecialModel.php" );
	require_once( "/home/caddevac/Projects/converter/util/util.php" );
	
	use Converter\Util\util                                   as util;
	use Converter\Interfaces\iSql                             as iSql;
	use Converter\Interfaces\iContained                       as iContained;
	use Converter\System\Datasource\Model\abstractSpecialModel      as abstractSpecialModel;

	class imageModel extends abstractSpecialModel implements iSql , iContained
	{

		protected $imageCutoff = null;

		public function getImageCutoff()
		{
			return $this->imageCutoff;
		}

		public function setImageCutoff( DateTime $c )
		{
			$this->imageCutoff = $c;
			return;
		}

		/**
		 * function for getting special data.  Special data can be
		 * 1. images
		 * 2. open
		 * 3. virtual
		 * 4. agent
		 * 5. office
		 */
		public function getData()
		{


			/**
			 * if imageCutoff is not set, then grab all images from the past 5 days
			 */

			if( !$this->imageCutoff )
			{
				$this->imageCutoff = new \DateTime('-5 day');
			}

			$imagesTable = "images";

			/**
			 * get at the cadID by getting the datasource, which gets the mls, which owns the cadID
			 */
			$cadID = $this->getIDatasource()
				->getIMls()
				->getCadID();

			//select active.* from `20130415-1` active left outer join `20130415-all` al on active.listingID = al.listingID;	
			$getImagesSql = "SELECT `$cadID`.`$imagesTable`.listingID , `$cadID`.`$imagesTable`.priority , `$cadID`.`$imagesTable`.url , `$cadID`.`$imagesTable`.caption FROM `$cadID`.`$imagesTable` WHERE lastUpdate >= '" . $this->imageCutoff->format('Y-m-d') . "'";

			return	self::$util->query( $getImagesSql , $cadID );

		}
	}

?>
