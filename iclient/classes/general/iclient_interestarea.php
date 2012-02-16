<?

/*
* CIAllClientSiteInterestArea - инициируется с дочернего сайта
* CIAllClientSiteInterestAreaFromController - инициируется с контроллера
*/

class CIAllClientSiteInterestArea
{
	/**
    * Добавление области интересов с дочернего сайта на домен и, в случае успеха, на текущий дочерний
    * 
    * @param mixed $arFields
    */
	function AddUpdate($arFields)
	{
		$ControllerClient = new CControllerClient;
		$ClientRequest = new CIControllerClientRequestTo;

		// определяем операцию, которую будем выполнять.
		// В данном случае - добавление компании
		$ClientRequest->operation = 'CIControllerAreaInt::Add';
		
		// Задаем параметры, которые отправляем на обработку.
		$ClientRequest->arParameters = $arFields;
		
		// этот скрипт будет обрабатывать данные, приходящие с сайта-клиента на контроллер.
		$ControllerResponse = $ClientRequest->Send("/bitrix/admin/icontroller_ws.php");

		$arResult = $ControllerResponse->arParameters;
		
		$arResult['STATUS'] = $ControllerResponse->status;
		
		if ($ControllerResponse->status == '200 OK')
		{
			$arResult['XML_ID'] = trim($arResult['XML_ID']);
		}
		else
		{
			//error
		}
		return $arResult;
	}
	
	

	/**
    * Получение данных области интересов
    * 
    * @param ште $ID
    */
	function GetInfo($ID)
	{
		$ControllerClient = new CControllerClient;
		$ClientRequest = new CIControllerClientRequestTo;

		// определяем операцию, которую будем выполнять.
		// В данном случае - добавление компании
		$ClientRequest->operation = 'CIControllerAreaInt::GetInfo';
		
		// Задаем параметры, которые отправляем на обработку.
		$ClientRequest->arParameters = Array('XML_ID' => $ID);
		
		// этот скрипт будет обрабатывать данные, приходящие с сайта-клиента на контроллер.
		$ControllerResponse = $ClientRequest->Send("/bitrix/admin/icontroller_ws.php");
		$arResult = $ControllerResponse->arParameters;
		
		$arResult['STATUS'] = $ControllerResponse->status;
		
		if ($ControllerResponse->status == '200 OK')
			return $arResult;
		else
			return false;
	}
	
	

	/**
    * Удаление области интересов на текущем дочернем сайте.
    * 
    * @param mixed $ID
    */
	function Delete($ID)
	{
		$ControllerClient = new CControllerClient;
		$ClientRequest = new CIControllerClientRequestTo;
		
		$ClientRequest->operation = 'CIControllerAreaInt::Delete';
		
		// Задаем параметры, которые отправляем на обработку.
		$ClientRequest->arParameters = $ID;
		
		$ControllerResponse = $ClientRequest->Send("/bitrix/admin/icontroller_ws.php");

//		$arResult = $ControllerResponse->arParameters;
//		$arResult['STATUS'] = $ControllerResponse->status;

		return $ControllerResponse;
	}

/*	
	function GetInfo($XML_ID)
	{
		$ControllerClient = new CControllerClient;
		$ClientRequest = new CIControllerClientRequestTo;

		$ClientRequest->operation = 'CIControllerCompany::GetInfo';
		
		$ClientRequest->arParameters = array('XML_ID'=>$XML_ID);
		
		$ControllerResponse = $ClientRequest->Send("/bitrix/admin/icontroller_ws.php");
		
		$arResult = $ControllerResponse->arParameters;
		
		$arResult['STATUS'] = $ControllerResponse->status;
		
		if ($ControllerResponse->status == '200 OK')
			return $arResult;
		else
			return false;
	}
*/
	
	/**
	* Получим ID инфоблока компаний
	* 
	*/
	function GetIblockId()
	{
		static $IblId;
		if($IblId > 0)
			return $IblId;

        $ibo = intval($ibo);
        if($ibo > 0)
        {
            $IblId = $ibo; 
            return $IblId;
        }

		$obCache = new CPHPCache;
		$CacheID = 1;
		$CacheFolder = '/CIController/InterestArea/GetIblockId';
		$CacheTime = 7*24*60*60;
		
		$IblId = 0;
		if($obCache->InitCache($CacheTime, $CacheID, $CacheFolder)) 
		{
			$IblId = $obCache->GetVars();
		}
		elseif($obCache->StartDataCache($CacheTime, $CacheID, $CacheFolder))
		{
			$rs = CIBlock::GetList(array('ID'=>'ASC'),array('TYPE'=>'siteinfo', 'CODE'=>'areaintr', 'ACTIVE'=>'Y'));
			if($arIbl = $rs->Fetch())
            {
				$IblId = intval($arIbl['ID']);
                $obCache->EndDataCache($IblId);  
            }
            else
            {
                $IblId = -1;
                $obCache->AbortDataCache();  
            }

		}
		return $IblId;
	}
	
	
	/**
	*	Проверяем, существует ли родительская секция, пришедшая с контроллера. Если не существует - рекурсивно воссоздаем структуру.
	*
	*	@param int $SECT_ID
	*/
	function CheckSection($SECT_ID)
	{
		$TargetID = intval($SECT_ID);
		if ($TargetID > 0)
		{
			CModule::IncludeModule('iblock');
			$SectionList = CIBlockSection::GetList(Array(), Array('XML_ID' => $TargetID));
			if ($Section = $SectionList->Fetch())
			{
				return true;
			}
			else
			{
				$SectionInfo = self::GetInfo($TargetID);
				if ($SectionInfo)
				{
					$Fields = $SectionInfo['arFields'];
					$InterestAreaIblockID = self::GetIblockId();
					$Fields['IBLOCK_ID'] = $InterestAreaIblockID;
					if (intval($Fields['IBLOCK_SECTION_ID']) > 0)
					{
						self::CheckSection($Fields['IBLOCK_SECTION_ID']);
						$ParentList = CIBlockSection::GetList(Array(), Array('IBLOCK_ID' => $InterestAreaIblockID, 'XML_ID' => $Fields['IBLOCK_SECTION_ID']));
						if ($Parent = $ParentList->Fetch())
							$Fields['IBLOCK_SECTION_ID'] = $Parent['ID'];
						else
							$Fields['IBLOCK_SECTION_ID'] = NULL;
					}
					$SectC = new CIBlockSection;
					$GLOBALS['IS_SYSTEM_SECTION_ADD'] = 'Y';
					if ($SectC->Add($Fields))
						return true;
					else
					{
						return false;
					//	return $SectC->LAST_ERROR;
					}
				}
			}
		}
	}
}



/*
* операции с контроллера
*/

class CIAllClientSiteInterestAreaFromController
{
	function Add($arParams)
	{
		$XML_ID = trim($arParams['XML_ID']);
		$arFields = $arParams['arFields'];
		
		if($XML_ID=='')
			return array('SUCCESS'=>false, 'ERROR_CODE'=>1001, 'MESSAGE'=>'Wrong XML_ID', 'XML_ID'=>false);
			
		CIClientSiteInterestArea::CheckSection($arFields['IBLOCK_SECTION_ID']);
		
		$sect = new CIBlockSection;
		
		$InterestAreaIblockID = CIClientSiteInterestArea::GetIblockId();
		if (intval($arFields['IBLOCK_SECTION_ID']) > 0)
		{
			$ParentSectionList = CIBlockSection::GetList(Array(), Array('IBLOCK_ID' => $InterestAreaIblockID, 'XML_ID' => $arFields['IBLOCK_SECTION_ID']));
			if ($ParentSection = $ParentSectionList->Fetch())
				$arFields['IBLOCK_SECTION_ID'] = $ParentSection['ID'];
			else
				$arFields['IBLOCK_SECTION_ID'] = NULL;
		}
		
		$resSect = CIBlockSection::GetList(Array(), Array("XML_ID"=>$XML_ID), false, Array("nPageSize"=>1), Array("ID", "IBLOCK_ID", "XML_ID"));
		if($arSect = $resSect->GetNext())
		{
			$GLOBALS['IS_SYSTEM_SECTION_UPDATE'] = 'Y'; // чтобы не отрабатывал обработчик события
			if($res = $sect->Update($arSect['ID'], $arFields))
			{
				return array('SUCCESS'=>true, 'ERROR_CODE'=>0, 'MESSAGE'=>'Element update ok', 'arFields'=>$arFields);
			}
			else
			{
				return array('SUCCESS'=>false, 'ERROR_CODE'=>1002, 'MESSAGE'=>'Upd Error: '.strip_tags($sect->LAST_ERROR), 'XML_ID'=>false);
			}
		}
		else
		{
			$arFields['IBLOCK_ID'] = $InterestAreaIblockID;
			$GLOBALS['IS_SYSTEM_SECTION_ADD'] = 'Y'; // чтобы не отрабатывал обработчик события
			if($PRODUCT_ID = $sect->Add($arFields))
			{
				return array('SUCCESS'=>true, 'ERROR_CODE'=>0, 'MESSAGE'=>'Element add ok', 'arFields'=>$arFields);
			}
			else
			{
				return array('SUCCESS'=>false, 'ERROR_CODE'=>1003, 'MESSAGE'=>'Add Error: '.strip_tags($sect->LAST_ERROR), 'XML_ID'=>false);
			}
		}
	}
	
	
	function Delete($arParams)
	{
		$XML_ID = trim($arParams['XML_ID']);
		
		if($XML_ID=='')
			return array('SUCCESS'=>false, 'ERROR_CODE'=>1004, 'MESSAGE'=>'Wrong XML_ID', 'XML_ID'=>false);
		
		$resSect = CIBlockSection::GetList(Array(), Array("XML_ID"=>$XML_ID), false, Array("nPageSize"=>1), Array("ID", "IBLOCK_ID", "XML_ID"));
		if($arSect = $resSect->GetNext())
		{
			global $IS_CONTROLLER_DELETE;
			$IS_CONTROLLER_DELETE = 'Y';
			if(CIBlockSection::Delete($arSect['ID']))
			{
				return array('SUCCESS'=>true, 'ERROR_CODE'=>0, 'MESSAGE'=>'Element delete ok', 'XML_ID'=>$XML_ID);
			}
			else
			{
				return array('SUCCESS'=>false, 'ERROR_CODE'=>1005, 'MESSAGE'=>'Error Delete', 'XML_ID'=>false);
			}
		}
	}
}
?>