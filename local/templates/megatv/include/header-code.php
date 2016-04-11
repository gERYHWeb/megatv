<?
global $USER;
session_start();
if(isset($_POST["city-id"]) && intval($_POST["city-id"])>0 && check_bitrix_sessid())
{
    CCityEx::setGeoCity(intval($_POST["city-id"]));
    header("Location: index.php");
}

if($USER->IsAuthorized())
{           
    $countRecorded = 0;
    $countInRec = 0;
    $arStatusRecording = array();   //записывается
    $arStatusRecorded = array();    //записана, можно просмотреть
    $arStatusViewed = array();    //просмотренна
    $arFilter = array(
        "UF_USER" => $USER->GetID(),
        //возможно нужно добавить фильтр по дате между -2д и +11д по дате окончания
    );
    $arRecords = CRecordEx::getList($arFilter, array("UF_URL", "UF_SCHEDULE", "UF_WATCHED", "ID"));
    foreach($arRecords as $arRecord)
    {
        $shedule_id = $arRecord["UF_SCHEDULE"];
        
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
    }
    $arRecordStatus = array(
        "RECORDING" => $arStatusRecording,
        "RECORDED"  => $arStatusRecorded,
        "VIEWED"    => $arStatusViewed
    );
    $APPLICATION->SetPageProperty("ar_record_status", json_encode($arRecordStatus));
    $APPLICATION->SetPageProperty("ar_record_in_rec", $countInRec);
    $APPLICATION->SetPageProperty("ar_record_recorded", $countRecorded);
    
    $selectedChannels = array();
    $CSubscribeEx = new CSubscribeEx("CHANNEL");
    $arChannels = $CSubscribeEx->getList(array("UF_ACTIVE"=>"Y", "UF_USER"=>$USER->GetID()), array("UF_CHANNEL"));
    foreach($arChannels as $arChannel)
    {
        $selectedChannels[] = $arChannel["UF_CHANNEL"];
    }
    $APPLICATION->SetPageProperty("ar_subs_channels", json_encode($selectedChannels));
}