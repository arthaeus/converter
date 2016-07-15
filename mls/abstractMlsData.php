<?php

	namespace Converter\Mls;

	require_once( "/home/caddevac/Projects/converter/interfaces.php" );
	require_once( "/home/caddevac/Projects/converter/util/util.php" );
	require_once( "/home/caddevac/Projects/converter/exception/exceptions.php" );
	require_once( 'abstractMls.php' );
	
	use Converter\Exceptions\NoCadIdException          as NoCadIdException;
	use Converter\Exceptions\NoAllTableException		   as NoAllTableException;
	use Converter\Exceptions\NoTableException		       as NoTableException;
	use Converter\Exceptions\NoFieldMigrationException as NoFieldMigrationException;
	use Converter\Interfaces\iProcess                  as iProcess;
	use Converter\Interfaces\iMls                      as iMls;
	use Converter\Mls\abstractMls                      as abstractMls;
	use Converter\Util\util                            as util;



	/**
	 * mls's have property types, and property types have listings.
	 * The translateProcess (generic) corresponds with the property type level.
	 */
	
	abstract class abstractMlsData extends abstractMls implements iMls
	{

		/**
		 * the mls should own the property types list.  during the constructor, make the mls aware of its 
		 * property types
		 */
		public function __construct( $cadID )
		{

			abstractMls::__construct();
			/**
			 * create the abstract mls.  the abstract mls (mls is core just like the datasource) extends
			 * base for access to the DI container
			 */

			$this->cadID = $cadID;

			try
			{
				if( !$cadID )
				{
					throw new NoCadIdException;
				}
			}
			catch( NoCadIdException $e )
			{
				print_r( $e );
			}
			
			//$this->propertyTypes = $this->fetchPropertyTypes();


			/**
			 * create a datasource for each propertyType.  use the factory to do this.
			 * should the factory return a populated datasource?
			 *
			 * the translation variable is the object that is returned from the getPropertyTypes call
			 * the translation variable is a simple object with two properties: v1Pt and v2Pt
			 * I made the key to this->propertyTypes the v2 ptID.
			**/

			return;
		}

		/**
		 * return the property types for the mls
		 * 
		 * in the end, I think it makes sense to only call this function when the api has a request
		 * for data.  Not when it has a request for images.
		 */
		public function getPropertyTypeInfo() 
		{ 

			/**
			 * get the active tables for this mls.  this is needed to build the query to grab the data
			 * should throw exceptions if each property type does not have an active table.
			 * also throw an exception if there is no -all table
			 */
			$activeTables = self::$util->getActiveTables( $this->cadID );
			
			$propertyTypesRes = $this->fetchPropertyTypes();

			/**
			 * The key will be the v2 prop type.
			 *
			 * @todo how simple would it be to go from v1-> v2?  this is being programmed to go one way, but 
			 * could need it to work both ways
			 *
			 */
			$propertyTypes = null;


			while( $pt = mysql_fetch_object($propertyTypesRes) )
			{
				$propertyTypes->{$pt->v2Pt} = $pt;
			}
				

			foreach( $propertyTypes as $v2 => $translation )
			{

				/**
				 * make sure that there is an active table for each property type.  if not, throw exception
				 */
				try
				{
					if( !$activeTables->{$v2} )
					{
						throw new NoTableException;
					}
				}
				catch( NoTableException $e )
				{
					echo $e->errorMessage();
				}
				
				/**
				 * the active -all table has a ptID of '0' and active = 1.  activeTables is an object where
				 * the properties are the different ptIDs.
				 */
				try
				{
					if( !$activeTables->{0} )
					{
						throw new NoAllTableException;
					}
				}
				catch( NoAllTableException $e )
				{
					echo $e->errorMessage();
				}

				/**
				 * no exceptions, so  add the active table name, and all table name to propertyTypes
				 */
				$propertyTypes->{$v2}->activeTable = $activeTables->{$v2};
				$propertyTypes->{$v2}->allTable    = $activeTables->{0};
			}
			return $propertyTypes;
		}

		/**
		 * i think that the mls should be responsible for fetching its property types.  this function
		 * fetches the info from the database.  This is not a getter function.  There is a getPropertyTypes
		 * to return this->propertyTypes.  This function just requests and returns information regarding the
		 * property types of this mls
		 */
		protected function fetchPropertyTypes()
		{
			/**
			 * the mls should own the property types.  Set my property types
			 */
			$propertyTypes = null;
			try
			{
				$propertyTypes = self::$util->getPropertyTypes( $this->cadID );
			}
			catch( NoFieldMigrationException $e )
			{
				/**
				 * at this point, there is no need to go on.  we need the prop type translations in order to process
				 * log the error, and the nexit
				 */
				$e->logException( base::getLoggerByID('global') ); 
				$e->handleException();
			}

			return $propertyTypes;
		}
	}

?>
