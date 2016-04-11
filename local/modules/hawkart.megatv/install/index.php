<?php

includeModuleLangFile(__FILE__);
if (class_exists('hawkart_megatv')) 
    return;

class hawkart_megatv extends CModule 
{
    var $MODULE_ID = 'hawkart.megatv';
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_GROUP_RIGHTS = 'Y';

	public function __construct()
	{
		$arModuleVersion = array();

		$path = str_replace('\\', '/', __FILE__);
		$path = substr($path, 0, strlen($path) - strlen('/index.php'));
		include($path.'/version.php');

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}
        
        $this->PARTNER_NAME = GetMessage("HAWKART_MEGATV_PARTNER_NAME");
		$this->PARTNER_URI = 'http://www.hawkart.ru/';

		$this->MODULE_NAME = getMessage('HAWKART_MEGATV_MODULE_NAME');
		$this->MODULE_DESCRIPTION = getMessage('HAWKART_MEGATV_MODULE_DESCRIPTION');
	}
    
    function doInstall()
	{
		global $DB, $APPLICATION;

		$this->installFiles();
		$this->installDB();

		$GLOBALS['APPLICATION']->includeAdminFile(
			getMessage('HAWKART_MEGATV_INSTALL_TITLE'),
			$_SERVER['DOCUMENT_ROOT'].'/local/modules/hawkart.megatv/install/step1.php'
		);
	}
    
    function installDB()
	{
		global $DB, $APPLICATION;
        CModule::IncludeModule('iblock');
        CModule::IncludeModule('highloadblock');

		$this->errors = false;
		if (!$DB->query("SELECT 'x' FROM hw_channel", true))
		{
			$this->errors = $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'].'/local/modules/hawkart.megatv/install/db/'.strtolower($DB->type).'/install.sql');
        }
        
        /*$arTables = array("Image"=>"hw_image");
        foreach($arTables as $key=>$table_name)
        {
            $data = array(
                'NAME' => str_replace('_','',trim($key)),
                'TABLE_NAME' => trim($table_name)
            );
            $result = \Bitrix\Highloadblock\HighloadBlockTable::add($data);
            if ($result->isSuccess())
            {
                $ID = $result->getId();
                
                $oUserTypeEntity    = new CUserTypeEntity();
                $aUserFields    = array(
                    'ENTITY_ID'         => 'HLBLOCK_'.$ID,
                    'FIELD_NAME'        => 'UF_MYFIELD',
                    'USER_TYPE_ID'      => 'string',
                    'MULTIPLE'          => 'N',
                    'MANDATORY'         => 'N',
                    'SHOW_FILTER'       => 'I',
                    'EDIT_FORM_LABEL'   => array(
                        'ru'    => 'MYFIELD',
                        'en'    => 'MYFIELD',
                    )
                );
                
                $iUserFieldId   = $oUserTypeEntity->Add( $aUserFields );
            }else{
                $errors = $result->getErrorMessages();
            }
        }*/
        
		if ($this->errors !== false)
		{
			$APPLICATION->throwException(implode('', $this->errors));

			return false;
		}

		registerModule($this->MODULE_ID);

		return true;
	}

	function installEvents()
	{
		return true;
	}

	function installFiles()
	{
		/*copyDirFiles(
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/hawkart.megatv/install/admin',
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin',
			true, true
		);*/

		return true;
	}

	function doUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $step;

		$step = intval($step);
		if ($step < 2)
		{
			$APPLICATION->includeAdminFile(
				getMessage('HAWKART_MEGATV_UNINSTALL_TITLE'),
				$DOCUMENT_ROOT . '/local/modules/hawkart.megatv/install/unstep1.php'
			);
		}
		elseif ($step == 2)
		{
			$this->uninstallDB(array('savedata' => $_REQUEST['savedata']));
			$this->uninstallFiles();
			$APPLICATION->includeAdminFile(
				getMessage('HAWKART_MEGATV_UNINSTALL_TITLE'),
				$DOCUMENT_ROOT . '/local/modules/hawkart.megatv/install/unstep2.php'
			);
		}
	}

	function uninstallDB($arParams = array())
	{
		global $APPLICATION, $DB, $errors;

		$this->errors = false;

		if (!$arParams['savedata'])
		{
			$this->errors = $DB->runSQLBatch(
				$_SERVER['DOCUMENT_ROOT'] . '/local/modules/hawkart.megatv/install/db/'.strtolower($DB->type).'/unistall.sql'
			);
		}

		if ($this->errors !== false)
		{
			$APPLICATION->throwException(implode('', $this->errors));

			return false;
		}

		unregisterModule($this->MODULE_ID);

		return true;
	}

	function uninstallEvents()
	{
		return true;
	}

	function uninstallFiles()
	{
		/*deleteDirFiles(
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/hawkart.megatv/install/admin',
			$_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin'
		);*/

		return true;
	}

}