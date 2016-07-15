<?php

	namespace Converter\System\Datasource\Sql;

	require_once( "/home/caddevac/Projects/converter/interfaces.php" );
	require_once( "/home/caddevac/Projects/converter/system/datasource/abstractModel.php" );
	require_once( "/home/caddevac/Projects/converter/util/util.php" );
	
	use Converter\System\Datasource\Model\abstractModel            		 as abstractModel;
	use Converter\Interfaces\iMls                                      as iMls;
	use Converter\Interfaces\iSql                                      as iSql;
	use Converter\Interfaces\iDatasourceModel                          as iDatasourceModel;
	use Converter\System\Datasource\abstractDatasourceBase 						 as abstractDatasourceBase;
	use Converter\Interfaces\iContained                                as iContained;
	use Converter\Util\util                                            as util;

	class globModel extends abstractModel implements iDatasourceModel
	{

		public function getFieldDefinitions()
		{

			/**
			 * remember, each dataSubset is an iDatasource (or a property type)
			 * looks something like
			 *
					[0] => Converter\System\Datasource\propData Object
							(
									[propertyTypeInfo:protected] => stdClass Object
											(
													[v1Pt] => sfr
													[v2Pt] => 1
													[activeTable] => 20121204-1
													[allTable] => 20121204-all
											)
									[data:protected] => 
									[iTranslate:protected] => 
			 */
			$dataSubsets = $this->getIDatasource()
				->getDataSubsets();


			/**
			 * looping through each subset (sfr,sfr2,sfr3,etc)
			 *
			 * at this point all of the subsets (iDatasources also known as property types) will call
			 * their getFieldDefinitions method.  since this is a globbed property type, i want to make sure
			 * that interacting with it is done in the same manner as interacting with a regular prop type
			 */
			foreach( $dataSubsets as $index => $dataSubset )
			{
				$subsetFieldDefs = $dataSubset->getIDatasourceModel()
					->getFieldDefinitions();

				/**
				 * we are looping through the data subsets of this property type (maybe this is sfr. so we are looping
				 * through sfr,sfr2,sfr3,etc)
				 */
        $count = 0;

        $fieldDefs = null;
        while( $fd = mysql_fetch_object( $subsetFieldDefs ) )
        {   
          $fieldDefs->{$fd->name} = $fd;
          $count++;

					/**
					 * get the v1 property type name just for display purposes
					 */
					$propertyType = $dataSubset->getPropertyTypeInfo()
						->v1Pt;

        }   

				/**
				 * this may be wrong.  I don't think the model should be responsible for calling the setter.  seems wrong.  like the 
				 * iDatasource should be doing this
				 */
        $dataSubset->setFieldDefinitions( $fieldDefs );

			}

		}

		public function getData( $propertyTypeInfo )
		{

			/**
			 * get the iDatasources from this glob, and for each iDatasource
			 * populate the data
			 */
			abstractModel::getData( $propertyTypeInfo );
		}
	}

?>
