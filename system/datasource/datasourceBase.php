<?php


	/**
	 * three pieces (potentially more) of the converter system share the datasource base.  Anything that
	 * is contained directly or indirectly by the datasource extends this base.  this provides a way for 
	 * the pieces of the subsystem to communicate with each other
	 */

	namespace Converter\System\Datasource;

	require_once( "/home/caddevac/Projects/converter/interfaces.php" );
	require_once( "/home/caddevac/Projects/converter/base.php" );
  require_once( "/home/caddevac/Projects/converter/system/datasource/datasourceBase.php" );	
	
	use Converter\Interfaces\iContained  as iContained;
	use Converter\base                   as base;
  use Converter\Interfaces\iSql                                      as iSql;
	use Converter\System\Datasource\abstractDatasourceBase             as abstractDatasourceBase;


	/**
	 * mls's have property types, and property types have listings.
	 * The dataSource corresponds with the property type level.
	 *
	 * extend base to give subclasses access to the DI container
	 */
	abstract class abstractDatasourceBase extends base implements iContained
	{

		/**
		 * after instantiation, state is null
		 */
		const STATE_NULL      = "STATE_NULL";

		/**
		 * after the propertyTypeInfo has been set
		 */
		const STATE_PROPTYPEINFO = "STATE_PROPERTYINFO";

		/**
		 * after field defs is populated
		 */
		const STATE_FIELDDEFS = "STATE_FIELDDEFS";

		/**
		 * after data is pupulated
		 */
		const STATE_POPULATED = "STATE_POPULATED";

		/**
		 * datasource is ready to be converted
		 */
		const STATE_READY     = "STATE_READY";

		/**
		 * datasource is ready to be converted
		 */
		const STATE_OUTPUT_READY = "STATE_OUTPUT_READY";

    /** 
     * right now i think that the actual sql for the datasource
     * should be abstracted.  a lot of potential for change in the query
     */
    protected $iDatasourceModel  = null;

		/**
		 * this will be the outputter for the datasource.  right now, it makes sense to have
		 * the outputter be on the datasource level.
		 */
		protected $iOutput = null;


    /**
     * The actual rows of data for this datasource
     */
    protected $data              = null;

		/**
		 * this container variable has nothing to do with DI.  It refers to
		 * things discussed in the iContained interface
		 */
		protected $container = null;



		public function __construct()
		{
			return;
		}

    public function setIDatasourceModel(iSql $s) {  $this->iDatasourceModel = $s; }
    public function getIDatasourceModel()        { return $this->iDatasourceModel; }


    public function setData($d)
    {
      $this->data = $d;
      $this->state = self::STATE_POPULATED;
      return;
    }
    public function getData()   { return $this->data; }

		public function setIOutput($o)
		{
			$this->iOutput = $o;
			return;
		}

		public function getIOutput()
		{
			return $this->iOutput;
		}

		public function setContainer(&$c)
		{
			$this->container = &$c;
		}

		public function getContainer()
		{
			return $this->container;
		}

	}

?>
