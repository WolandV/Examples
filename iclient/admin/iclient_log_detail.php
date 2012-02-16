<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule("iclient");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/iclient/prolog.php");
IncludeModuleLangFile(__FILE__);

$MOD_RIGHT = $APPLICATION->GetGroupRight("iclient");
if($MOD_RIGHT<"V") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

?>