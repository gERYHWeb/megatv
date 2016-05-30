<?
global $USER;
session_start();
if(isset($_POST["city-id"]) && intval($_POST["city-id"])>0 && check_bitrix_sessid())
{
    \Hawkart\Megatv\CityTable::setGeoCity(intval($_POST["city-id"]));
    header("Location: index.php");
}

if($USER->IsAuthorized())
{           
    $countRecorded = 0;
    $countInRec = 0;
    $count = 0;
    $arStatusRecording = array();   //записывается
    $arStatusRecorded = array();    //записана, можно просмотреть
    $arStatusViewed = array();    //просмотренна
    $result = \Hawkart\Megatv\RecordTable::getList(array(
        'filter' => array("=UF_USER_ID" => $USER->GetID()),
        'select' => array("ID", "UF_URL", "UF_SCHEDULE_ID", "UF_WATCHED", "UF_PROG_ID"),
    ));
    while ($arRecord = $result->fetch())
    {
        $shedule_id = $arRecord["UF_SCHEDULE_ID"];
        
        if($arRecord["UF_WATCHED"]==1)
        {
            $countRecorded++;
            $arStatusViewed[$shedule_id] = $arRecord;
        }
        else if(empty($arRecord["UF_URL"]))
        {
            $countInRec++;
            $arStatusRecording[$shedule_id] = $arRecord;
        }
        else if(!empty($arRecord["UF_URL"]))
        {
            $countRecorded++;
            $arStatusRecorded[$shedule_id] = $arRecord;
        }
        $count++;
    }
    $arRecordStatus = array(
        "RECORDING" => $arStatusRecording,
        "RECORDED"  => $arStatusRecorded,
        "VIEWED"    => $arStatusViewed
    );
    $APPLICATION->SetPageProperty("ar_record_status", json_encode($arRecordStatus));
    $APPLICATION->SetPageProperty("ar_record_in_rec", $countInRec);
    $APPLICATION->SetPageProperty("ar_record_recorded", $countRecorded);
    $APPLICATION->SetPageProperty("ar_record_total", $count);
    
    
    /**
     * User subscribe channel list. Add global property
     */
    $selectedChannels = array();
    $result = \Hawkart\Megatv\SubscribeTable::getList(array(
        'filter' => array("UF_ACTIVE"=>1, "=UF_USER_ID" => $USER->GetID(), ">UF_CHANNEL_ID" => 0),
        'select' => array("UF_CHANNEL_ID")
    ));
    while ($arSub = $result->fetch())
    {
        $selectedChannels[] = $arSub["UF_CHANNEL_ID"];
    }
    $APPLICATION->SetPageProperty("ar_subs_channels", json_encode($selectedChannels));
}