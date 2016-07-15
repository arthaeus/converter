<?php

	namespace Converter\Config;

	use \stdClass as stdClass;
	abstract class Configuration
	{

		/** 
		 * start off in the config ready state
		 */
		const CONFIGREADY  = 0;

		/** 
		 * the configToObject has run, and the config is an object.
		 */
		const CONFIGOBJECT = 1;

		/**
		 *  I always want to deal with objects, and not arrays.  make the configuration
		 *  array into an object
		 */
		protected abstract function configToObject( &$settings );

	}
	/**
	 * this file creates raw config objects.  I am going to take these raw objects and turn them into
	 * blueprints so that we can pass in a common interface to the factories.  the factories will only
	 * ever have to call the function exposed in the interface regardless of the object type.  Look at
	 * the iBlueprint to see what the factory will expect.
	 */
	
	class config extends Configuration
	{

		protected $settings = null;

		/**
		 * start in configready state
		 */
		protected $state = "CONFIGREADY";

		
		/**
		 * the settings should not be settable from the outside.  only providing a getter
		 * for settings.  this function is different that getSetting() in that this function
		 * returns all settings.  getSetting() returns a specific setting
		 */
		public function getSettings() { return $this->settings; }

		public function getSetting( $name )
		{
			if( !( $this->state >= self::CONFIGOBJECT ) )
			{
				throw new Exception( "state must be at least configobject" );
			}	
			return $this->settings->$name;
		}


		/**
		 * i prefer to work with objects, so convert the array that ini_parse returns to an
		 * object.  also, this function allows me to use dots in the .ini file for clarity
		 */
		protected function configToObject( &$settings )
		{

			$return = new stdClass;

			foreach( $settings as $key => $value )
			{

				$configForFactory = new stdClass;
				foreach( $value as $attribute => $attributeValue )
				{
					$attribute = explode( '.' , $attribute );


					$c = $configForFactory;
					foreach( $attribute as $a )
					{
						if( !isset( $c->$a ) )
						{
							$c->$a = new stdClass ;
						}
						$c = $c->$a;
					}
					unset( $c->$a );
					$c->value = $attributeValue;
					$return->$key = $configForFactory;

				}
			}
			$this->state = self::CONFIGOBJECT;
			return $return;
		}


		public function buildConfig()
		{
      $settings = (object)parse_ini_file( "api.ini" , true );
      $this->settings = $this->configToObject( $settings );
		}

	}
?>
