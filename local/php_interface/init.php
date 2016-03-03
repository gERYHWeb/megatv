<?
//Константы
define("FULL_PATH_DOCUMENT_ROOT", "Z:/home/megatv.ru/www"); //изменить на сервере
define("LOG_FILENAME", "/logs/import.txt");
define("CHANNEL_IB", 6);    //ид каналов
define("PROG_IB", 7);   //ид программ
define("PROG_TIME_IB", 8);   //ид показа программ
define("CITY_IB", 5);
define("SERVICE_IB", 13);
define("BROADCAT_COLS", 24);
define("USER_SOCIAL_IB", 9);
define("PASSPORT_IB", 10);
define("SOCIAL_CONFIG_IB", 11);
define("SUBSCRIBE_HL", 4);
define("RECORD_HL", 5);
define("BONUS_FOR_REGISTRATION", 20);   //бонус за регистрацию +20 ГБ пространства

require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use \Bitrix\Highloadblock as HL;
use \Bitrix\Main\Entity;
use \Bitrix\Main\Type\DateTime;

\Bitrix\Main\Loader::includeModule('olegpro.ipgeobase');

CModule::IncludeModule("echogroup.smsru");
AddEventHandler("main", "OnBeforeEventSend",array("CEchogroupSmsru","TerminateEvent"));

// Классы
$sClassesPath = '/local/php_interface/classes/';
CModule::AddAutoloadClasses(
	'',
	array(
        '\CCacheEx' => $sClassesPath.'CCacheEx.php',
        '\CXmlEx' => $sClassesPath.'CXmlEx.php',
		'\CDev' => $sClassesPath.'CDev.php',
        '\CCityEx' => $sClassesPath.'CCity.php',
        '\CSaleAccountEx' => $sClassesPath.'CSaleAccountEx.php',
        '\CChannel' => $sClassesPath.'CChannel.php',
        '\CProg' => $sClassesPath.'CProg.php',
        '\CProgTime' => $sClassesPath.'CProgTime.php',
        '\CEpg' => $sClassesPath.'CEpg.php',
        '\CTimeEx' => $sClassesPath.'CTimeEx.php',
        '\CScheduleTable' => $sClassesPath.'CScheduleTable.php',
        '\CSocialAuth' => $sClassesPath.'CSocialAuth.php',
        '\CSubscribeEx' => $sClassesPath.'CSubscribeEx.php',
        '\CSotal' => $sClassesPath.'CSotal.php',
        '\UploadHandler' => $sClassesPath.'UploadHandler.php',
        '\CRecordEx' => $sClassesPath.'CRecordEx.php',
        '\CServiceEx' => $sClassesPath.'CServiceEx.php',
        '\CUserEx' => $sClassesPath.'CUserEx.php',
        '\CCommentEx' => $sClassesPath.'CCommentEx.php',
        '\CStatChannel' => $sClassesPath.'CStatChannel.php'
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
       include $_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/'.SITE_TEMPLATE_ID.'/header.php';
       require ($_SERVER['DOCUMENT_ROOT'].'/404.php');
       include $_SERVER['DOCUMENT_ROOT'].'/bitrix/templates/'.SITE_TEMPLATE_ID.'/footer.php';
   }
}

AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", Array("MyCIBlockElement", "OnBeforeIBlockElementUpdateHandler"));
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", Array("MyCIBlockElement", "OnAfterIBlockElementUpdateHandler"));

class MyCIBlockElement
{
    function OnBeforeIBlockElementUpdateHandler(&$arFields)
    {
        //Если включили бесплатный канал, активируем для всех пользователей подписку.
        if($arFields["IBLOCK_ID"]==CHANNEL_IB)
        {
            CModule::IncludeModule("iblock");
            $res = CIBlockElement::GetByID($arFields["ID"]);
            $arChannel = $res->GetNext();

            $price = intval($arFields["PROPERTY_VALUES"][41]["n0"]["VALUE"]);

            if($arFields["ACTIVE"]=="Y" && $arChannel["ACTIVE"]=="N" && $price==0)
            {
                //Найдем пользователей, для кого эта подписка была включена
                $userIds = array();
                $CSubscribeEx = new CSubscribeEx("CHANNEL");
                $arSubscriptions = $CSubscribeEx->getList(array("UF_CHANNEL"=>$arFields["ID"]), array("ID", "UF_USER"));
                if(count($arSubscriptions)>0)
                {
                    foreach($arSubscriptions as $arSub)
                    {
                        $userIds[$arSub["UF_USER"]] = $arSub["ID"];
                    }
                }

                $dbUsers = CUser::GetList(($by="EMAIL"), ($order="desc"), Array("ACTIVE" =>"Y"));
                while($arUser = $dbUsers->Fetch())
                {
                    if(!array_key_exists($arUser["ID"], $userIds))
                    {
                        $CSubscribeEx->setUserSubscribe($arFields["ID"], $arUser["ID"]);
                    }else{
                        $sub_id = $userIds[$arUser["ID"]];
                        $CSubscribeEx->updateUserSubscribe($sub_id, array("UF_ACTIVE"=>"Y"));
                    }
                }

                //Обновим кэш каналов
                CChannel::updateCache();
            }
        }
    }

    function OnAfterIBlockElementUpdateHandler(&$arFields)
    {
        //Обновление кэша
        if($arFields["IBLOCK_ID"]==CHANNEL_IB)
        {
            CChannel::updateCache();
        }
        if($arFields["IBLOCK_ID"]==PROG_IB)
        {
            CProg::updateCache();
        }
        if($arFields["IBLOCK_ID"]==PROG_TIME_IB)
        {
            CProgTime::updateCache();
        }
    }
}