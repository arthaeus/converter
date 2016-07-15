<?php

  namespace Converter\System\Datasource;

  require_once( "/home/caddevac/Projects/converter/interfaces.php" );
  require_once( "/home/caddevac/Projects/converter/exception/exceptions.php" );
  require_once( '/home/caddevac/Projects/converter/system/translate/translateProcess.php' );

  use Converter\Exceptions\NoCadIdException as NoCadIdException;
  use Converter\Translate\translateProcess  as translateProcess;
	
	use Converter\Interfaces\iProcess         as iProcess;
  use Converter\Interfaces\iTranslate       as iContained;
  use Converter\Interfaces\iMls             as iMls;
  use Converter\Interfaces\iFactory         as iFactory;

  use Converter\Mls\abstractMls             as abstractMls;
  use Converter\Util\util                   as util;

	class datasourceFactory implements iFactory
	{
		public static function build()
		{
			$translate = new translateProcess();
		}
	}

?>
