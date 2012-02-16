<?

/*
* CIClientSiteCompany - инициируется с дочернего сайта
* CIClientSiteCompanyFromController - инициируется с контроллера
*/

class CIAllClientSiteCompany
{
	/**
    * Добавление компании с дочернего сайта на домен и, в случае успеха, на текущий дочерний
    * 
    * @param mixed $arFields
    */
	function Add($arFields)
	{
		/* на локальной версии компанию не добавляем до тех пор, 
		   пока не придет подтверждение от контроллера,
		   что на домене компания успешно добавлена */
		$ControllerClient = new CControllerClient;
		$ClientRequest = new CIControllerClientRequestTo;

		// определяем операцию, которую будем выполнять.
		// В данном случае - добавление компании
		$ClientRequest->operation = 'CIControllerCompany::Add';
		
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
    * Обновление компании на текущем дочернем сайте.
    * 
    * @param mixed $ID
    * @param mixed $arFields
    */
	function Update($arFields)
	{
		/* на локальной версии компанию не обновляем до тех пор, 
		   пока не придет подтверждение от контроллера,
		   что на домене компания успешно обновлена */
		$ControllerClient = new CControllerClient;
		$ClientRequest = new CIControllerClientRequestTo;
		
		// определяем операцию, которую будем выполнять.
		$ClientRequest->operation = 'CIControllerCompany::Update';
		
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
	
	
/*	
	/**
    * Удаление компании на текущем дочернем сайте.
    * 
    * @param mixed $ID
    *\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\/
	function Delete($ID)
	{
		$ControllerClient = new CControllerClient;
		$ClientRequest = new CIControllerClientRequestTo;
		
		$ClientRequest->operation = 'CIControllerCompany::Delete';
		
		// Задаем параметры, которые отправляем на обработку.
		$ClientRequest->arParameters = $ID;
		
		$ControllerResponse = $ClientRequest->Send("/bitrix/admin/icontroller_ws.php");

		$arResult = $ControllerResponse->arParameters;
		$arResult['STATUS'] = $ControllerResponse->status;
		
		if ($ControllerResponse->status == '200 OK')
		{
			//ok
		}
		else
		{
			//error
		}
		return $arResult;
	}
*/	
	
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
		{
			return $arResult;
		}
		else
		{
			return false;
		}
		
	}
	
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
		$CacheFolder = '/CIController/Company/GetIblockId';
		$CacheTime = 7*24*60*60;
		
		$IblId = 0;
		if($obCache->InitCache($CacheTime, $CacheID, $CacheFolder)) 
		{
			$IblId = $obCache->GetVars();
		}
		elseif($obCache->StartDataCache($CacheTime, $CacheID, $CacheFolder))
		{
			$rs = CIBlock::GetList(array('ID'=>'ASC'),array('TYPE'=>'company', 'CODE'=>'company', 'ACTIVE'=>'Y'));
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
}





/*
* операции с контроллера
*/

class CIAllClientSiteCompanyFromController
{
	function Add($arParams)
	{
		$XML_ID = trim($arParams['XML_ID']);
		$arFields = $arParams['arFields'];
		
		if(strlen($XML_ID)<=0)
			return array('SUCCESS'=>false, 'ERROR_CODE'=>1001, 'MESSAGE'=>'Wrong XML_ID', 'XML_ID'=>false);
		
		$el = new CIBlockElement;
		
		$resEl = CIBlockElement::GetList(Array(), Array("XML_ID"=>$XML_ID), false, Array("nPageSize"=>1), Array("ID", "IBLOCK_ID", "XML_ID"));
		if($arEl = $resEl->GetNext())
		{
			$GLOBALS['IS_SYSTEM_UPDATE'] = 'Y'; // чтобы не отрабатывал обработчик события
			$res = $el->Update($arEl['ID'], $arFields);
			if(intval($res) > 0)
			{
				$GLOBALS['IS_SYSTEM_UPDATE'] = 'N';
				return array('SUCCESS'=>true, 'ERROR_CODE'=>0, 'MESSAGE'=>'Element update ok', 'arFields'=>$arFields, 'NewId'=>$arEl['ID']);
			}
			else
			{
				$GLOBALS['IS_SYSTEM_UPDATE'] = 'N';
				return array('SUCCESS'=>false, 'ERROR_CODE'=>1002, 'MESSAGE'=>'Upd Error: '.strip_tags($el->LAST_ERROR), 'XML_ID'=>false);
			}
		}
		else
		{
			$arFields['IBLOCK_ID'] = IBLOCK_ID_company;
			$GLOBALS['IS_SYSTEM_ADD'] = 'Y'; // чтобы не отрабатывал обработчик события
			$PRODUCT_ID = $el->Add($arFields);
			if(intval($PRODUCT_ID) > 0)
			{
				$GLOBALS['IS_SYSTEM_ADD'] = 'N';
				return array('SUCCESS'=>true, 'ERROR_CODE'=>0, 'MESSAGE'=>'Element add ok', 'arFields'=>$arFields, 'NewId' => $PRODUCT_ID);
			}
			else
			{
				$GLOBALS['IS_SYSTEM_ADD'] = 'N';
				return array('SUCCESS'=>false, 'ERROR_CODE'=>1003, 'MESSAGE'=>'Add Error: '.strip_tags($el->LAST_ERROR), 'XML_ID'=>false, 'GG'=>'по русски');
			}
			
		}
	}
	
	
	function Delete($arParams)
	{
		$XML_ID = trim($arParams['XML_ID']);
		
		if($XML_ID=='')
			return array('SUCCESS'=>false, 'ERROR_CODE'=>1001, 'MESSAGE'=>'Wrong XML_ID', 'XML_ID'=>false);
		
		$resEl = CIBlockElement::GetList(Array(), Array("XML_ID"=>$XML_ID), false, Array("nPageSize"=>1), Array("ID", "IBLOCK_ID", "XML_ID"));
		if($arEl = $resEl->GetNext())
		{
			global $IS_CONTROLLER_DELETE;
			$IS_CONTROLLER_DELETE = 'Y';
			if(CIBlockElement::Delete($arEl['ID']))
			{
				return array('SUCCESS'=>true, 'ERROR_CODE'=>0, 'MESSAGE'=>'Element delete ok', 'XML_ID'=>$XML_ID);
			}
			else
			{
				return array('SUCCESS'=>false, 'ERROR_CODE'=>1003, 'MESSAGE'=>'Error Delete', 'XML_ID'=>false);
			}
		}
	}
	
}
?>