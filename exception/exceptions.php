<?php

	namespace Converter\Exceptions;

	class NoFieldMigrationException extends \Exception
	{
		public function logException( $iLogger)
		{
			$iLogger->logError( $this->getMessage() );
		}

		public function handleException()
		{
			echo $this->getMessage();
			exit;
		}

		public function errorMessage()
		{
		}
	}

	class NoCadIdException extends \Exception

	{
		public function errorMessage()
		{
			$msg = "You must provide an cadID at file " . $this->getLine() . " " . $this->getFile();
			return $msg;
		}
	}

	class NoAllTableException extends \Exception
	{
		public function errorMessage()
		{
			$msg = "There is no all table for this MLS @todo, more description";
			return $msg;
		}
	}

	class NoTableException extends \Exception
	{
		public function errorMessage()
		{
			$msg = "There is a missing table for this MLS @todo, more description";
			return $msg;
		}
	}

	/**
	 * maybe the exception handler returns a default object of that type
	 */
	class NoCustomClassException extends \Exception
	{
		public function errorMessage()
		{
			$msg = "";
			return $msg;
		}
	}

	class MisconfigException extends \Exception
	{
		public function errorMessage()
		{
			$msg = "";
			return $msg;
		}
	}
	
	class V1PropTypeException extends \Exception
	{
		public function __construct()
		{
			return;
		}

		public function errorMessage()
		{
			$msg = "";
			return $msg;
		}
	}

	
	class V2PropTypeException extends \Exception
	{
		public function errorMessage()
		{
			$msg = "";
			return $msg;
		}
	}

	
	class V2ActiveTableException extends \Exception
	{
		public function errorMessage()
		{
			$msg = "";
			return $msg;
		}
	}
	
	class V2AllTableException extends \Exception
	{
		public function errorMessage()
		{
			$msg = "";
			return $msg;
		}
	}



?>
