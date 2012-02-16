<?
class CIClientTools
{
	/**
    * Возвращает значения свойств элемента в нормальном виде, по символьным кодам, вместо того что в стандартном $arFields
    */
	function GetNormalProps($IBLOCK_ID, $arPropsVal)
	{
		$IBLOCK_ID = intval($IBLOCK_ID);
		if($IBLOCK_ID<=0 || !CModule::IncludeModule("iblock")) return $arPropsVal;
		
		$arProps = array();
		$properties = CIBlockProperty::GetList(Array(), Array("ACTIVE"=>"Y", "IBLOCK_ID"=>$IBLOCK_ID));
		while($arProp = $properties->GetNext())
		{
			$arProps[$arProp["ID"]] = $arProp;
		}

		$arResProps = array();
		foreach($arPropsVal as $propID=>$arPropVal)
		{
			if (isset($arProps[$propID]))
			{
				if($arProps[$propID]['MULTIPLE']=='Y')
				{
					$thisPropVal = array();
					foreach($arPropVal as $propVal)
					{
						if($propVal['VALUE'])
							$thisPropVal[] = $propVal['VALUE'];
					}
				}
				else
				{
					$propVal = array_shift($arPropVal);
					$thisPropVal = $propVal['VALUE'];
				}
				
				$arResProps[$arProps[$propID]['CODE']] = $thisPropVal;
			}
			else
				$arResProps[$propID] = $arPropVal;
		}
		
		return $arResProps;
	}
}
?>