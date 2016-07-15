<?php

/**
 * a hodgepodge of data functions that can be used from anywhere
 * I don't want to muck up my classes with sql, so I am going to stick sql
 * stuff in here as intuitively as possible
 */

namespace Converter\Util;

require_once( "/var/www/acq/parentClasses/defaults.php" );
require_once( "/home/caddevac/Projects/converter/exception/exceptions.php" );

use Converter\Exceptions\NoFieldMigrationException as NoFieldMigrationException;
	
	
class util extends \defaults
{

	/**
	 * controls which table we use.  if in dev mode use processLogTest table, and when populating real data
	 * use processLog table
	 */

	public function __construct()
	{
		parent::__construct();
	}


	/**
	 * gets the property types for an mls.  
	 * 
	 * @todo should the util class be responsible for creating this query?  Maybe the mls should be responsible for creating the
	 * query, and then passing it in.  
	 *
	 * maybe some class can extend uitl and create the queries.  I really prefer not have sql in the classes.
	 */
	public function getPropertyTypes( $cadID )
	{
		$propertyTypesSQL = "SELECT `v1PropTypeTranslation`.`v1Pt`,`v1PropTypeTranslation`.`v2Pt` FROM `mls`.`v1PropTypeTranslation` where v2cadID = '$cadID'";


		$propertyTypesResult = mysql_query($propertyTypesSQL,$this->getRes('mls'));

		if( mysql_num_rows( $propertyTypesResult ) < 1 )
		{
			throw new NoFieldMigrationException( "Error in getting data from the v1PropTypeTranslation.  Here is the query that returned zero results: " . $propertyTypesSQL . "This mls possibly needs fields to be migrated.  This error has been logged." );
		}

		return $propertyTypesResult;

	}


	/**
	 * the cadID should be known when this function is called.
	 */
	public function getActiveTables($cadID)
	{

		$return = null;
		/**
		 * need to grab the -all table, and the property type table.  only the active ones
		 */
		$activeTableSQL = "SELECT tableName , mlsPtID FROM `$cadID`.`active` WHERE ( active = 1 AND location='core' ) OR ( active = 1 AND location='advanced' )";
		$activeTableRes = mysql_query($activeTableSQL,$this->getRes($cadID));

		while( $active= mysql_fetch_object($activeTableRes) )
		{
			$return->{$active->mlsPtID} = $active->tableName;
		}
		return $return;
	}

	/**
	 * perform a query
	 */
	public function query( $sql , $databaseName , $index = '' )
	{

		$return = null;
		$queryRes = mysql_query($sql,$this->getRes($databaseName));

		/**
		$count = 0;
		while( $res= mysql_fetch_object($queryRes) )
		{
			$return->{$res->{$index}} = $res;
			$count++;
			echo DYELLOW." --Now doing number " . $count .NC.CLEAR.CR;
		}
		 */
		return $queryRes;
	}

	/**
	 * 
	 */
	public function getProperties()
	{

	}

}


?>
