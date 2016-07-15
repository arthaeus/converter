<?php

	namespace Converter\Mls;

	require_once( "/home/caddevac/Projects/converter/interfaces.php" );
	require_once( "/home/caddevac/Projects/converter/util/util.php" );
	require_once( "/home/caddevac/Projects/converter/exception/exceptions.php" );
	require_once( 'abstractMlsData.php' );
	
	use Converter\Exceptions\NoCadIdException          as NoCadIdException;
	use Converter\Exceptions\NoAllTableException		   as NoAllTableException;
	use Converter\Exceptions\NoTableException		       as NoTableException;
	use Converter\Translate\translateProcess           as translateProcess;
	use Converter\Interfaces\iProcess                  as iProcess;
	use Converter\System\Datasource\datasourceFactory  as datasourceFactory;
	use Converter\Interfaces\iTranslate                as iContained;
	use Converter\Interfaces\iMls                      as iMls;
	use Converter\Mls\abstractMlsData                  as abstractMlsData;



	/**
	 * mls's have property types, and property types have listings.
	 * The translateProcess (generic) corresponds with the property type level.
	 */
	
	class mls extends abstractMlsData implements iMls
	{
	}

?>
