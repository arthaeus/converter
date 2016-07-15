<?php

interface iConfig
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
	 * this function should make sure that the config has  been loaded from
	 * the app ini file, and the implementing object correctly represents
	 * how it is described in the app ini file.  iConfigs are ultimately 
	 * responsible for the settings that are passed into the factory methods,
	 * thus they are responsible for the objects being built correctly. 
	 */
	public function buildConfig();

}
	
?>
