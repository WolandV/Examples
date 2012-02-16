<?
global $DBType;
IncludeModuleLangFile(__FILE__);

CModule::AddAutoloadClasses(
	"iclient",
	array(
		"CIClientSiteUser"							=> "classes/".$DBType."/iclient_user.php",
		"CIClientSiteUserFromController"			=> "classes/".$DBType."/iclient_user.php",
		"CIClientSiteCompany"						=> "classes/".$DBType."/iclient_company.php",
		"CIClientSiteCompanyFromController"			=> "classes/".$DBType."/iclient_company.php",
		"CIClientSiteInterestArea"					=> "classes/".$DBType."/iclient_interestarea.php",
		"CIClientSiteInterestAreaFromController"	=> "classes/".$DBType."/iclient_interestarea.php",
		"CIControllerClientRequestTo"				=> "classes/general/iclient_transport.php",
		"CIClientTools"								=> "classes/general/iclient_tools.php",
		
		// events user
		"CIClientUserAddHandlerClass"		=> "classes/general/iclient_events.php",
		"CIClientUserUpdateHandlerClass"	=> "classes/general/iclient_events.php",
		"CIClientUserLoginHandlerClass"		=> "classes/general/iclient_events.php",
		"CIClientUserLogoutHandlerClass"	=> "classes/general/iclient_events.php",
		
		// secret_id set event
		"CIClientOnSetOptionClass"			=> "classes/general/iclient_events.php",
		
		// events content
		"CIClientOnEpilogHandlerClass"		=> "classes/general/iclient_events.php",
		
		// events pageStart
		"CIClientOnPageStartClass"			=> "classes/general/iclient_events.php",
		
		// events iblock
		"CIClientOnIblockClass"				=> "classes/general/iclient_events.php",
	)
);


/* define ID инфоблоков IBLOCK_ID_<код_инфоблока> если такие еще не заданы */
	$obCache = new CPHPCache;
	$CacheTime = 360000;
	$CACHE_ID = 'iblocks';
	$CACHE_DIR = 'iblocks';

	if($obCache->StartDataCache($CacheTime, $CACHE_ID, $CACHE_DIR))
	{
		if(!CModule::IncludeModule("iblock")) return false;
		$res = CIBlock::GetList(Array(), Array('ACTIVE'=>'Y'));
		$arIB = array();
		while($ar_res = $res->Fetch())
		{
			$arIB[] = $ar_res;
		}
		$obCache->EndDataCache($arIB);
	}
	else
	{
		$arIB = $obCache->GetVars();
	}
	
	foreach($arIB as $arIBlock)
	{
		if(!defined('IBLOCK_ID_'.$arIBlock['CODE']))
			define('IBLOCK_ID_'.$arIBlock['CODE'], $arIBlock['ID']);
	}
/* end */


$DB_test = CDatabase::GetModuleConnection("iclient", true);
if(!is_object($DB_test))
	return false;
if(!CModule::IncludeModule("iclient") || !CModule::IncludeModule("iblock"))
    return false;
?>