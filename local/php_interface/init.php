<?
//Константы
define("FULL_PATH_DOCUMENT_ROOT", "/home/d/daotel/MEGATV/public_html"); //изменить на сервере
define("LOG_FILENAME", "/logs/import.txt");
define("PROG_IB", 7);   //ид программ
define("BROADCAT_COLS", 24);
define("USER_SOCIAL_IB", 9);
define("PASSPORT_IB", 10);
define("SOCIAL_CONFIG_IB", 11);
define("RECORD_HL", 5);
define("BONUS_FOR_REGISTRATION", 10);   //бонус за регистрацию +20 ГБ пространства

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use \Bitrix\Highloadblock as HL;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Type\DateTime;

\Bitrix\Main\Loader::includeModule('olegpro.ipgeobase');
\CModule::IncludeModule("echogroup.smsru");
\CModule::IncludeModule("hawkart.megatv");
AddEventHandler("main", "OnBeforeEventSend",array("CEchogroupSmsru", "TerminateEvent"));

// Классы
$sClassesPath = '/local/php_interface/classes/';
CModule::AddAutoloadClasses(
	'',
	array(
        '\CCacheEx' => $sClassesPath.'CCacheEx.php',
        '\CXmlEx' => $sClassesPath.'CXmlEx.php',
		'\CDev' => $sClassesPath.'CDev.php',
        '\CSaleAccountEx' => $sClassesPath.'CSaleAccountEx.php',
        '\CTimeEx' => $sClassesPath.'CTimeEx.php',
        '\CSocialAuth' => $sClassesPath.'CSocialAuth.php',
        '\UploadHandler' => $sClassesPath.'UploadHandler.php',
        '\CUserEx' => $sClassesPath.'CUserEx.php',
        '\CCommentEx' => $sClassesPath.'CCommentEx.php',
        '\CNotifyEx' => $sClassesPath.'CNotifyEx.php',
        '\YoutubeClient' => $sClassesPath.'YoutubeClient.php',
        '\VkClient' => $sClassesPath.'VkClient.php',
	)
);

AddEventHandler('main', 'OnEpilog', '_Check404Error', 1);
AddEventHandler("main", "OnBeforeUserLogin", Array("CUserEx", "OnBeforeUserLogin"));
AddEventHandler("main", "OnBeforeUserRegister", Array("CUserEx", "OnBeforeUserRegister"));
AddEventHandler("main", "OnAfterUserAdd", Array("CUserEx", "OnAfterUserUpdateHandler"));
AddEventHandler("main", "OnBeforeUserSendPassword", Array("CUserEx", "OnBeforeUserSendPasswordHandler"));
AddEventHandler("main", "OnBeforeUserDelete", Array("CUserEx", "OnBeforeUserDeleteHandler"));

// обработка опенграфовских мета-тегов
AddEventHandler('main', 'OnEpilog', array('CMyEpilogHooks', 'OpenGraph'));
class CMyEpilogHooks
{
    function OpenGraph()
    {
        GLOBAL $APPLICATION;
        foreach (array('og_title', 'og_image', 'og_url', 'og_site_name', 'og_type', 'og_description') as $prop_name)
        {
            $value = $APPLICATION->GetDirProperty($prop_name);
            
            if ($prop_name == 'og_url' && empty($value))
                $value = $APPLICATION->GetCurPage(false);
            
            if (in_array($prop_name, array('og_image', 'og_url')) && !empty($value))
                $value = "http://".$_SERVER["SERVER_NAME"].$value;
            
            $prop_code = str_replace('og_', 'og:', $prop_name);
            if (!empty($value))
                $APPLICATION->AddHeadString("<meta property=\"$prop_code\" content=\"$value\" />");
        }
    }
}

function _Check404Error()
{
   if (defined('ERROR_404') && ERROR_404=='Y' && !defined('ADMIN_SECTION'))
   {
       GLOBAL $APPLICATION;
       $APPLICATION->RestartBuffer();
       include $_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/header.php';
       require ($_SERVER['DOCUMENT_ROOT'].'/404.php');
       include $_SERVER['DOCUMENT_ROOT'].SITE_TEMPLATE_PATH.'/footer.php';
   }
}