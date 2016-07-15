<?php

	namespace Converter\Interfaces;


	/**
	 * each iDatasource will have a (potential) collection of callbacks
	 */
	interface iCallback
	{
		public function perform( &$listing );
	}

	interface iCache
	{
		public function setData($key,$data,$expiration=null);
		public function getData($key);
		public function deleteData($key);
		

		/**
		 * do all of the things that need to be done to set up this cache
		 */
		public function setupCache();
	}

	/**
	 * the sql interface for mls related sql
	 *
	 * an iMlsModel extends iSql, so it will already have access to the datasource
	 */
	interface iDatasourceModel extends iSql
	{

		/**
		 * why is getFieldDefinitions on the iDatasourceModel, and not on the iDatasource or the iMls
		 */
		public function getFieldDefinitions();
		public function getData($propertyTypeInfo);
	}

	/**
	 * i need an interface .. maybe it should be called iSql.  i want to make the system consider building the query for
	 * grabbing the data, to be an entire process.  It seems safer to create the sql via some sort of process, instead of just
	 * putting a query in the middle of the page
	 *
	 * the sql thing is confusing.  sql morphed into model.  this is something to look at later
	 */
	interface iSql
	{
		/**
		 * build, store, and return the sql query
		 */
		public function getIDatasource();
		public function setIDatasource(iDatasource $d);
	}

	interface iMls
	{
		public function getPropertyTypeInfo();
	}

	/**
	 * each process is a portion of a job.
	 * In the end, (in the context of the v1->v2 translator) each iProcess will be an abstraction of a single property type being converted
	 * I'm doing this because I want to use multiple processes to convert the data from v2 to v1.
	 * I don't want to tie the concept of an iProcess solely to a property type processing.
	 *
	 *  - something needs to go to the datasource and get the listing data.  probably should not get it 
	 * all at once.  this object will be responsible for keeping data in a queue so that child threads
	 * can translate the data.
	 *  - on second thought I will get all of the data at once.  cuts down on complexity, and minimizes calls to the database.  The datagrabber
	 *  should have an interface that allows for forks to request data to process.
	 *
	 *
	 * data grabber will be the observer, and the iForkTranslators(?) will be the subject
	 * each iForkTranslator will update the data grabber.
	 *
	 * the datagrabber should array_shift to the forkTranslators.  the forks translate, and then dump
	 */
	interface iProcess
	{
		public function perform();
	}

	/**
	 * an iDataSource is the interface between the actual datasource (database, flat file, etc) and objects that will consume the data
	 * in the context of the v2->v1 translator, i think it makes sense that all iTranslators have an iDataSource. if there is no data source,
	 * what is being converted?
	 *
	 * maybe instead, the datasource is the outer object.  meaning the datasource contains the iTranslate instead of vice verca.  It sounds more 
	 * intuitive to access mls->iDataSource than mls->iTranslate->iDataSource
	 */
	interface iDatasource
	{
		/**
		 * an iDatasource is contained by a mls.  the datasource base is implementing iContained indirectly (extending a base class that implements iContained)
		 * so to make the code more descriptive, maybe I should wrap the getContainer method in the getMls method.
		 */
		public function getIMls();
		public function setIMls(iMls &$m);
		public function populate();
		public function perform();

		/**
		 * I am putting this function here because datasources can be a single datasource (sfr), or it can be an agregate datasource.  regardless, I want to be
		 * able to interact with all datasources the same way, and all iDatasources have a model, so they should be forced to expose a function for this
		 * this will tie into the way agregate vs single datasources do other things as well.
		 */
		public function getIDatasourceModel();
	}

	/**
	 * thinking about making the propData and propDataGlob classes implement this interface as well.  not all iDatasources are going to have the getFieldDefinitions function
	 * (images,agent,office,open,virtual).  and now that I think about it, iDatasources are more than just property types.  I need a way to designate an iDatasource as a property data
	 * or [image,agent,office,open,virtual]
	 */
	interface iPropertyData extends iDatasource
	{
		public function getFieldDefinitions();
	}



	/**
	 * make sure that we have a method of communication between container objects and the objects that they contain. when a containing class constructs one 
	 * of it's properties
	 * 
	 * if( the class that I am constructing implements iContained)
	 * {
	 *   give that object a reference to me (the container)
	 * }
	 */
	interface iContained
	{
		/**
		 * if you are an object, and you are contained by another object, the containing object can call your setContainer method, and pass a reference to itself
		 */
		public function setContainer(&$c);
		public function getContainer();
	}
	
	/**
	 * each datasource will have an iOutput responsible for outputting data in whatever format
	 * slince it is on the datasource, it should implement iContained
	 *
	 * todo
	 * think about this interface in the context of glob datatypes.
	 */
	interface iOutput extends iContained
	{
		/**
		 * take an input, and turn it into an output
		 */
		public function output($content);
	}

	interface iFactory
	{
		public static function build();
	}
?>
