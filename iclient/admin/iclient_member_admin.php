<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!CModule::IncludeModule("iclient"))
	die('The site-client module is not installed!');

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/iclient/prolog.php");
IncludeModuleLangFile(__FILE__);

$MOD_RIGHT = $APPLICATION->GetGroupRight("iclient");
if($MOD_RIGHT<="T") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

?>