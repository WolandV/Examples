<?
IncludeModuleLangFile(__FILE__);
if(class_exists("iclient"))  return;

Class iclient extends CModule
{
	var $MODULE_ID = "iclient";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	
	function iclient()
	{
		$this->MODULE_NAME = GetMessage("ICLIENT_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("ICLIENT_MODULE_DESCRIPTION");
	}
	function InstallDB()
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		// Database tables creation
		if(!$DB->Query("SELECT 'x' FROM b_iclient", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iclient/install/db/".strtolower($DB->type)."/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}
		else
		{
			RegisterModule("iclient");
			CModule::IncludeModule("iclient");
			
			/* 
			отбой, работает и без этого :)
			прописываем в init.php - CModule::IncludeModule("iclient"); */
				/*$FileInitCont = $APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/init.php");
				$FileInitCont = str_replace("<?CModule::IncludeModule('iclient');?>\n", '', $FileInitCont);
				$FileInitCont = "<?CModule::IncludeModule('iclient');?>\n".$FileInitCont;
				RewriteFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/init.php", $FileInitCont);*/
			/* end */
			
			RegisterModuleDependences("main", "OnBeforeUserAdd", 'iclient', "CIClientUserAddHandlerClass", "OnBeforeUserAddHandler");
			RegisterModuleDependences("main", "OnAfterUserAdd", 'iclient', "CIClientUserAddHandlerClass", "OnAfterUserAddHandler");
			RegisterModuleDependences("main", "OnUserDelete", 'iclient', "CIClientUserDeleteHandlerClass", "OnUserDeleteHandler");
			RegisterModuleDependences("main", "OnBeforeUserUpdate", 'iclient', "CIClientUserUpdateHandlerClass", "OnBeforeUserUpdateHandler");
			RegisterModuleDependences("main", "OnAfterUserUpdate", 'iclient', "CIClientUserUpdateHandlerClass", "OnAfterUserUpdateHandler");
			RegisterModuleDependences("main", "OnBeforeUserLogin", 'iclient', "CIClientUserLoginHandlerClass", "OnBeforeUserLoginHandler");
			RegisterModuleDependences("main", "OnBeforeUserLogout", 'iclient', "CIClientUserLogoutHandlerClass", "OnBeforeUserLogoutHandler");
			RegisterModuleDependences("main", "OnEpilog", 'iclient', "CIClientOnEpilogHandlerClass", "OnEpilogHandler");
			RegisterModuleDependences("main", "OnPageStart", 'iclient', "CIClientOnPageStartClass", "OnPageStartHandler");
			RegisterModuleDependences("main", "OnAfterSetOption_controller_member_secret_id", 'iclient', "CIClientOnSetOptionClass", "OnSetOptionHandler");
			
			RegisterModuleDependences("iblock", "OnBeforeIBlockElementAdd", 'iclient', "CIClientOnIblockClass", "OnBeforeIBlockElementAddHandler");
			RegisterModuleDependences("iblock", "OnBeforeIBlockElementUpdate", 'iclient', "CIClientOnIblockClass", "OnBeforeIBlockElementUpdateHandler");
			RegisterModuleDependences("iblock", "OnBeforeIBlockElementDelete", 'iclient', "CIClientOnIblockClass", "OnBeforeIBlockElementDeleteHandler");
			
			RegisterModuleDependences("iblock", "OnBeforeIBlockSectionAdd", 'iclient', "CIClientOnIblockClass", "OnBeforeIBlockSectionAddUpdateHandler");
			RegisterModuleDependences("iblock", "OnBeforeIBlockSectionUpdate", 'iclient', "CIClientOnIblockClass", "OnBeforeIBlockSectionAddUpdateHandler");
			RegisterModuleDependences("iblock", "OnBeforeIBlockSectionDelete", 'iclient', "CIClientOnIblockClass", "OnBeforeIBlockSectionDeleteHandler");
			
			return true;
		}
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iclient/install/db/".strtolower($DB->type)."/uninstall.sql");
		}
		
		/*
		отбой, работает и без этого :)
		удаляем из init.php - CModule::IncludeModule("iclient"); */
			/*$FileInitCont = $APPLICATION->GetFileContent($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/init.php");
			$FileInitCont = str_replace("<?CModule::IncludeModule('iclient');?>\n", '', $FileInitCont);
			RewriteFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/init.php", $FileInitCont);*/
		/* end */

		UnRegisterModuleDependences("main", "OnBeforeUserAdd", 'iclient', "CIClientUserAddHandlerClass", "OnBeforeUserAddHandler");
		UnRegisterModuleDependences("main", "OnAfterUserAdd", 'iclient', "CIClientUserAddHandlerClass", "OnAfterUserAddHandler");
		UnRegisterModuleDependences("main", "OnUserDelete", 'iclient', "CIClientUserDeleteHandlerClass", "OnUserDeleteHandler");
		UnRegisterModuleDependences("main", "OnBeforeUserUpdate", 'iclient', "CIClientUserUpdateHandlerClass", "OnBeforeUserUpdateHandler");
		UnRegisterModuleDependences("main", "OnAfterUserUpdate", 'iclient', "CIClientUserUpdateHandlerClass", "OnAfterUserUpdateHandler");
		UnRegisterModuleDependences("main", "OnBeforeUserLogin", 'iclient', "CIClientUserLoginHandlerClass", "OnBeforeUserLoginHandler");
		UnRegisterModuleDependences("main", "OnBeforeUserLogout", 'iclient', "CIClientUserLogoutHandlerClass", "OnBeforeUserLogoutHandler");
		UnRegisterModuleDependences("main", "OnEpilog", 'iclient', "CIClientOnEpilogHandlerClass", "OnEpilogHandler");
		UnRegisterModuleDependences("main", "OnAfterSetOption_controller_member_secret_id", 'iclient', "CIClientOnSetOptionClass", "OnSetOptionHandler");
		
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockElementAdd", 'iclient', "CIClientOnIblockClass", "OnBeforeIBlockElementAddHandler");
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockElementUpdate", 'iclient', "CIClientOnIblockClass", "OnBeforeIBlockElementUpdateHandler");
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockElementDelete", 'iclient', "CIClientOnIblockClass", "OnBeforeIBlockElementDeleteHandler");
		
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockSectionAdd", 'iclient', "CIClientOnIblockClass", "OnBeforeIBlockSectionAddUpdateHandler");
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockSectionUpdate", 'iclient', "CIClientOnIblockClass", "OnBeforeIBlockSectionAddUpdateHandler");
		UnRegisterModuleDependences("iblock", "OnBeforeIBlockSectionDelete", 'iclient', "CIClientOnIblockClass", "OnBeforeIBlockSectionDeleteHandler");
		
		UnRegisterModule("iclient");

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		return true;
	}

	function InstallEvents()
	{	
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iclient/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
//		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iclient/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iclient/install/components/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", true, true);
		// Spread tc
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iclient/install/bitrix", $_SERVER["DOCUMENT_ROOT"]."/bitrix", True, True);
		
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iclient/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iclient/install/components/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components");
//		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iclient/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
//		DeleteDirFilesEx("/bitrix/themes/.default/icons/iclient/");


		$dir = $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/iclient/install/bitrix/';
        $ddir = $_SERVER["DOCUMENT_ROOT"].'/bitrix/';
        if (is_dir($dir)) 
        {
            if ($dh = opendir($dir)) 
            {
                while (($file = readdir($dh)) !== false) 
                {
                    if($file != '.' && $file !='..' && file_exists($dir.$file) && is_file($dir.$file) && file_exists($ddir.$file) && is_file($ddir.$file))
                    {
                        @unlink($ddir.$file);
                    }
                }
                closedir($dh);
            }
        }

		return true;
	}

	function DoInstall()
	{
		$this->InstallFiles();
		$this->InstallDB();
		$this->InstallEvents();
	}

	function DoUninstall()
	{
		$this->UnInstallDB();
		$this->UnInstallFiles();
		$this->UnInstallEvents();
	}
}
?>