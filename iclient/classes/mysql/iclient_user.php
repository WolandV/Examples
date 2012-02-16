<?
IncludeModuleLangFile(__FILE__);  
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iclient/classes/general/iclient_user.php");

/*
* CIClientSiteUser - инициируется с дочернего сайта
* CIClientSiteUserFromController - инициируется с контроллера
*/


class CIClientSiteUser extends CIAllClientSiteUser
{
	
}

class CIClientSiteUserFromController extends CIAllClientSiteUserFromController
{
	
}
?>