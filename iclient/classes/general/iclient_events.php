<?
class CIClientUserAddHandlerClass
{
    function OnBeforeUserAddHandler(&$arFields)
    {
		if($GLOBALS['IS_SYSTEM_ADD']=='Y' || $GLOBALS['IS_SYSTEM_EVENT']=='Y')
		{
			$GLOBALS['IS_SYSTEM_ADD'] = 'N';
			$GLOBALS['IS_SYSTEM_EVENT'] = 'N';
			return true;
		}
		
		global $USER, $CHECKER;

		if(!$CHECKER)
		{
			$FieldsChecker = false;
			$CHECKER = true;
			$FieldsChecker = $USER->CheckFields($arFields);
			$CHECKER = false;
			if($FieldsChecker)
			{
				$arFieldsController = $arFields;
				
				// в списковых полях передаем сами значения, а не ID
				global $USER_FIELD_MANAGER;
				$arFieldsUF = $USER_FIELD_MANAGER->GetUserFields("USER");

				$arEnums = array();
				$rsUFProp = CUserFieldEnum::GetList(array(), array());
				while($arEnum = $rsUFProp->GetNext())
				{
					$arEnums[$arEnum['ID']] = $arEnum;
				}

				foreach($arFieldsUF as $arUFProp)
				{
					if($arUFProp['USER_TYPE']['BASE_TYPE']=='enum')
						$arFieldsController[$arUFProp['FIELD_NAME']] = $arEnums[$arFields[$arUFProp['FIELD_NAME']]]['VALUE'];
				}

				CModule::IncludeModule('iblock');
				// компанию передаем XML_ID вместо обычного ID
				if($arFieldsController['UF_COMPANY']>0)
				{
					$arFiltr = Array("ID"=>$arFieldsController['UF_COMPANY'], 'IBLOCK_ID'=>IBLOCK_ID_company);
					$resEl = CIBlockElement::GetList(Array(), $arFiltr, false, Array("nPageSize"=>1), Array("ID", "IBLOCK_ID", "XML_ID"));
					if($arEl = $resEl->GetNext())
					{
						$arFieldsController['UF_COMPANY'] = $arEl['XML_ID'];
					}
					else
					{
						unset($arFieldsController['UF_COMPANY']);
					}
				}

				// передаем XML_ID вместо обычного ID области интересов
				if(count($arFieldsController['UF_INTEREST']) > 0)
				{
					$InterestAreas = Array();
					$InterestAreaIblockID = CIClientSiteInterestArea::GetIblockId();
					foreach ($arFieldsController['UF_INTEREST'] as $InterestArea)
					{
						$arFiltr = Array("ID"=>intval($InterestArea), 'IBLOCK_ID'=>$InterestAreaIblockID);
						$resEl = CIBlockSection::GetList(Array(), $arFiltr);
						if($arEl = $resEl->GetNext())
							$InterestAreas[] = $arEl['XML_ID'];
					}
					$arFieldsController['UF_INTEREST'] = $InterestAreas;
				}
				
				
				// передаем фотографию
				if(is_array($arFieldsController['PERSONAL_PHOTO']))
				{
					$arFieldsController['file'] = file_get_contents($arFieldsController['PERSONAL_PHOTO']['tmp_name'], FILE_BINARY);
				}

				// Если у групп указаны периоды действия, то удаляем их (периоды)
				if (is_array($arFieldsController['GROUP_ID']) && count($arFieldsController['GROUP_ID']))
				{
					$NewGroupsArr = Array();
					foreach ($arFieldsController['GROUP_ID'] as $Group)
					{
						if(is_array($Group) && count($Group))
							$NewGroupsArr[] = $Group['GROUP_ID'];
						else
							$NewGroupsArr[] = $Group;
					}
					unset($arFieldsController['GROUP_ID']);
					$arFieldsController['GROUP_ID'] = $NewGroupsArr;
					$arFields['GROUP_ID'] = $arFieldsController['GROUP_ID'];
				}

				if ($ID = CIClientSiteUser::Add($arFieldsController))
				{
					global $CLIENT_USER_ID;
					$CLIENT_USER_ID = $ID;
				}
				else
				{
					global $APPLICATION;
					$APPLICATION->throwException($USER->LAST_ERROR);
					return false;
				}
			}
		}
    }
	
	function OnAfterUserAddHandler(&$arFields)
    {
		global $CLIENT_USER_ID, $USER, $DB, $IS_SYSTEM_UPDATE;

		if (intval($CLIENT_USER_ID))
		{
			$IS_SYSTEM_UPDATE = 'Y';
			$UpdateXMLID = new CUser;
			if (!$NEWXMLID = $UpdateXMLID->Update($arFields['ID'], Array('XML_ID' => intval($CLIENT_USER_ID))))
			{
				$USER->Delete($arFields['ID']);
				$USER->LAST_ERROR = 'Error work with DB! Try again. In case of repeated errors, contact your administrator!';
				return false;
			}
/*
			от этого маразма избавились
			
			// Lord, forgive me for my sins and give me strength to write these iniquitous patches more and more.......
			$DB->StartTransaction();
			$CurrentMaxIDSQL = 'SELECT MAX(ID) FROM b_user';
			$CurrentMaxIDRes = $DB->Query($CurrentMaxIDSQL);
			if ($Current = $CurrentMaxIDRes->Fetch())
			{
				$NewAutoIncrement = intval($Current['MAX(ID)'])+1;
				$AutoIncrementUpdateSQL = 'ALTER TABLE b_user AUTO_INCREMENT='.$NewAutoIncrement;
				if ($AutoIncrementUpdateRes = $DB->Query($AutoIncrementUpdateSQL, true))
				{
					$StrSQL = 'UPDATE b_user SET ID="'.$CLIENT_USER_ID.'" WHERE ID="'.intval($arFields['ID']).'"';
					$UpdateUserIDResult = $DB->Query($StrSQL, true);
					if (!$UpdateUserIDResult)
					{
						$DB->Rollback();
						$USER->Delete(intval($arFields['ID']));
						$USER->LAST_ERROR = 'Error work with DB! Try again. In case of repeated errors, contact your administrator!';
						return false;
					}
					else
						$DB->Commit();
				}
				else
				{
					$DB->Rollback();
					$USER->LAST_ERROR = 'Error work with DB! Try again. In case of repeated errors, contact your administrator!';
					return false;
				}
			}
			else
			{
				$DB->Rollback();
				$USER->LAST_ERROR = 'Error work with DB! Try again. In case of repeated errors, contact your administrator!';
				return false;
			}
*/
		}
    }
}






class CIClientUserUpdateHandlerClass
{
    function OnBeforeUserUpdateHandler(&$arFields)
    {
		if($GLOBALS['IS_SYSTEM_UPDATE']=='Y' || $GLOBALS['IS_SYSTEM_EVENT']=='Y')
		{
			$GLOBALS['IS_SYSTEM_UPDATE'] = 'N';
			$GLOBALS['IS_SYSTEM_EVENT'] = 'N';
			return true;
		}
		
		global $USER, $CHECKER;

		if(!$CHECKER)
		{
			$FieldsChecker = false;
			$CHECKER = true;
			$FieldsChecker = $USER->CheckFields($arFields, $arFields['ID']);
			$CHECKER = false;
			if($FieldsChecker)
			{
				$arFieldsController = $arFields;
				
				// в списковых полях передаем сами значения, а не ID
				global $USER_FIELD_MANAGER;
				$arFieldsUF = $USER_FIELD_MANAGER->GetUserFields("USER");

				$arEnums = array();
				$rsUFProp = CUserFieldEnum::GetList(array(), array());
				while($arEnum = $rsUFProp->GetNext())
				{
					$arEnums[$arEnum['ID']] = $arEnum;
				}

				foreach($arFieldsUF as $arUFProp)
				{
					if($arUFProp['USER_TYPE']['BASE_TYPE']=='enum')
						$arFieldsController[$arUFProp['FIELD_NAME']] = $arEnums[$arFields[$arUFProp['FIELD_NAME']]]['VALUE'];
				}
				
				CModule::IncludeModule('iblock');
				// компанию передаем XML_ID вместо обычного ID
				if($arFieldsController['UF_COMPANY']>0)
				{
					$resEl = CIBlockElement::GetList(Array(), Array("ID"=>$arFieldsController['UF_COMPANY'], 'IBLOCK_ID'=>IBLOCK_ID_company), false, Array("nPageSize"=>1), Array("ID", "IBLOCK_ID", "XML_ID"));
					if($arEl = $resEl->GetNext())
					{
						$arFieldsController['UF_COMPANY'] = $arEl['XML_ID'];
					}
					else
					{
						unset($arFieldsController['UF_COMPANY']);
					}
				}

				// передаем XML_ID вместо обычного ID области интересов
				if(count($arFieldsController['UF_INTEREST']) > 0)
				{
					$InterestAreas = Array();
					$InterestAreaIblockID = CIClientSiteInterestArea::GetIblockId();
					foreach ($arFieldsController['UF_INTEREST'] as $InterestArea)
					{
						$arFiltr = Array("ID"=>intval($InterestArea), 'IBLOCK_ID'=>$InterestAreaIblockID);
						$resEl = CIBlockSection::GetList(Array(), $arFiltr);
						if($arEl = $resEl->GetNext())
						{
							$InterestAreas[] = $arEl['XML_ID'];
						}
					}
					$arFieldsController['UF_INTEREST'] = $InterestAreas;
				}
				
				// передаем фотографию
				if(is_array($arFieldsController['PERSONAL_PHOTO']))
				{
					$arFieldsController['file'] = file_get_contents($arFieldsController['PERSONAL_PHOTO']['tmp_name'], FILE_BINARY);
				}
				
				
				// Если у групп указаны периоды действия, то удаляем их (периоды)
				if (is_array($arFieldsController['GROUP_ID']) && count($arFieldsController['GROUP_ID']))
				{
					$NewGroupsArr = Array();
					foreach ($arFieldsController['GROUP_ID'] as $Group)
					{
						if(is_array($Group) && count($Group))
						{
							$NewGroupsArr[] = $Group['GROUP_ID'];
						}
					}
					unset($arFieldsController['GROUP_ID']);
					$arFieldsController['GROUP_ID'] = $NewGroupsArr;
					$arFields['GROUP_ID'] = $arFieldsController['GROUP_ID'];
				}
				
				if (!CIClientSiteUser::Update($arFieldsController))
				{
					global $APPLICATION;
					$APPLICATION->throwException($USER->LAST_ERROR);
					return false;
				}
			}
		}
    }
}




class CIClientUserDeleteHandlerClass
{
    function OnUserDeleteHandler(&$ID)
    {
		if($GLOBALS['IS_SYSTEM_DELETE']=='Y' || $GLOBALS['IS_SYSTEM_EVENT']=='Y')
		{
			$GLOBALS['IS_SYSTEM_DELETE'] = 'N';
			$GLOBALS['IS_SYSTEM_EVENT'] = 'N';
			return true;
		}
		$UserList = CUser::GetByID($ID);
		if ($UserData = $UserList->GetNext())
		{
			CIClientSiteUser::Delete($UserData['XML_ID']);
		}
    }
}




class CIClientUserLoginHandlerClass
{
    function OnBeforeUserLoginHandler(&$arFields)
	{
		global $USER;
		if (!CIClientSiteUser::Authorize($arFields))
		{
			global $APPLICATION;
			$APPLICATION->throwException($USER->LAST_ERROR);
			return false;
		}
    }
}



class CIClientUserLogoutHandlerClass
{
    function OnBeforeUserLogoutHandler($arParams)
    {
        global $USER;
		if (!CIClientSiteUser::Logout($arParams))
		{
			global $APPLICATION;
			$APPLICATION->throwException($USER->LAST_ERROR);
			return false;
		}
    }
}


class CIClientOnPageStartClass
{
	function OnPageStartHandler($arParams)
	{
		//define global user object
		$GLOBALS["USER"] = new CUser;
		CIClientSiteUser::CheckAuth();
	}
}


class CIClientOnEpilogHandlerClass
{
    function OnEpilogHandler($arParams)
    {
		if(defined("ERROR_404") || defined("NO_SPREAD_COOKIE"))
            return;
        global $APPLICATION, $COOKIE_STORAGE;
		
		$params = Array();
		foreach ($COOKIE_STORAGE as $CookieName=>$Cookie)
		{
			if(!$Cookie['FROM_BROWSER'])
			{
				$params[] = $CookieName.chr(1).$Cookie['VALUE'].chr(1).$Cookie['TIME'].chr(1).$Cookie['FOLDER'].chr(1).$Cookie['DOMAIN'].chr(1).$Cookie['SECURE'].chr(2);
				setcookie($CookieName, $Cookie['VALUE'], $Cookie['TIME'], $Cookie['FOLDER'], $Cookie['DOMAIN'], $Cookie['SECURE']);
			}
		}
		$params = implode('', $params);
		
		if(strlen($params)>0)
		{
			$params = base64_encode($params);
			$arSites = CIClientSiteUser::GetChildSpreadUrl();
			if (is_array($arSites) && count($arSites))
			{
				foreach ($arSites as $Site)
				{
					
					$key = md5($params.$Site['SALT']);
					$url = $Site['URL']."?s=".$params.'&k='.$key;
					echo '<img src="'.htmlspecialchars($url).'" alt="" style="width:0px; height:0px; position:absolute; left:-1px; top:-1px;" />'."\n";
				}
			}
		}
    }
}



class CIClientOnIblockClass
{
    function OnBeforeIBlockElementAddHandler(&$arFields)
	{
		if($GLOBALS['IS_SYSTEM_ADD']=='Y' || $GLOBALS['IS_SYSTEM_EVENT']=='Y')
		{
			$GLOBALS['IS_SYSTEM_ADD'] = 'N';
			$GLOBALS['IS_SYSTEM_EVENT'] = 'N';
			return true;
		}
		
		
		$arFields['PROPERTY_VALUES'] = CIClientTools::GetNormalProps($arFields['IBLOCK_ID'], $arFields['PROPERTY_VALUES']);
		
		if($arFields['IBLOCK_ID']==IBLOCK_ID_company)
		{
			$arResult = CIClientSiteCompany::Add($arFields);
			if ($arResult['ERROR_CODE']==0)
			{
				$arFields['XML_ID'] = $arResult['XML_ID'];
				
				$resEl = CIBlockElement::GetList(Array(), Array("XML_ID"=>$arFields['XML_ID']), false, Array("nPageSize"=>1), Array("ID", "IBLOCK_ID", "XML_ID"));
				if($arEl = $resEl->GetNext())
				{
					unset($arFields['ID']);
					$GLOBALS['IS_SYSTEM_UPDATE'] = 'Y'; // чтобы не отрабатывал обработчик события
					$el = new CIBlockElement;
					$res = $el->Update($arEl['ID'], $arFields);
					return false;
				}
			}
			else
			{
				global $APPLICATION;
				$APPLICATION->throwException($arResult['MESSAGE']);
				return false;
			}
		}
	}
	
	
    function OnBeforeIBlockElementUpdateHandler(&$arFields)
	{
		if($GLOBALS['IS_SYSTEM_UPDATE']=='Y' || $GLOBALS['IS_SYSTEM_EVENT']=='Y')
		{
			$GLOBALS['IS_SYSTEM_UPDATE'] = 'N';
			$GLOBALS['IS_SYSTEM_EVENT'] = 'N';
			return true;
		}
		
		
		$arFields['PROPERTY_VALUES'] = CIClientTools::GetNormalProps($arFields['IBLOCK_ID'], $arFields['PROPERTY_VALUES']);

		if($arFields['IBLOCK_ID']==IBLOCK_ID_company)
		{
			$resEl = CIBlockElement::GetList(Array(), Array("ID"=>$arFields['ID']), false, Array("nPageSize"=>1), Array("ID", "IBLOCK_ID", "XML_ID"));
			if($arEl = $resEl->GetNext())
			{
				$XML_ID = $arEl['XML_ID'];
				
				$arResult = CIClientSiteCompany::Update(array('XML_ID'=>$XML_ID, 'arFields'=>$arFields));

				if ($arResult['ERROR_CODE']==0)
				{
					$arFields['XML_ID'] = $arResult['XML_ID'];
				}
				else
				{
					global $APPLICATION;
					$APPLICATION->throwException($arResult['MESSAGE']);
					return false;
				}
			}
		}
	}
	
	
    function OnBeforeIBlockElementDeleteHandler(&$ID)
	{
		if($GLOBALS['IS_CONTROLLER_DELETE']!='Y')
		{
			global $APPLICATION;
			$APPLICATION->throwException('Удаление синхронизируемых данных возможно только на контроллере!');
			return false;
		}
		else
			return true;
/*		
		$resEl = CIBlockElement::GetList(Array(), Array("ID"=>$ID), false, Array("nPageSize"=>1), Array("ID", "IBLOCK_ID"));
		if($arFields = $resEl->GetNext())
		{
			if($arFields['IBLOCK_ID']==IBLOCK_ID_company)
			{
				$arResult = CIClientSiteCompany::Delete($ID);
				
				if ($arResult['ERROR_CODE']==0)
				{
					//ok
				}
				else
				{
					global $APPLICATION;
					$APPLICATION->throwException($arResult['MESSAGE']);
					return false;
				}
			}
		}
*/
	}
	
//W//////////////////////////* SECTIONS */

	function OnBeforeIBlockSectionAddUpdateHandler(&$arFields)
	{
		if($GLOBALS['IS_SYSTEM_SECTION_ADD']=='Y' || $GLOBALS['IS_SYSTEM_SECTION_UPDATE']=='Y' || $GLOBALS['IS_SYSTEM_EVENT']=='Y')
		{
			$GLOBALS['IS_SYSTEM_SECTION_ADD'] = 'N';
			$GLOBALS['IS_SYSTEM_SECTION_UPDATE'] = 'N';
			$GLOBALS['IS_SYSTEM_EVENT'] = 'N';
			return true;
		}
		
		$InterestAreaIblockID = CIClientSiteInterestArea::GetIblockId();
		
		if (intval($arFields['IBLOCK_ID']) <= 0)
		{
			$SectionList = CIBlockSection::GetByID($arFields['ID']);
			$Section = $SectionList->Fetch();
			$arFields['IBLOCK_ID'] = $Section['IBLOCK_ID'];
		}
		
		if ($arFields['IBLOCK_ID'])
		{
			if($arFields['IBLOCK_ID'] == $InterestAreaIblockID)
			{
				$arResult = CIClientSiteInterestArea::AddUpdate($arFields);
				if ($arResult['ERROR_CODE']==0)
				{
					$arFields['XML_ID'] = $arResult['XML_ID'];
					
					if (intval($arFields['ID']) <= 0)
					{
						$resEl = CIBlockSection::GetList(Array(), Array("XML_ID"=>$arFields['XML_ID']));
						if($arEl = $resEl->GetNext())
						{
							unset($arFields['ID']);
							$GLOBALS['IS_SYSTEM_UPDATE'] = 'Y'; // чтобы не отрабатывал обработчик события
							$sect = new CIBlockSection;
							$res = $sect->Update($arEl['ID'], $arFields);
							return false;
						}
					}
					else
						$arFields['XML_ID'] = $arResult['XML_ID'];
				}
				else
				{
					global $APPLICATION;
					$APPLICATION->throwException($arResult['MESSAGE']);
					return false;
				}
			}
		}
		else
		{
			global $APPLICATION;
			$APPLICATION->throwException('Ошибка взаимодействия с хранилищем данных! Обратитесь к администратору сайта!');
			return false;
		}
	}
	
	function OnBeforeIBlockSectionDeleteHandler(&$ID)
	{
/*		if($GLOBALS['IS_CONTROLLER_DELETE']!='Y')
		{
			global $APPLICATION;
				$APPLICATION->throwException('Удаление областей интересов возможно только на контроллере!');
			return false;
		}
		else
			return true;*/
/*
//		if($GLOBALS['IS_CONTROLLER_DELETE']!='Y')
//			return false;
//		else
//			return true;
	
		file_put_contents($_SERVER['DOCUMENT_ROOT'].'/print01.htm', '<pre>'.var_export(array("result"=>$ID), true).'</pre>');
		$resSectionList = CIBlockSection::GetList(Array(), Array("ID"=>$ID));
		if ($resSection = $resSectionList->Fetch())
		{
			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/print02.htm', '<pre>'.var_export(array("result"=>$resSection), true).'</pre>');
			$InterestAreaIblockID = CIClientSiteInterestArea::GetIblockId();
			file_put_contents($_SERVER['DOCUMENT_ROOT'].'/print03.htm', '<pre>'.var_export(array("result"=>$InterestAreaIblockID), true).'</pre>');
			if($resSection['IBLOCK_ID'] == $InterestAreaIblockID)
			{
				$arResult = CIClientSiteInterestArea::Delete($resSection['XML_ID']);
				file_put_contents($_SERVER['DOCUMENT_ROOT'].'/print04.htm', '<pre>'.var_export(array("result"=>$arResult), true).'</pre>');
				if ($arResult['ERROR_CODE']==0)
				{
					//W//////////////////////////
				}
				else
				{
					global $APPLICATION;
					$APPLICATION->throwException($arResult['MESSAGE']);
					return false;
				}
			}
		}
*/
	}
}



class CIClientOnSetOptionClass
{
    function OnSetOptionHandler(&$SID)
	{
		$fp = fopen($_SERVER['DOCUMENT_ROOT'].'/bitrix/SID.dat', 'w');
		if($fp)
		{
			fputs($fp, $SID);
			fclose($fp);
		}
	}
}
?>