<?
class CIAllClientSiteUser
{
	/**
     * Список обрабатываемых полей
     * 
     * @param mixed $operation
     */
	function GetFields($operation = '')
	{
		$arAll = array(
            'ID' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false), 
            'ACTIVE' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false), 
            'LOGIN' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'GROUP_ID' => array('UFT'=>'FIELD', 'TYPE'=>'GR', 'MULT'=>true),
            'PASSWORD' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'EMAIL' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'LAST_NAME' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'NAME' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'SECOND_NAME' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'WORK_PROFILE' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'PERSONAL_PHOTO' => array('UFT'=>'FIELD', 'TYPE'=>'F', 'MULT'=>false),
            'WORK_POSITION' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'PERSONAL_STREET' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'PERSONAL_PHONE' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'PERSONAL_FAX' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'PERSONAL_WWW' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'PERSONAL_MAILBOX' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'PERSONAL_ZIP' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'PERSONAL_NOTES' => array('UFT'=>'FIELD', 'TYPE'=>'S', 'MULT'=>false),
            'UF_CONTACTS_PHONE' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_CONTACTS_EMAIL' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_CONTACTS_CELL' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_CONTACTS_FIO' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_AUTHORITY' => array('UFT'=>'UF', 'TYPE'=>'L', 'MULT'=>false),
            'UF_SIGNING_FIO' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_BIK' => array('UFT'=>'FIELD', 'UF'=>'S', 'MULT'=>false),
            'UF_C_ACCOUNT' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_BANKCITY' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_BANK' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_TALACCOUNT' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_KPP' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_INN' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_OGRN' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_BT_ENTINITY' => array('UFT'=>'UF', 'TYPE'=>'L', 'MULT'=>false),
            'UF_RAB' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_BOSS' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_CATEGORY' => array('UFT'=>'UF', 'TYPE'=>'L', 'MULT'=>false),
            'UF_SWWW' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_SFAX' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_SPHONE' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_SADDRESS' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_COMPANY' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_GRADE2' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_GRADE' => array('UFT'=>'UF', 'TYPE'=>'L', 'MULT'=>false),
            'UF_STATION2' => array('UFT'=>'UF', 'TYPE'=>'S', 'MULT'=>false),
            'UF_STATION' => array('UFT'=>'UF', 'TYPE'=>'L', 'MULT'=>false),
        );

		if($operation == 'ADD')
		{
			unset($arAll['ID']);
		}

		if($operation == 'UPD')
		{
			unset($arAll['ID']);
			unset($arAll['ACTIVE']);
		}

		return $arAll;
    }

	/**
    * Добавление пользователя с дочернего сайта на домен и, в случае успеха, на текущий дочерний
    * 
    * @param mixed $arFields
    */
	function Add($arFields)
	{
		global $USER;
		
		$IsMember = COption::GetOptionString("main", "controller_member", 'N');
		$MemberID = COption::GetOptionString("main", "controller_member_id", '');
		/* если мы не в домене, то запрещаем добавление пользователей */
		if ($IsMember != 'Y' || empty($MemberID))
		{
			$USER->LAST_ERROR = 'На сайте запрещено добавление пользователей, потому что сайт не входит в домен!';
			return false;
		}
		
		/* на локальной версии пользователя не добавляем до тех пор, 
		   пока не придет подтверждение от контроллера,
		   что на домене пользователь успешно добавлен */
		$ControllerClient = new CControllerClient;
		$ClientRequest = new CIControllerClientRequestTo;

		// определяем операцию, которую будем выполнять.
		// В данном случае - регистрация пользователя
		$ClientRequest->operation = 'CIControllerUser::Add';
		
		// Задаем параметры, которые отправляем на обработку. В данном случае - указали данные для регистрации.
		
		$file = $arFields['file'];
		unset($arFields['file']);
		$arUser = $arFields;
		$ClientRequest->arParameters = Array('arUser' => $arUser, 'file' => $file);
//		$ClientRequest->arParameters = Array('arUser' => $arFields);
		// этот скрипт будет обрабатывать данные, приходящие с сайта-клиента на контроллер.
		$ControllerResponse = $ClientRequest->Send("/bitrix/admin/icontroller_ws.php");
		if ($ControllerResponse->status == '200 OK')
		{
			if ($ControllerResponse->arParameters['SUCCESS'] === true)
				return $ControllerResponse->arParameters['ID'];
			else
			{
				$USER->LAST_ERROR = $ControllerResponse->arParameters['MESSAGE'];
				return false;
			}
		}
		else
		{
			$USER->LAST_ERROR = 'Ошибка взаимодействия с хранилищем данных! Повторите попытку! В случае повторного возникновения ошибки убедительная просьба сообщить об этой ошибке администратору сайта!';
			return false;
		}
	}
	
	/**
    * Обновление пользователя на текущем дочернем сайте, инициализация обновления пользователя на контроллере
    * Обновление родительского включено
    * 
    * @param mixed $ID
    * @param mixed $arFields
    */
	function Update($arFields)
	{
		global $USER;
		
		/* если мы не в домене, то запрещаем обновление данных пользователей */
		$IsMember = COption::GetOptionString("main", "controller_member", 'N');
		$MemberID = COption::GetOptionString("main", "controller_member_id", '');
		if ($IsMember != 'Y' || empty($MemberID))
		{
			$arAll = self::GetFields('UPD');

			foreach ($arAll as $ElKey=>$Element)
			{
				if (isset($arFields[$ElKey]))
				{
					$USER->LAST_ERROR = 'На сайте запрещено обновление пользователей, потому что сайт не входит в домен!';
					return false;
				}
			}
		}
		
		/* на локальной версии пользователя не обновляем до тех пор, 
		   пока не придет подтверждение от контроллера,
		   что на домене пользователь успешно обновлен */
		$ControllerClient = new CControllerClient;
		$ClientRequest = new CIControllerClientRequestTo;
		
		// определяем операцию, которую будем выполнять.
		// В данном случае - обновление пользователя
		$ClientRequest->operation = 'CIControllerUser::Update';
		
		// Задаем параметры, которые отправляем на обработку. В данном случае - указали обновляемые данные.
		$file = $arFields['file'];
//		$UserID = $arFields['XML_ID'];
		$UserDataList = CUser::GetByID($arFields['ID']);
		if ($UserData = $UserDataList->GetNext())
			$UserID = $UserData['XML_ID'];
		else
		{
			$USER->LAST_ERROR = 'Ошибка взаимодействия с хранилищем данных! Повторите попытку! В случае повторного возникновения ошибки убедительная просьба сообщить об этой ошибке администратору сайта!';
			return false;
		}
		unset($arFields['file']);
		
		$arUser = $arFields;
		$ClientRequest->arParameters = Array('arUser' => $arUser, 'ID' => $UserID, 'file' => $file);

		// этот скрипт будет обрабатывать данные, приходящие с сайта-клиента на контроллер.
		$ControllerResponse = $ClientRequest->Send("/bitrix/admin/icontroller_ws.php");
		
		if ($ControllerResponse->status == '200 OK')
		{
			if ($ControllerResponse->arParameters['SUCCESS'] === true)
				return $arFields;
			else
			{
				$USER->LAST_ERROR = $ControllerResponse->arParameters['MESSAGE'];
				return false;
			}
		}
		else
		{
			$USER->LAST_ERROR = 'Ошибка взаимодействия с хранилищем данных! Повторите попытку! В случае повторного возникновения ошибки убедительная просьба сообщить об этой ошибке администратору сайта!';
			return false;
		}
	}
	
	/**
	* "Отвязка" пользователя от контроллера при удалении на дочернем сайте
	* 
    * @param int $ID
	*/
	function Delete($ID)
	{
		/* если мы не в домене, то отменяем "отвязку" от контроллера */
		$IsMember = COption::GetOptionString("main", "controller_member", 'N');
		$MemberID = COption::GetOptionString("main", "controller_member_id", '');
		if ($IsMember != 'Y' || empty($MemberID))
			return true;
		else
		{
			$ControllerClient = new CControllerClient;
			$ClientRequest = new CIControllerClientRequestTo;
			$ClientRequest->arParameters = Array('ID' => $ID);
			$ClientRequest->operation = 'CIControllerUser::UnBindUserFromChildSite';
			$ControllerResponse = $ClientRequest->Send("/bitrix/admin/icontroller_ws.php");
		}
	}
	
	/**
    * Проверка авторизации пользователя на домене, возврат кук на дочерний сайт
    */
	function CheckAuth()
	{
		global $USER;
		
		/* если мы не в домене, то отменяем CheckAuth */
		$IsMember = COption::GetOptionString("main", "controller_member", 'N');
		$MemberID = COption::GetOptionString("main", "controller_member_id", '');
		if ($IsMember != 'Y' || empty($MemberID))
			return true;
		else
		{
			$ControllerClient = new CControllerClient;
			$ClientRequest = new CIControllerClientRequestTo;
			$ClientRequest->operation = 'CIControllerUser::CheckAuth';
			$ControllerResponse = $ClientRequest->Send("/bitrix/admin/icontroller_ws.php");

			if ($ControllerResponse->status == '200 OK')
			{
				if ($uid = intval($ControllerResponse->arParameters['USER_ID']))
				{
					$UserDataList = $USER->GetList(($by="id"), ($order="asc"), Array('XML_ID' => $uid));
					if ($UserData = $UserDataList->GetNext())
					{
						$USER->Authorize($UserData['ID']);
					}
					else
					{
						$arUserFields = self::GetInfo(Array('ID' => $uid));
						if (is_array($arUserFields) || count($arUserFields))
						{
							$NewUser = new CUser;
							if ($NewUserID = $NewUser->Add($arUserFields))
								$USER->Authorize($NewUserID);
							else
								$USER->Logout();
						}
						else
							$USER->Logout();
					}
				}
				else
					$USER->Logout();
			}
			else
			{
				
				$CurrentUserID = $USER->GetID();
				if ($CurrentUserID)
				{
					global $BEFORE_START_USER_ERROR;
					$BEFORE_START_USER_ERROR = 'Ошибка взаимодействия с хранилищем данных! Вы будете разавторизованы! В случае повторного возникновения ошибки убедительная просьба сообщить об этой ошибке администратору сайта!';
					$USER->Logout();
				}
			}
		}
	}
	
	/**
    * Авторизация пользователя на домене. В случае неудачи - отказ в авторизации на дочернем
    * 
    * @param mixed $arFields
    */
	function Authorize($arFields)
	{
		global $USER;
		
		$IsMember = COption::GetOptionString("main", "controller_member", 'N');
		$MemberID = COption::GetOptionString("main", "controller_member_id", '');
		if ($IsMember != 'Y' || empty($MemberID))
			return true;
		
		$ControllerClient = new CControllerClient;
		$ClientRequest = new CIControllerClientRequestTo;
		
		$ClientRequest->operation = 'CIControllerUser::Authorize';
		$ClientRequest->arParameters = $arFields;
		
		$ControllerResponse = $ClientRequest->Send("/bitrix/admin/icontroller_ws.php");
		if ($ControllerResponse->status == '200 OK')
		{
			if ($ControllerResponse->arParameters['SUCCESS'] === true)
			{
				$CheckedUserList = CUser::GetByLogin($arFields['LOGIN']);
				$u_id = intval($ControllerResponse->arParameters['USER_ID']);
				if ($CheckedUser = $CheckedUserList->GetNext())
				{
					if ($CheckedUser['XML_ID'] == $u_id)
						return true;
					else
					{
						$USER->Logout();
						$USER->LAST_ERROR = 'Обнаружено исключение, противоречащее логике работы с пользователями! Убедительная просьба сообщить об этой ошибке администратору сайта!';
						return false;
					}
				}
				else
				{
					$CheckedUserByIDList = CUser::GetList(($by="id"), ($order="asc"), Array('XML_ID' => $u_id));
					if ($CheckedUserByID = $CheckedUserByIDList->Fetch())
					{
						$USER->Logout();
						$USER->LAST_ERROR = 'Обнаружено исключение, противоречащее логике работы с пользователями! Убедительная просьба сообщить об этой ошибке администратору сайта!';
						return false;
					}
					else
					{
						global $APPLICATION;
						$GetUserInfo = self::GetInfo(Array('ID' => $u_id));
						
						global $CHECK_USER_GROUP;
						$CHECK_USER_GROUP = $GetUserInfo;
				/*		$APPLICATION->IncludeComponent(
							"individ:role.choice",
							"",
							array("arUser" => $GetUserInfo['arUser']),
							false
						);*/
					}
				}
			}
			else
			{
				$USER->Logout();
				$USER->LAST_ERROR = $ControllerResponse->arParameters['MESSAGE'];
				return false;
			}
		}
		else
		{
			$USER->Logout();
			$USER->LAST_ERROR = 'Ошибка взаимодействия с хранилищем данных! Повторите попытку! В случае повторного возникновения ошибки убедительная просьба сообщить об этой ошибке администратору сайта!';
			return false;
		}
	}
	
	/**
    * Разавторизация пользователя на текущем сайте, на контроллере и на остальных дочерних сайтах
    * 
    * @param mixed $arParams
    */
	function Logout($arParams)
	{
		global $USER;
		
		$ControllerClient = new CControllerClient;
		$ClientRequest = new CIControllerClientRequestTo;
		
		$ClientRequest->operation = 'CIControllerUser::Logout';
		$UserDataList = CUser::GetByID($arParams['USER_ID']);
		if ($UserData = $UserDataList->GetNext())
			$ClientRequest->arParameters = Array('USER_ID' => $UserData['XML_ID']);
		else
		{
			$USER->LAST_ERROR = 'Ошибка взаимодействия с хранилищем данных! Повторите попытку! В случае повторного возникновения ошибки убедительная просьба сообщить об этой ошибке администратору сайта!';
			return false;
		}
		
		
		$ControllerResponse = $ClientRequest->Send("/bitrix/admin/icontroller_ws.php");
		return true;
		//W////////////////////////// как обрабатывать неудачные попытки разавторизации?
/*		if ($ControllerResponse->status == '200 OK')
		{
			if ($ControllerResponse->arParameters['SUCCESS'] === true)
				return true;
			else
			{
				$USER->Logout();
				$USER->LAST_ERROR = $ControllerResponse->arParameters['MESSAGE'];
				return false;
			}
		}
		else
		{
			$USER->LAST_ERROR = 'Ошибка взаимодействия с хранилищем данных! Повторите попытку! В случае повторного возникновения ошибки убедительная просьба сообщить об этой ошибке администратору сайта!';
			return false;
		}*/
	}
	
	/**
    * Получение с сайта-контроллера списка всех сайтов (дочерних и самого контроллера)
    * 
    */
	function GetChildSpreadUrl()
	{
		$cache = new CPHPCache();
		
		$CACHE_TIME = 24*60*60;
		
		if (COption::GetOptionString("main", "component_cache_on", "Y") == "N")    
			$arParams['CACHE_TIME'] = 0;
		
		$CACHE_FOLDER = '/iclient/GetChildSpreadUrl/';
		global $USER;
		$CACHE_ID = 1;

		$arResult = false;
		if($cache->InitCache($CACHE_TIME, $CACHE_ID, $CACHE_FOLDER))
		{
			$arResult = $cache->GetVars();
		}
		elseif($cache->StartDataCache($CACHE_TIME, $CACHE_ID, $CACHE_FOLDER))
		{
			$ControllerClient = new CControllerClient;
			$ClientRequest = new CIControllerClientRequestTo;
			
			$ClientRequest->operation = 'CIController::GetChildSpreadUrl';
			$ControllerResponse = $ClientRequest->Send("/bitrix/admin/icontroller_ws.php");
			
			if ($ControllerResponse->status == '200 OK')
				$arResult = $ControllerResponse->arParameters;
			else
				$arResult = false;
			
			$cache->EndDataCache($arResult);
		}
		
		return $arResult;
	}
	
	/**
    * Получение данных пользователя с контроллера по ID либо по логину
    * 
    * @param mixed $arFields
    */
	function GetInfo($arFields)
	{
		global $USER;
		
		$ControllerClient = new CControllerClient;
		$ClientRequest = new CIControllerClientRequestTo;
		
		$ClientRequest->operation = 'CIControllerUser::GetInfo';
		$ClientRequest->arParameters = $arFields;
		
		$ControllerResponse = $ClientRequest->Send("/bitrix/admin/icontroller_ws.php");
		if ($ControllerResponse->status == '200 OK')
			return $ControllerResponse->arParameters;
		else
			return false;
	}
	

	/**
    * Получение набора групп, которым будет принадлежать пользователь
    * 
    */
/*
	function GetUserGroupsFromCtrl()
	{
		$ControllerClient = new CControllerClient;
		$ClientRequest = new CIControllerClientRequestTo;
		
		$ClientRequest->operation = 'CIControllerUserGroups::GetMatrixUserGroupsFromCtrl';
//		$ClientRequest->arParameters = $arParams;
		
		$ControllerResponse = $ClientRequest->Send("/bitrix/admin/icontroller_ws.php");
		
		if (is_array($ControllerResponse->arParameters['MATRIX']) && count ($ControllerResponse->arParameters['MATRIX']))
			return $ControllerResponse->arParameters['MATRIX'];
		else
			return false;
	}
*/
}






class CIAllClientSiteUserFromController
{
	function Add($arParams)
	{
		$ID = intval($arParams['ID']);
		$arFields = $arParams['arUser'];
		
		if($ID<=0)
			return array('SUCCESS'=>false, 'ERROR_CODE'=>2001, 'MESSAGE'=>'Wrong User ID', 'ID'=>false);

		$user1 = new CUser;
//		$rsUser = CUser::GetByID($ID);
		$rsUser = CUser::GetList(($by="id"), ($order="asc"), Array('XML_ID' => $ID));
		
		$tmpname = '';
		if (isset($arParams['file']) && strlen($arParams['file']))
		{
			CheckDirPath($_SERVER["DOCUMENT_ROOT"]."/bitrix/tmp/");
			$tmpname = tempnam($_SERVER["DOCUMENT_ROOT"]."/bitrix/tmp", "ictrl_uadd_");
			if(strlen($tmpname) > 0)
			{
				$fp = fopen($tmpname, 'wb');
				if($fp)
				{
					fwrite($fp, $arParams['file']);
					fclose($fp);
					$arfilef = array(
						'name' => ( strlen($arFields['PERSONAL_PHOTO']['ORIGINAL_NAME']) > 0 ? $arFields['PERSONAL_PHOTO']['ORIGINAL_NAME'] : (strlen($arFields['PERSONAL_PHOTO']['FILE_NAME']) > 0 ?  $arFields['PERSONAL_PHOTO']['FILE_NAME'] : $arFields['PERSONAL_PHOTO']['name'])),
						'size' => ($arFields['PERSONAL_PHOTO']['FILE_SIZE'] ? $arFields['PERSONAL_PHOTO']['FILE_SIZE'] : ( $arFields['PERSONAL_PHOTO']['size'] ? $arFields['PERSONAL_PHOTO']['size'] : filesize($tmpname))),
						'tmp_name' =>  $tmpname,
						'type' => (strlen($arFields['PERSONAL_PHOTO']['CONTENT_TYPE'])>0 ?  $arFields['PERSONAL_PHOTO']['CONTENT_TYPE'] : $arFields['PERSONAL_PHOTO']['type']),
						'MODULE_ID' => 'main'
					);
					$arFields['PERSONAL_PHOTO'] = $arfilef;
				}
			}
		}
	
		$arExistFields = CIClientSiteUser::GetFields('ADD');
		$arUserFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields("USER");
		
		foreach($arExistFields as $field => $afield )
		{
			if($afield['TYPE'] == 'L' && $afield['UFT'] == 'UF' && !$afield['MULT'])
			{
				if(strlen($arFields[$field]) > 0)
				{
					$FIELD_ID = $arUserFields[$field]["ID"];
					if($FIELD_ID)
					{
						$rsUV = CUserFieldEnum::GetList(array(), array('USER_FIELD_ID'=>$FIELD_ID, "VALUE" => $arFields[$field]));   
						if($arUV = $rsUV->Fetch())
							$arFields[$field] = $arUV['ID'];
						else
						{
							$obEnum = new CUserFieldEnum;    
							$obEnum->SetEnumValues($FIELD_ID, array("n0" => array("VALUE" => $arFields[$field])));    
							$rsEnum = CUserFieldEnum::GetList(array(), array('USER_FIELD_ID'=>$FIELD_ID, "VALUE" =>$arFields[$field]));
							if($arEnum = $rsEnum->Fetch())
								$arFields[$field] = $arEnum['ID'];    
						}        
					 }
				}
			}
			elseif($afield['TYPE'] == 'L' && $afield['UFT'] == 'UF' && $afield['MULT'])
			{
				if(is_array($arFields[$field]) && $arFields[$field])
				{
					$FIELD_ID = $arFields["ID"];
					if($FIELD_ID)
					{
						$rsUV = CUserFieldEnum::GetList(array(), array( 'USER_FIELD_ID'=>$FIELD_ID));
						$arUValues = array();   
						while($arUV = $rsUV->Fetch())
							$arUValues[$arUV['VALUE']] = $arUV['ID'];
						foreach($arFields[$field] as $uv)
						{
							$uv = trim($uv);
							if(strlen($uv) > 0)
							{
								$uvn = $arUValues[$uv];
								if(strlen($uvn) > 0)
									$arFields[$field][] = $uvn;  
								else
								{
									$obEnum = new CUserFieldEnum;    
									$obEnum->SetEnumValues($FIELD_ID, array("n0" => array("VALUE" => $uv)));    
									$rsEnum = CUserFieldEnum::GetList(array(), array('USER_FIELD_ID'=>$FIELD_ID, "VALUE" =>$uv));
									if($arEnum = $rsEnum->Fetch())
										$arFields[$field][] = $arEnum['ID'];        
								}  
							}
						}        
					 }
				}
			}
		}
		
		$InterestAreaIblockID = CIClientSiteInterestArea::GetIblockId();
		$NewInterestArr = Array();
		foreach ($arFields['UF_INTEREST'] as $InterestArea)
		{
			CIClientSiteInterestArea::CheckSection($InterestArea);
			$InterestList = CIBlockSection::GetList(Array(), Array('XML_ID' => $InterestArea, 'IBLOCK_ID' => $InterestAreaIblockID));
			if ($NewInterest = $InterestList->Fetch())
				$NewInterestArr[] = $NewInterest['ID'];
			
		}
		$arFields['UF_INTEREST'] = $NewInterestArr;
		
		if ($arFields['UF_COMPANY'])
		{
			$CompanyIblockID = CIClientSiteCompany::GetIblockId();
			$CompanyList = CIBlockElement::GetList(Array(), Array('XML_ID' => $arFields['UF_COMPANY'], 'IBLOCK_ID' => $CompanyIblockID), false, Array('nTopCount' => 1), Array('ID', 'IBLOCK_ID'));
			if ($Company = $CompanyList->GetNext())
				$arFields['UF_COMPANY'] = $Company['ID'];
			else
				$arFields['UF_COMPANY'] = 0;
		}
		
		if($arUser = $rsUser->Fetch())
		{
			$GLOBALS['IS_SYSTEM_UPDATE'] = 'Y'; // чтобы не отрабатывал обработчик события
			unset($arFields['GROUP_ID']);
			if($res = $user1->Update($arUser['ID'], $arFields))
			{
				global $DB;
				$strSql = 'UPDATE b_user SET PASSWORD="'.$arFields['PASSWORD'].'" WHERE ID='.$arUser['ID'];
				$res = $DB->Query($strSql, true);
				return array('SUCCESS'=>true, 'ERROR_CODE'=>0, 'MESSAGE'=>'User update ok', 'arFields'=>$arFields);
			}
			else
			{
				return array('SUCCESS'=>false, 'ERROR_CODE'=>2002, 'MESSAGE'=>'Error: '.$user1->LAST_ERROR, 'XML_ID'=>false);
			}
		}
		else
		{
			return array('SUCCESS'=>false, 'ERROR_CODE'=>2009, 'MESSAGE'=>'Error: Nonexistent user', 'XML_ID'=>false);
			/*
			$GLOBALS['IS_SYSTEM_ADD'] = 'Y'; // чтобы не отрабатывал обработчик события
			$arFields['XML_ID'] = $ID;
			if($USER_ID = $user1->Add($arFields))
			{
				global $CLIENT_USER_ID;
				$CLIENT_USER_ID = $USER_ID;
				
				global $DB;
				$strSql = 'UPDATE b_user SET PASSWORD="'.$arFields['PASSWORD'].'" WHERE ID='.$USER_ID;
				$res = $DB->Query($strSql, true);
				return array('SUCCESS'=>true, 'ERROR_CODE'=>0, 'MESSAGE'=>'User add ok', 'arFields'=>$arFields);
			}
			else
			{
				return array('SUCCESS'=>false, 'ERROR_CODE'=>2002, 'MESSAGE'=>'Error: '.$user1->LAST_ERROR, 'XML_ID'=>false);
			}
			*/
		}
	}
	
	function AddWithGroups($arParams)
	{
		$ID = intval($arParams['ID']);
		$arFields = $arParams['arUser'];
		
		if($ID<=0)
			return array('SUCCESS'=>false, 'ERROR_CODE'=>2001, 'MESSAGE'=>'Wrong User ID', 'ID'=>false);

		$user1 = new CUser;
//		$rsUser = CUser::GetByID($ID);
		$rsUser = CUser::GetList(($by="id"), ($order="asc"), Array('XML_ID' => $ID));
		
		$tmpname = '';
		if (isset($arParams['file']) && strlen($arParams['file']))
		{
			CheckDirPath($_SERVER["DOCUMENT_ROOT"]."/bitrix/tmp/");
			$tmpname = tempnam($_SERVER["DOCUMENT_ROOT"]."/bitrix/tmp", "ictrl_uadd_");
			if(strlen($tmpname) > 0)
			{
				$fp = fopen($tmpname, 'wb');
				if($fp)
				{
					fwrite($fp, $arParams['file']);
					fclose($fp);
					$arfilef = array(
						'name' => ( strlen($arFields['PERSONAL_PHOTO']['ORIGINAL_NAME']) > 0 ? $arFields['PERSONAL_PHOTO']['ORIGINAL_NAME'] : (strlen($arFields['PERSONAL_PHOTO']['FILE_NAME']) > 0 ?  $arFields['PERSONAL_PHOTO']['FILE_NAME'] : $arFields['PERSONAL_PHOTO']['name'])),
						'size' => ($arFields['PERSONAL_PHOTO']['FILE_SIZE'] ? $arFields['PERSONAL_PHOTO']['FILE_SIZE'] : ( $arFields['PERSONAL_PHOTO']['size'] ? $arFields['PERSONAL_PHOTO']['size'] : filesize($tmpname))),
						'tmp_name' =>  $tmpname,
						'type' => (strlen($arFields['PERSONAL_PHOTO']['CONTENT_TYPE'])>0 ?  $arFields['PERSONAL_PHOTO']['CONTENT_TYPE'] : $arFields['PERSONAL_PHOTO']['type']),
						'MODULE_ID' => 'main'
					);
					$arFields['PERSONAL_PHOTO'] = $arfilef;
				}
			}
		}
		
		
		$arExistFields = CIClientSiteUser::GetFields('ADD');
		$arUserFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields("USER");
		
		foreach($arExistFields as $field => $afield )
		{
			if($afield['TYPE'] == 'L' && $afield['UFT'] == 'UF' && !$afield['MULT'])
			{
				
				if(strlen($arFields[$field]) > 0)
				{
					$FIELD_ID = $arUserFields[$field]["ID"];
					if($FIELD_ID)
					{
						$rsUV = CUserFieldEnum::GetList(array(), array('USER_FIELD_ID'=>$FIELD_ID, "VALUE" => $arFields[$field]));   
						if($arUV = $rsUV->Fetch())
							$arFields[$field] = $arUV['ID'];
						else
						{
							$obEnum = new CUserFieldEnum;    
							$obEnum->SetEnumValues($FIELD_ID, array("n0" => array("VALUE" => $arFields[$field])));    
							$rsEnum = CUserFieldEnum::GetList(array(), array('USER_FIELD_ID'=>$FIELD_ID, "VALUE" =>$arFields[$field]));
							if($arEnum = $rsEnum->Fetch())
								$arFields[$field] = $arEnum['ID'];    
						}        
					 }
				}
			}
			elseif($afield['TYPE'] == 'L' && $afield['UFT'] == 'UF' && $afield['MULT'])
			{
				if(is_array($arFields[$field]) && $arFields[$field])
				{
					$FIELD_ID = $arFields["ID"];
					if($FIELD_ID)
					{
						$rsUV = CUserFieldEnum::GetList(array(), array( 'USER_FIELD_ID'=>$FIELD_ID));
						$arUValues = array();   
						while($arUV = $rsUV->Fetch())
							$arUValues[$arUV['VALUE']] = $arUV['ID'];
						foreach($arFields[$field] as $uv)
						{
							$uv = trim($uv);
							if(strlen($uv) > 0)
							{
								$uvn = $arUValues[$uv];
								if(strlen($uvn) > 0)
									$arFields[$field][] = $uvn;  
								else
								{
									$obEnum = new CUserFieldEnum;    
									$obEnum->SetEnumValues($FIELD_ID, array("n0" => array("VALUE" => $uv)));    
									$rsEnum = CUserFieldEnum::GetList(array(), array('USER_FIELD_ID'=>$FIELD_ID, "VALUE" =>$uv));
									if($arEnum = $rsEnum->Fetch())
										$arFields[$field][] = $arEnum['ID'];        
								}  
							}
						}        
					 }
				}
			}
		}
		
		$InterestAreaIblockID = CIClientSiteInterestArea::GetIblockId();
		$NewInterestArr = Array();
		foreach ($arFields['UF_INTEREST'] as $InterestArea)
		{
			CIClientSiteInterestArea::CheckSection($InterestArea);
			$InterestList = CIBlockSection::GetList(Array(), Array('XML_ID' => $InterestArea, 'IBLOCK_ID' => $InterestAreaIblockID));
			if ($NewInterest = $InterestList->Fetch())
				$NewInterestArr[] = $NewInterest['ID'];
			
		}
		$arFields['UF_INTEREST'] = $NewInterestArr;
		
		if ($arFields['UF_COMPANY'])
		{
			$CompanyIblockID = CIClientSiteCompany::GetIblockId();
			$CompanyList = CIBlockElement::GetList(Array(), Array('XML_ID' => $arFields['UF_COMPANY'], 'IBLOCK_ID' => $CompanyIblockID), false, Array('nTopCount' => 1), Array('ID', 'IBLOCK_ID'));
			if ($Company = $CompanyList->GetNext())
				$arFields['UF_COMPANY'] = $Company['ID'];
			else
				$arFields['UF_COMPANY'] = 0;
		}
		
		if($arUser = $rsUser->Fetch())
		{
			$GLOBALS['IS_SYSTEM_UPDATE'] = 'Y'; // чтобы не отрабатывал обработчик события
			if($res = $user1->Update($arUser['ID'], $arFields))
			{
				global $DB;
				$strSql = 'UPDATE b_user SET PASSWORD="'.$arFields['PASSWORD'].'" WHERE ID='.$arUser['ID'];
				$res = $DB->Query($strSql, true);
				return array('SUCCESS'=>true, 'ERROR_CODE'=>0, 'MESSAGE'=>'User update ok', 'arFields'=>$arFields);
			}
			else
			{
				return array('SUCCESS'=>false, 'ERROR_CODE'=>2002, 'MESSAGE'=>'Error: '.$user1->LAST_ERROR, 'XML_ID'=>false);
			}
		}
		else
		{
			$GLOBALS['IS_SYSTEM_ADD'] = 'Y'; // чтобы не отрабатывал обработчик события
			$arFields['XML_ID'] = $ID;
			if($USER_ID = $user1->Add($arFields))
			{
				global $CLIENT_USER_ID;
				$CLIENT_USER_ID = $USER_ID;
				
				global $DB;
				$strSql = 'UPDATE b_user SET PASSWORD="'.$arFields['PASSWORD'].'" WHERE ID='.$USER_ID;
				$res = $DB->Query($strSql, true);
				return array('SUCCESS'=>true, 'ERROR_CODE'=>0, 'MESSAGE'=>'User add ok', 'arFields'=>$arFields);
			}
			else
			{
				return array('SUCCESS'=>false, 'ERROR_CODE'=>2006, 'MESSAGE'=>'Error: '.$user1->LAST_ERROR, 'XML_ID'=>false);
			}
		}
	}
	
	function Delete($arParams)
	{
		$ID = intval($arParams['ID']);
		
		if($ID<=0)
			return array('SUCCESS'=>false, 'ERROR_CODE'=>2001, 'MESSAGE'=>'Wrong user ID', 'ID'=>false);
		
		$CheckUserList = CUser::GetList(($by="id"), ($order="asc"), Array('XML_ID' => $ID));
		if ($CheckUser = $CheckUserList->GetNext())
		{
			if(CUser::Delete($CheckUser['ID']))
				return array('SUCCESS'=>true, 'ERROR_CODE'=>0, 'MESSAGE'=>'User delete ok', 'XML_ID'=>$CheckUser['ID']);
			else
				return array('SUCCESS'=>false, 'ERROR_CODE'=>2003, 'MESSAGE'=>'Error delete user', 'XML_ID'=>false);
		}
		else
			return array('SUCCESS'=>false, 'ERROR_CODE'=>2004, 'MESSAGE'=>'Error delete user', 'XML_ID'=>false);
	}
	
	
	
	
	function GetGroupsList($arParams)
	{
		$arFilter = array();
		$rsGroups = CGroup::GetList(($by="id"), ($order="asc"), $arFilter);
		
		$arGroups = array();
		while($arGroup = $rsGroups->GetNext())
		{
			$arGroups[] = $arGroup;
		}
		return array('SUCCESS'=>true, 'ERROR_CODE'=>0, 'MESSAGE'=>'', 'arGroups'=>$arGroups);
	}
	
	function GetUserGroups($arParams)
	{
		$ID = intval($arParams['ID']);
		
		if($ID<=0)
			return array('SUCCESS'=>false, 'ERROR_CODE'=>2001, 'MESSAGE'=>'Wrong user ID', 'arGroups'=>array());
		
		$CheckUserList = CUser::GetList(($by="id"), ($order="asc"), Array('XML_ID' => $ID));
		if ($CheckUser = $CheckUserList->Fetch())
		{
			$arGroups = CUser::GetUserGroup($CheckUser['ID']);

			return array('SUCCESS'=>true, 'ERROR_CODE'=>0, 'MESSAGE'=>'', 'arGroups'=>$arGroups);
		}
		else
		/// NO CHANGE ERROR_CODE!!!!!!!
			return array('SUCCESS'=>false, 'ERROR_CODE'=>2002, 'MESSAGE'=>'Wrong user', 'arGroups'=>array());
		
	}
	
	function SetUserGroups($arParams)
	{
		$ID = intval($arParams['ID']);
		$arGroups = $arParams['arGroups'];
		
		if($ID<=0)
			return array('SUCCESS'=>false, 'ERROR_CODE'=>2001, 'MESSAGE'=>'Wrong user ID', 'ID'=>false);
			
		if(!is_array($arGroups) || count($arGroups)<=0)
			return array('SUCCESS'=>false, 'ERROR_CODE'=>2005, 'MESSAGE'=>'Wrong user groups', 'ID'=>false);
		
		$CheckUserList = CUser::GetList(($by="id"), ($order="asc"), Array('XML_ID' => $ID));
		if ($CheckUser = $CheckUserList->Fetch())
		{
			CUser::SetUserGroup($CheckUser['ID'], $arGroups);

			return array('SUCCESS'=>true, 'ERROR_CODE'=>0, 'MESSAGE'=>'Set user group ok', 'arGroups'=>$arGroups);
		}
	}
}
?>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     