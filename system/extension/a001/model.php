<?

	namespace Converter\System\Extension;
	require_once( '/home/caddevac/Projects/converter/system/datasource/abstractModel.php' );
	require_once( '/home/caddevac/Projects/converter/interfaces.php' );

	use Converter\System\Datasource\Model\abstractModel as abstractModel;
	use Converter\Interfaces\iDatasourceModel as iDatasourceModel;

	class modelFOO extends abstractModel implements iDatasourceModel
	{
		public function getData()
		{
			echo "foobar";
			return;
		}
	}

?>
