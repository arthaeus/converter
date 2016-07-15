<?php

	namespace Converter\Cache;

	require_once( '/home/caddevac/Projects/converter/interfaces.php' );
	require_once( '/home/caddevac/Projects/converter/base.php' );

	use Converter\Interfaces\iCache             as iCache;
	use Converter\base                          as base;
	use \Memcache                               as Memcache;

	class cadMemcache extends Memcache implements iCache
	{

		protected $host = null;
		protected $port = null;

		public function __construct()
		{
		}

		public function setupCache()
		{

			//$globalConfig = base::getIConfig->getSetting('global');
			$globalConfig = base::getIConfig()->getSetting( 'global' );
			
			$this->host = $globalConfig->cache
				->host
				->value;

			$this->port = $globalConfig->cache
				->host
				->port
				->value;

			$this->addServer( $this->host , $this->port );
			$this->connect( $this->host , $this->port );
		}

		public function getHost() { return $this->host; }
		public function setHost( $h ) { $this->host = $h; }

		public function getPort() { return $this->port; }
		public function setPort( $p ) { $this->port = $p; }
		
		public function setData( $key, $data , $expiration = null )
		{
			return $this->set( $key , $data , null , $expiration );
		}

		public function deleteData( $key)
		{
		}

		public function getData( $key)
		{
			return $this->get( $key );
		}
	}
?>
