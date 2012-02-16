<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
$arFormFields = array(
	"NAME",
	"SECOND_NAME",
	"LAST_NAME",
	"PERSONAL_PROFESSION",
	"PERSONAL_WWW",
	"PERSONAL_ICQ",
	"PERSONAL_GENDER",
	"PERSONAL_BIRTHDAY",
	"PERSONAL_PHOTO",
	"PERSONAL_PHONE",
	"PERSONAL_FAX",
	"PERSONAL_MOBILE",
	"PERSONAL_PAGER",
	"PERSONAL_STREET",
	"PERSONAL_MAILBOX",
	"PERSONAL_CITY",
	"PERSONAL_STATE",
	"PERSONAL_ZIP",
	"PERSONAL_COUNTRY",
	"PERSONAL_NOTES",
	"WORK_COMPANY",
	"WORK_DEPARTMENT",
	"WORK_POSITION",
	"WORK_WWW",
	"WORK_PHONE",
	"WORK_FAX",
	"WORK_PAGER",
	"WORK_STREET",
	"WORK_MAILBOX",
	"WORK_CITY",
	"WORK_STATE",
	"WORK_ZIP",
	"WORK_COUNTRY",
	"WORK_PROFILE",
	"WORK_LOGO",
	"WORK_NOTES",
);

$arUserFields = array();
foreach ($arFormFields as $value) 
{
	$arUserFields[$value] = "[".$value."] ".GetMessage("REGISTER_FIELD_".$value);
}
$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
$userProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$userProp[$val["FIELD_NAME"]] = (strLen($val["EDIT_FORM_LABEL"]) > 0 ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"SEF_MODE" => array(),
	
		"SHOW_FIELDS" => array(
			"NAME" => GetMessage("REGISTER_SHOW_FIELDS"), 
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arUserFields,
			"PARENT" => "BASE",
		),

		"REQUIRED_FIELDS" => array(
			"NAME" => GetMessage("REGISTER_REQUIRED_FIELDS"), 
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arUserFields,
			"PARENT" => "BASE",
		),
		"REQUIRED_FIELDSTOO" => array(
			"NAME" => GetMessage("REGISTER_REQUIRED_FIELDS")." ...еще", 
			"TYPE" => "TEXT",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => "",
			"PARENT" => "BASE",
		),
		"GROUP_ID" => array(
			"NAME" => "В какую группу (ID)", 
			"TYPE" => "TEXT",
			"VALUES" => "",
			"PARENT" => "BASE",
		),

		"AUTH" => array(
			"NAME" => GetMessage("REGISTER_AUTOMATED_AUTH"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
        "USE_CAPTCHA" => array(
            "NAME" => "Использовать CAPTCHA",
            "TYPE" => "CHECKBOX",
            "DEFAULT" => COption::GetOptionString("main", "captcha_registration", "N") == "Y" ? "Y" : "N",
            "PARENT" => "ADDITIONAL_SETTINGS",
        ),
        
		"USE_BACKURL" => array(
			"NAME" => GetMessage("REGISTER_USE_BACKURL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		
		"SUCCESS_PAGE" => array(
			"NAME" => GetMessage("REGISTER_SUCCESS_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",			
		),
		
		"SET_TITLE" => array(),
		
		//"CACHE_TIME" => array("DEFAULT" => "3600"),
		
		"USER_PROPERTY"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("USER_PROPERTY"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),
		),
	),

);
?>