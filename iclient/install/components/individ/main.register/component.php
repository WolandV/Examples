<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();


// apply default param values
$arDefaultValues = array(
	"SHOW_FIELDS" => array(),
	"REQUIRED_FIELDS" => array(),
	"AUTH" => "Y",
	"USE_BACKURL" => "Y",
	"SUCCESS_PAGE" => "",
	//"CACHE_TYPE" => "A",
	//"CACHE_TIME" => "3600",
);

foreach ($arDefaultValues as $key => $value)
{
	if (!is_set($arParams, $key)) $arParams[$key] = $value;
}

// if user registration blocked - return auth form
if (COption::GetOptionString("main", "new_user_registration", "N") == "N")
{
	$APPLICATION->AuthForm(array());
}

// apply core fields to user defined
$arDefaultFields = array(
	"LOGIN",
	"PASSWORD",
	"CONFIRM_PASSWORD",
	"EMAIL",
);

$arResult["USE_EMAIL_CONFIRMATION"] = COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y" ? "Y" : "N";
$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
if($def_group!="")
{
	$arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(explode(",", $def_group));
}
else
{
	$arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(array());
}

$arResult["SHOW_FIELDS"] = array_merge($arDefaultFields, $arParams["SHOW_FIELDS"]);
$arResult["USER_PROPERTY"] = array_merge($arDefaultFields, $arParams["USER_PROPERTY"]);
$arResult["REQUIRED_FIELDS"] = array_merge($arDefaultFields, $arParams["REQUIRED_FIELDS"]);



// use captcha?
$arResult["USE_CAPTCHA"] = COption::GetOptionString("main", "captcha_registration", "N") == "Y" ? "Y" : "N";
if ($arParams["USE_CAPTCHA"])
    $arResult["USE_CAPTCHA"]=$arParams["USE_CAPTCHA"];

// start values
$arResult["VALUES"] = array();
$arResult["VALUES"]["PERSONAL_WWW"] = "http://";
$arResult["VALUES"]["WORK_WWW"] = "http://";

$arResult["ERRORS"] = array();

$arResult['arSubscrRubric'] = array();
if(CModule::IncludeModule('subscribe'))
{
    $rsrub = CRubric::GetList(array("SORT"=>"ASC", "NAME"=>"ASC"), array("ACTIVE"=>"Y", "LID"=>LANG, 'VISIBLE' =>'Y'));
    while($arRub = $rsrub->Fetch())
        $arResult['arSubscrRubric'][$arRub['ID']] = $arRub;
}



/**
* пост- пробуем зарегистрировать 
*/
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_REQUEST["register_submit_button"]) && !$USER->IsAuthorized())
{
	

			
	// check emptiness of required fields
	foreach ($arResult["SHOW_FIELDS"] as $key)
	{
		if ($key != "PERSONAL_PHOTO" && $key != "WORK_LOGO")
		{
			$arResult["VALUES"][$key] = $_REQUEST["REGISTER"][$key];
			if (in_array($key, $arResult["REQUIRED_FIELDS"]) && strlen($arResult["VALUES"][$key]) <= 0)
			{
				$arResult["ERRORS"][$key] = GetMessage("REGISTER_FIELD_REQUIRED");				
			}
		}
		else
		{
			$_FILES["REGISTER_FILES_".$key]["MODULE_ID"] = "main";
			$arResult["VALUES"][$key] = $_FILES["REGISTER_FILES_".$key];
			if (in_array($key, $arResult["REQUIRED_FIELDS"]) && !is_uploaded_file($_FILES["REGISTER_FILES_".$key]["tmp_name"]))
			{
				$arResult["ERRORS"][$key] = GetMessage("REGISTER_FIELD_REQUIRED");				
			}
		}
	}
	
	if(strlen($_REQUEST["UF_DATECOMMING"])>0 && strlen($_REQUEST["UF_DATEDEP"])>0){
		if($DB->CompareDates($_REQUEST["UF_DATECOMMING"], $_REQUEST["UF_DATEDEP"]) > 0){
			$arResult["ERRORS"][] = "Дата прибытия в гостинницу должна быть раньше даты убытия";
		}
	}
	
		
	if (in_array("UF_GRADE", $arParams["REQUIRED_FIELDSTOO"]) && $_REQUEST["UF_GRADE"]==UF_GRADE) {
		$arParams["REQUIRED_FIELDSTOO"][]="UF_GRADE2";
	}
	
	if (in_array("UF_STATION", $arParams["REQUIRED_FIELDSTOO"]) && $_REQUEST["UF_STATION"]==UF_STATION) {
		$arParams["REQUIRED_FIELDSTOO"][]="UF_STATION2";
	}
	
	foreach ($arParams["REQUIRED_FIELDSTOO"] as $key)
	{	
		if (in_array($key, $arParams["REQUIRED_FIELDSTOO"]) && (strlen($_REQUEST[$key]) <= 0 && count($_REQUEST[$key])<=0 ) )
		{	
			if ( in_array($key, array("ADDRESS", "WWW", "PHONE", "FAX")) && strlen($_REQUEST["WORKNAME"])<=0 ) {
				continue;
			}
			if ( in_array($key, array("ADDRESS", "WWW", "PHONE", "FAX")) && strlen($_REQUEST["WORKNAME"])<=0 ) {
				continue;
			}
			$arResult["ERRORS"][$key] = str_replace("#FIELD_NAME#", GetMessage("REGISTER_FIELD_$key"), GetMessage("REGISTER_FIELD_REQUIRED"));
		}
	}
	
	
	// check captcha
	if ($arResult["USE_CAPTCHA"] == "Y")
	{
		if (!$APPLICATION->CaptchaCheckCode($_REQUEST["captcha_word"], $_REQUEST["captcha_sid"]))
		{
			$arResult["ERRORS"][] = GetMessage("REGISTER_WRONG_CAPTCHA");
		}
	}

	if(strlen($arResult["VALUES"]["EMAIL"]) > 0 && COption::GetOptionString("main", "new_user_email_uniq_check", "N") === "Y")
	{
		$res = CUser::GetList($b, $o, array("=EMAIL" => $arResult["VALUES"]["EMAIL"]));
		if($res->Fetch())
			$arResult["ERRORS"][] = GetMessage("REGISTER_USER_WITH_EMAIL_EXIST", array("#EMAIL#" => htmlspecialchars($arResult["VALUES"]["EMAIL"])));
	}

    if(is_array($_REQUEST["SubscrRubric"]) )
    {
        $arResult['VALUES']['SubscrRubric'] = array();
        foreach($_REQUEST["SubscrRubric"] as $Rubric)
        {
            $Rubric = intval($Rubric);
            if($Rubric > 0 && array_key_exists($Rubric, $arResult['arSubscrRubric']))
                $arResult['VALUES']['SubscrRubric'][] =  $Rubric; 
        }
    }
    
    
	if(count($arResult["ERRORS"]) > 0)
	{
		if(COption::GetOptionString("main", "event_log_register_fail", "N") === "Y")
		{
			CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", false, implode("<br>", $arResult["ERRORS"]));
		}
	}
	else // if there;s no any errors - create user
	{
		$bConfirmReq = COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y";

		$arResult['VALUES']["CHECKWORD"] = randString(8);
		$arResult['VALUES']["~CHECKWORD_TIME"] = $DB->CurrentTimeFunction();
		$arResult['VALUES']["ACTIVE"] = $bConfirmReq? "N": "Y";
		$arResult['VALUES']["CONFIRM_CODE"] = $bConfirmReq? randString(8): "";
		$arResult['VALUES']["USER_IP"] = $_SERVER["REMOTE_ADDR"];
		$arResult['VALUES']["USER_HOST"] = @gethostbyaddr($REMOTE_ADDR);

		$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
		if($def_group != "")
			$arResult['VALUES']["GROUP_ID"] = explode(",", $def_group);
			
		if (intval($arParams["GROUP_ID"])>0) 
            $arResult['VALUES']["GROUP_ID"][]=$arParams["GROUP_ID"];

		$bOk = true;

		$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("USER", $arResult["VALUES"]);

		$events = GetModuleEvents("main", "OnBeforeUserRegister");
		while($arEvent = $events->Fetch())
		{
			if(ExecuteModuleEvent($arEvent, &$arResult['VALUES']) === false)
			{
				if($err = $APPLICATION->GetException())
					$arResult['ERRORS'][] = $err->GetString();

				$bOk = false;
				break;
			}
		}

		if ($bOk)
		{		
            //xvar_dump(isset($_REQUEST['SEARCH_COMPANY']));
           // xvar_dump($_REQUEST['WAS_PLASH_COMPANY']);
            //die();
            if(CModule::IncludeModule("iblock") && isset($_REQUEST['SEARCH_COMPANY']))
            {
                if($_REQUEST['WAS_PLASH_COMPANY'] == 'Y')
                {
                    $COMPANY_ID = intval($_REQUEST["UF_COMPANY"]);
                    if($COMPANY_ID > 0)
                    {
                        $arSor = array('ID'=>'ASC');
                        $arFil = array('ID'=>$COMPANY_ID, 'ACTIVE'=>'Y', 'IBLOCK_ID'=>COMPANY_IBLOCK_ID);
                        $arSel = array('ID', 'IBLOCK_ID');
                        $arNav = array('nTopCount'=>1);
                        $rs = CIBlockElement::GetList($arSor, $arFil, false, $arNav, $arSel);
                        if($arComp = $rs->Fetch())
                        {
                            $COMPANY_ID = intval($arComp['ID']);    
                        }
                        else
                            $COMPANY_ID = 0;
                    }
                    if($COMPANY_ID <=0 )
                    {
                        $arResult["ERRORS"][] = "Не указана организация";
                        $bOk = false; 
                    }
                }
                elseif($_REQUEST['WAS_FORM_COMPANY'] == 'Y')  
                {
                    
                    $PROP = array();
                    $COMPANY_ID = 0;
                    $WN = htmlspecialchars(trim($_REQUEST["WORKNAME"]));
                    $PROP["SHORTNAME"] = htmlspecialchars(trim($_REQUEST["SHORTNAME"])); 
                    $PROP["ADDRESS"] = htmlspecialchars(trim($_REQUEST["ADDRESS"])); 
                    $PROP["WWW"] = htmlspecialchars(trim($_REQUEST["WWW"])); 
                    $PROP["PHONE"] = htmlspecialchars(trim($_REQUEST["PHONE"])); 
                    $PROP["FAX"] = htmlspecialchars(trim($_REQUEST["FAX"]));    
                    
                    if(strlen($WN)<=0)
                    {
                        $arResult["ERRORS"][] = "Не указано название организации";
                        $bOk = false;     
                    }
                    if(strlen($PROP["ADDRESS"])<=0)
                    {
                        $arResult["ERRORS"][] = "Не указан адрес организации";
                        $bOk = false;     
                    }
                    if(strlen($PROP["WWW"])<=0)
                    {
                        $arResult["ERRORS"][] = "Не указан адрес web сервера организации";
                        $bOk = false;     
                    }
                    if(strlen($PROP["PHONE"])<=0)
                    {
                        $arResult["ERRORS"][] = "Не указан телефон организации";
                        $bOk = false;     
                    }
                    
                    if($bOk)
                    {
                        $el = new CIBlockElement;  
                        $arLoadProductArray = Array(                      
                        "IBLOCK_ID"      => COMPANY_IBLOCK_ID,                    
                        "PROPERTY_VALUES"=> $PROP,  
                        "NAME"           => $_REQUEST["WORKNAME"],  
                        "ACTIVE"         => "Y",            
                         );
                    
                        $COMPANY_ID = $el->Add($arLoadProductArray);
                        if(!$COMPANY_ID ||$COMPANY_ID<=0)
                        {
                            $arResult["ERRORS"][] = "Ошибка создания новой организации"; 
                            $COMPANY_ID = 0;    
                            $bOk = false;
                        } else {
                            mylog("COMPANY: Зарегистрирована новая компания: ".$_REQUEST["WORKNAME"],true);
                        }
                             
                    }
                    else
                        $COMPANY_ID = 0;
                        
                }
                else
                {
                    $arResult["ERRORS"][] = "Не указана организации"; 
                    $COMPANY_ID = 0;    
                    $bOk = false;    
                }
                if($COMPANY_ID > 0)
                    $arResult["VALUES"]["UF_COMPANY"]=$COMPANY_ID;  
                
            }
            
			/*if (strlen($_REQUEST["WORKNAME"])>0 && CModule::IncludeModule("iblock"))
			{				
				
				$el = new CIBlockElement;
				$PROP = array();
				
				$PROP["SHORTNAME"] = $_REQUEST["SHORTNAME"]; 
				$PROP["ADDRESS"] = $_REQUEST["ADDRESS"]; 
				$PROP["WWW"] = $_REQUEST["WWW"]; 
				$PROP["PHONE"] = $_REQUEST["PHONE"]; 
				$PROP["FAX"] = $_REQUEST["FAX"]; 
				
				
				$arLoadProductArray = Array(  				    
					"IBLOCK_ID"      => COMPANY_IBLOCK_ID,					
					"PROPERTY_VALUES"=> $PROP,  
					"NAME"           => $_REQUEST["WORKNAME"],  
					"ACTIVE"         => "Y",            
				);
				
				if($COMPANY_ID = $el->Add($arLoadProductArray)) {
					$arResult["VALUES"]["UF_COMPANY"]=$COMPANY_ID;  
					//echo "New ID: ".$COMPANY_ID;
				}
					//else  echo "Error: ".$el->LAST_ERROR;
					
			}	   */
				
            if($bOk)
            {
                
			    $user = new CUser();
			    if($ID = $user->Add($arResult["VALUES"]))
                    mylog("USER: Зарегистрирована новый пользователь: ".$ID,true);
            }
            
            
		}

		if (intval($ID) > 0)		
		{
            //xvar_dump($arResult['VALUES']['SubscrRubric']);
            if($arResult['VALUES']['SubscrRubric'] && CModule::IncludeModule('subscribe'))
            {
                //foreach($arResult['VALUES']['SubscrRubric'] as $Rubric)
                //{
                $arFieldsSubs = array(
                    'USER_ID' => $ID,
                    'EMAIL' => $arResult["VALUES"]["EMAIL"],
                    'FORMAT' => 'html',
                    'ACTIVE' => 'Y',
                    'RUB_ID' => $arResult['VALUES']['SubscrRubric'],
                    'SEND_CONFIRM' => 'N',
                    "CONFIRMED"=>"Y"
                    );
                    $subscrObj = new CSubscription;

                    $rssubs = CSubscription::GetList(array("ID"=>"ASC"),  array("EMAIL"=>$arResult["VALUES"]["EMAIL"], 'ANONYMOUS' => 'Y'));
                    if($RSubscr = $rssubs->Fetch())
                    {
                        //echo "==============<br>";
                        //xvar_dump($RSubscr);
                        $r = $subscrObj->Update($RSubscr['ID'], $arFieldsSubs, SITE_ID);
                        //xvar_dump($r);
                       // xvar_dump($subscrObj->LAST_ERROR);
                    }
                    else
                    {
                        //echo "==============<br>";
                        $r = $subscrObj->Add($arFieldsSubs, SITE_ID);
                        //xvar_dump($r);
                        //xvar_dump($subscrObj->LAST_ERROR);
                    }
                //}
            }
					
            //die();
			if ((isset($_REQUEST['SEARCH_COMPANY']) || strlen($_REQUEST["WORKNAME"])>0) && CModule::IncludeModule("iblock"))
			{					
				/*---------Проверить не превышен ли лимит участников---------*/
				
				/*
				$rsGroup = CGroup::GetByID(GROUP_MEMBERCONF, "Y");
				$arGroup = $rsGroup->Fetch();
				
				if ($arGroup["USERS"]>=$arGroup["DESCRIPTION"]) {//если после этого пользователя будет превышен лимит возможных участников
					
					$arEventFields = array();
					CEvent::Send("LIMIT_MEMBER", SITE_ID, $arEventFields);	
				}			
                */
                
                $arGroups = CIUser::GetUserGroups(0);
                foreach($arGroups as $arGroup)
                {
                    if($arGroup['MAX_COUNT'] <= 0)
                        continue;
                    if($arGroup['MAX_COUNT'] <= $arGroup["USERS"])
                    {
                        $arEventFields = array(
                            'LIMIT_COUNT' => $arGroup['MAX_COUNT'],
                            'CURRENT_COUNT' => $arGroup['USERS'],
                            'GROUP_NAME' => $arGroup['NAME'],
                        ); 
                        CEvent::Send("LIMIT_MEMBER", SITE_ID, $arEventFields);
                    }
                }
                				
				/*-----------------------------------------------------------*/
			}		
					
			// set user group
			//$sGroups = COption::GetOptionString("main", "new_user_registration_def_group", "");
			//CUser::SetUserGroup($ID, explode(",", $sGroups));

			// authorize user
			if ($arParams["AUTH"] == "Y" && $arResult["VALUES"]["ACTIVE"] == "Y")
			{
				if (!$arAuthResult = $USER->Login($arResult["VALUES"]["LOGIN"], $arResult["VALUES"]["PASSWORD"]))
				{
					$arResult["ERRORS"][] = $arAuthResult;
				}
			}
			else
			{
				$register_done = true;
			}

			$arResult['VALUES']["USER_ID"] = $ID;

			
            /**
            * На сайте успешно зарегистрирован новый пользователь
            *  добавим поле EMAIL_TO для шаблона
            *  если нет SPEC_MESSAGE - то создадим его чтобы не было в #SPEC_MESSAGE# в тексте сообщений
            */
            $arResult['VALUES']['EMAIL_TO']=$arResult['VALUES']['EMAIL'];
            if (!$arResult['VALUES']['SPEC_MESSAGE'])
                $arResult['VALUES']['SPEC_MESSAGE']='';
                
            CIUser::SendMessageToUserByCODE("NEW_USER", $arResult['VALUES'], true, false);
            
            /**
            * Сообщение про домен на почту при регистрации
            */
            $messageID=false;
            switch ($arParams["GROUP_ID"]) {
                case CIUser::GetVisitor():
                    $messageID=$GLOBALS["CONFIG"]->config["MESSAGE_TEMPLATE_REG_VISITOR_EMAIL"];
                break;
                case CIUser::GetMemberConf():
                    $messageID=$GLOBALS["CONFIG"]->config["MESSAGE_TEMPLATE_REG_CONF_EMAIL"];
                break;
                case CIUser::GetMemberShow():
                    $messageID=$GLOBALS["CONFIG"]->config["MESSAGE_TEMPLATE_REG_SHOW_EMAIL"];
                break;
            }
            
            
            if ($messageID){
                /**
                * отправляем письмо зарегистрировавшемуся
                */
                CIUser::SendMessageToUserByID($messageID, $arResult['VALUES'], true, false);
            }
            
            $messageID=false;
            switch ($arParams["GROUP_ID"]) {
                case CIUser::GetVisitor():
                    $messageID=$GLOBALS["CONFIG"]->config["MESSAGE_TEMPLATE_REG_VISITOR"];
                break;
                case CIUser::GetMemberConf():
                    $messageID=$GLOBALS["CONFIG"]->config["MESSAGE_TEMPLATE_REG_CONF"];
                break;
                case CIUser::GetMemberShow():
                    $messageID=$GLOBALS["CONFIG"]->config["MESSAGE_TEMPLATE_REG_SHOW"];
                break;
            }
            if ($messageID){
                /**
                * отправляем сообщение зарегистрировавшемуся
                */
                
                CIUser::SendMessageToUserByID($messageID, $arResult['VALUES'], false, true);    
            }

            
		}
		else
		{
			$arResult["ERRORS"][] = $user->LAST_ERROR;
		}

		if(count($arResult["ERRORS"]) <= 0)
		{
			if(COption::GetOptionString("main", "event_log_register", "N") === "Y")
				CEventLog::Log("SECURITY", "USER_REGISTER", "main", $ID);
		}
		else
		{
			if(COption::GetOptionString("main", "event_log_register_fail", "N") === "Y")
			{
				CEventLog::Log("SECURITY", "USER_REGISTER_FAIL", "main", $ID, implode("<br>", $arResult["ERRORS"]));
			}
		}

		$events = GetModuleEvents("main", "OnAfterUserRegister");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEvent($arEvent, &$arResult['VALUES']);
	}
}


// if user is registered - redirect him to backurl or to success_page; currently added users too
if ($USER->IsAuthorized() || $register_done)
{
	if ($arParams["USE_BACKURL"] == "Y" && strlen($_REQUEST["backurl"]) > 0)
	{
		LocalRedirect($_REQUEST["backurl"].(strpos($_REQUEST["backurl"],"?")===false?"?":"&")."from_url=".$APPLICATION->GetCurPage());
	}
	elseif (strlen($arParams["SUCCESS_PAGE"]))
	{
		LocalRedirect($arParams["SUCCESS_PAGE"].(strpos($arParams["SUCCESS_PAGE"],"?")===false?"?":"&")."from_url=".$APPLICATION->GetCurPage());
	}
	//else $APPLICATION->AuthForm(array());
	//die();
}
else
{
	$arResult["VALUES"] = htmlspecialcharsEx($arResult["VALUES"]);
}

// redefine required list - for better use in template
$arResult["REQUIRED_FIELDS_FLAGS"] = array();
foreach ($arResult["REQUIRED_FIELDS"] as $field)
{
	$arResult["REQUIRED_FIELDS_FLAGS"][$field] = "Y";
}

// check backurl existance
$arResult["BACKURL"] = htmlspecialchars($_REQUEST["backurl"]);

// get countries list
if (in_array("PERSONAL_COUNTRY", $arResult["SHOW_FIELDS"]) || in_array("WORK_COUNTRY", $arResult["SHOW_FIELDS"])) $arResult["COUNTRIES"] = GetCountryArray();
// get date format
if (in_array("PERSONAL_BIRTHDAY", $arResult["SHOW_FIELDS"])) $arResult["DATE_FORMAT"] = CLang::GetDateFormat("SHORT");

// ********************* User properties ***************************************************
$arResult["USER_PROPERTIES"] = array("SHOW" => "N");
$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
if (is_array($arUserFields) && count($arUserFields) > 0)
{
	if (!is_array($arParams["USER_PROPERTY"]))
		$arParams["USER_PROPERTY"] = array($arParams["USER_PROPERTY"]);
	foreach ($arUserFields as $FIELD_NAME => $arUserField)
	{
        
		if( in_array($FIELD_NAME, array("UF_SECTION", "UF_STATION", "UF_INTEREST", "UF_GRADE", "UF_COMPANY","UF_CATEGORY")) ) {
            
			if ($FIELD_NAME=="UF_SECTION" && in_array("UF_SECTION", $arParams["REQUIRED_FIELDSTOO"]))
				$arUserField["MANDATORY"]="Y";
			if ($FIELD_NAME=="UF_STATION" && in_array("UF_STATION", $arParams["REQUIRED_FIELDSTOO"])) {
				$arUserField["MANDATORY"]="Y";			
			}
			if ($FIELD_NAME=="UF_INTEREST" && in_array("UF_INTEREST", $arParams["REQUIRED_FIELDSTOO"]))
				$arUserField["MANDATORY"]="Y";
			if ($FIELD_NAME=="UF_GRADE" && in_array("UF_GRADE", $arParams["REQUIRED_FIELDSTOO"]))
				$arUserField["MANDATORY"]="Y";
			if ($FIELD_NAME=="UF_COMPANY" && in_array("UF_COMPANY", $arParams["REQUIRED_FIELDSTOO"]))
				$arUserField["MANDATORY"]="Y";
            if ($FIELD_NAME=="UF_CATEGORY")
                $arUserField["MANDATORY"]="Y";    
            
		}
		
		if (!in_array($FIELD_NAME, $arParams["USER_PROPERTY"]) && $arUserField["MANDATORY"] != "Y")
			continue;
        
		$arUserField["EDIT_FORM_LABEL"] = strLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
		$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
		$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
		$arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
		$arResult["SHOW_FIELDS"][] = $arUserField;
	}
    
}



if (!empty($arResult["USER_PROPERTIES"]["DATA"]))
{
	$arResult["USER_PROPERTIES"]["SHOW"] = "Y";
	$arResult["bVarsFromForm"] = (count($arResult['ERRORS']) <= 0) ? false : true;
}
// ******************** /User properties ***************************************************

// initialize captcha
if ($arResult["USE_CAPTCHA"] == "Y")
{
	$arResult["CAPTCHA_CODE"] = htmlspecialchars($APPLICATION->CaptchaGetCode());
}

// set title
if ($arParams["SET_TITLE"] == "Y") $APPLICATION->SetTitle(GetMessage("REGISTER_DEFAULT_TITLE"));


$this->IncludeComponentTemplate();
?>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 