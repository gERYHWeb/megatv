<?
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__) . '/../');
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
set_time_limit(0);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: text/html; charset=utf-8');
ini_set('mbstring.func_overload', '2');
ini_set('mbstring.internal_encoding', 'UTF-8');

global $USER, $APPLICATION;
if (!is_object($USER))
    $USER=new \CUser;

//Получим список записей для каждого пользователей, у которых нет еще ссылки на видео
$arUserRecords = array();
$arRecords = array();
$dt = new Bitrix\Main\Type\DateTime(date('Y-m-d H:i:s', time()), 'Y-m-d H:i:s');
$result = \Hawkart\Megatv\RecordTable::getList(array(
    'filter' => array(
        "UF_URL" => false,
        "<=UF_DATE_END" => $dt
    ),
    'select' => array(
        "ID", "UF_TITLE" => "UF_PROG.UF_TITLE", "UF_SUB_TITLE" => "UF_PROG.UF_SUB_TITLE",
        "UF_IMG_PATH" => "UF_PROG.UF_IMG.UF_PATH", "UF_SOTAL_ID", "UF_USER_ID"
    )
));
while ($arRecord = $result->fetch())
{
    $arRecord["NAME"] = \Hawkart\Megatv\ProgTable::getName($arRecord);
    $arRecord["PICTURE"]["SRC"] = \Hawkart\Megatv\CFile::getCropedPath($arRecord["UF_IMG_PATH"], array(300, 300), true);

    $arUserRecords[$arRecord["UF_USER_ID"]][] = $arRecord["UF_SOTAL_ID"];
    $arRecords[$arRecord["UF_SOTAL_ID"]] = $arRecord;
}

$filter = Array("ACTIVE" =>"Y", "!UF_SOTAL_LOGIN" => false);
$rsUsers = \CUser::GetList(($by="LAST_NAME"), ($order="asc"), $filter, array("SELECT"=>array("UF_CAPACITY_BUSY", "UF_CAPACITY"), "FIELDS" => array("ID", "EMAIL", "NAME")) );
while($arUser = $rsUsers->GetNext())
{
    //Если у пользователя есть программы, ожидающие записи
    if(isset($arUserRecords[$arUser["ID"]]) && !empty($arUserRecords[$arUser["ID"]]))
    {
        $ids = $arUserRecords[$arUser["ID"]];
        
        $Sotal = new \Hawkart\Megatv\CSotal($arUser["ID"]);
        $Sotal->getSubscriberToken();
        $arSchedules = $Sotal->getScheduleList();
        
        foreach($arSchedules["schedule"] as $arSchedule)
        {
            $record_id = $arSchedule["id"];
            $status = $arSchedule["state"];
            
            if(intval($status)==3 && in_array($record_id, $ids))
            {
                $arStream = $Sotal->getStreamUrl($record_id);
                $url = $arStream["url"];
                
                if(!empty($url))
                {
                    $arRecord = $arRecords[$record_id];

                    //Если достаточно пространства
                    if(intval($arUser["UF_CAPACITY_BUSY"])<intval($arUser["UF_CAPACITY"]))
                    {
                        \CNotifyEx::afterRecord(array(
                            "USER_ID" => $arUser["ID"],
                            "USER_NAME" => $arUser["NAME"],
                            "USER_EMAIL" => $arUser["EMAIL"],
                            "RECORD_ID" => $arRecord["ID"],
                            "RECORD_NAME" => $arRecord["NAME"],
                            "PICTURE" => "http://megatv.su".$arRecord["PICTURE"]["SRC"],
                            "URL" => "http://megatv.su/personal/records/?record_id=".$arRecord["ID"]."&play=yes"
                        ));
                        
                        \Hawkart\Megatv\RecordTable::update($arRecord["ID"], array("UF_URL" => $url, "UF_AFTER_NOTIFY" => 1));
                    }
                    
                }        
            }
        }
        
        unset($Sotal);
    }
}

unset($arUserRecords);
unset($arRecords);

echo "loaded";

die();