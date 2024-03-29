<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

global $USER;
/*
Authorization form (for prolog)
Params:
	REGISTER_URL => path to page with authorization script (component?)
	PROFILE_URL => path to page with profile component
*/



global $CHECK_USER_GROUP;
file_put_contents($_SERVER['DOCUMENT_ROOT'].'/print.htm', '<pre>'.var_export(array("result"=>$CHECK_USER_GROUP), true).'</pre>');

$arParamsToDelete = array(
	"login",
	"logout",
	"register",
	"forgot_password",
	"change_password",
	"confirm_registration",
	"confirm_code",
	"confirm_user_id",
	"logout_butt",
	"auth_service_id",
);

$currentUrl = $APPLICATION->GetCurPageParam("", $arParamsToDelete);

$arResult["BACKURL"] = $currentUrl;

//W//////////////////////////
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_REQUEST["send_group_choice"]) && !$USER->IsAuthorized() && !empty($_REQUEST["u_id"]) && isset($_REQUEST["group_choice"]))
{
	$GetUserInfo = CIClientSiteUser::GetInfo(Array('ID' => intval($_REQUEST["u_id"])));
	unset($GetUserInfo['arUser']['GROUP_ID']);
	$GetUserInfo['arUser']['GROUP_ID'] = Array(0 => intval($_REQUEST["group_choice"]));
	$GetUserInfo['arUser']['XML_ID'] = $GetUserInfo['arUser']['ID'];
	$NewUser = new CUser();
	
	global $IS_SYSTEM_ADD;
	$IS_SYSTEM_ADD = 'Y';
	if ($NewID = $NewUser->Add($GetUserInfo['arUser']))
		$USER->Authorize($NewID);

	LocalRedirect($arResult["BACKURL"]);
}
//W//////////////////////////

$arResult['ERROR'] = false;
$arResult['SHOW_ERRORS'] = (array_key_exists('SHOW_ERRORS', $arParams) && $arParams['SHOW_ERRORS'] == 'Y'? 'Y' : 'N');

if(!$USER->IsAuthorized())
{
	$arResult["FORM_TYPE"] = "login";

	$arResult["STORE_PASSWORD"] = COption::GetOptionString("main", "store_password", "Y") == "Y" ? "Y" : "N";
	$arResult["NEW_USER_REGISTRATION"] = COption::GetOptionString("main", "new_user_registration", "N") == "Y" ? "Y" : "N";

	if(defined("AUTH_404"))
		$arResult["AUTH_URL"] = htmlspecialcharsback(POST_FORM_ACTION_URI);
	else
		$arResult["AUTH_URL"] = $APPLICATION->GetCurPageParam("login=yes", array_merge($arParamsToDelete, array("logout_butt", "backurl")));

	$arParams["REGISTER_URL"] = ($arParams["REGISTER_URL"] <> ''? $arParams["REGISTER_URL"] : $currentUrl);

	$bRegisterURLque = strpos($arParams["REGISTER_URL"], "?") !== false;
	$url = $APPLICATION->GetCurPageParam("", array_merge($arParamsToDelete, array("backurl")));

	$arResult["AUTH_REGISTER_URL"] = $arParams["REGISTER_URL"].($bRegisterURLque? "&" : "?")."register=yes&backurl=".urlencode($url);
	$arResult["AUTH_FORGOT_PASSWORD_URL"] = $arParams["REGISTER_URL"].($bRegisterURLque? "&" : "?")."forgot_password=yes&backurl=".urlencode($url);

	$arRes = array();
	foreach($arResult as $key=>$value)
	{
		$arRes[$key] = htmlspecialchars($value);
		$arRes['~'.$key] = $value;
	}
	$arResult = $arRes;

	$arResult["POST"] = array();
	foreach($_POST as $vname=>$vvalue)
	{
		if($vname=="USER_LOGIN" || $vname=="backurl" || $vname == "auth_service_id" || is_array($vvalue)) 
			continue;
		$arResult["POST"][htmlspecialchars($vname)] = htmlspecialchars($vvalue);
	}

	if(defined("HTML_PAGES_FILE") && !defined("ERROR_404"))
		$arResult["~USER_LOGIN"] = "";
	else
		$arResult["~USER_LOGIN"] = $_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"];

	$arResult["USER_LOGIN"] = $arResult["LAST_LOGIN"] = htmlspecialchars($arResult["~USER_LOGIN"]);
	$arResult["~LAST_LOGIN"] = $arResult["~USER_LOGIN"];

	$arResult["AUTH_SERVICES"] = false;
	$arResult["CURRENT_SERVICE"] = false;
	$arResult["AUTH_SERVICES_HTML"] = '';
	if(!$USER->IsAuthorized() && $arResult["NEW_USER_REGISTRATION"] == "Y" && CModule::IncludeModule("socialservices"))
	{
		$oAuthManager = new CSocServAuthManager();
		$arServices = $oAuthManager->GetActiveAuthServices($arResult);
	
		if(!empty($arServices))
		{
			$arResult["AUTH_SERVICES"] = $arServices;
			if(isset($_REQUEST["auth_service_id"]) && $_REQUEST["auth_service_id"] <> '' && isset($arResult["AUTH_SERVICES"][$_REQUEST["auth_service_id"]]))
			{
				$arResult["CURRENT_SERVICE"] = $_REQUEST["auth_service_id"];
				if(isset($_REQUEST["auth_service_error"]) && $_REQUEST["auth_service_error"] <> '')
				{
					$arResult['ERROR_MESSAGE'] = $oAuthManager->GetError($arResult["CURRENT_SERVICE"], $_REQUEST["auth_service_error"]);
				}
				elseif(!$oAuthManager->Authorize($_REQUEST["auth_service_id"]))
				{
					$ex = $APPLICATION->GetException();
					if ($ex)
						$arResult['ERROR_MESSAGE'] = $ex->GetString();
				}
			}
		}
	}

	if($APPLICATION->arAuthResult)
		$arResult['ERROR_MESSAGE'] = $APPLICATION->arAuthResult;

	if($arResult['ERROR_MESSAGE'] <> '')
		$arResult['ERROR'] = true;

	if($APPLICATION->NeedCAPTHAForLogin($arResult["USER_LOGIN"]))
		$arResult["CAPTCHA_CODE"] = $APPLICATION->CaptchaGetCode();
	else
		$arResult["CAPTCHA_CODE"] = false;
}
else //if(!$USER->IsAuthorized())
{
	$arResult["FORM_TYPE"] = "logout";

	$arResult["AUTH_URL"] = $currentUrl;
	$arResult["PROFILE_URL"] = $arParams["PROFILE_URL"].(strpos($arParams["PROFILE_URL"], "?") !== false? "&" : "?")."backurl=".urlencode($currentUrl);

	$arRes = array();
	foreach($arResult as $key=>$value)
	{
		$arRes[$key] = htmlspecialchars($value);
		$arRes['~'.$key] = $value;
	}
	$arResult = $arRes;

	$arResult["USER_NAME"] = htmlspecialcharsEx($USER->GetFullName());
	$arResult["USER_LOGIN"] = htmlspecialcharsEx($USER->GetLogin());

	$arResult["GET"] = array();
	foreach($_GET as $vname=>$vvalue)
		if(!is_array($vvalue) && $vname!="backurl" && $vname != "login" && $vname != "auth_service_id")
			$arResult["GET"][htmlspecialchars($vname)] = htmlspecialchars($vvalue);
}

$this->IncludeComponentTemplate();
?>