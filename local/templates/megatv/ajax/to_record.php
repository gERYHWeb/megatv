<?
define('STOP_STATISTICS', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$GLOBALS['APPLICATION']->RestartBuffer();

global $USER;
if(!is_object($USER))
    $USER = new CUser;
    
$status = false;

$prog_time = intval($_REQUEST["broadcastID"]);
if($USER->IsAuthorized() && $prog_time>0)
{
    //Получим список всех каналов, на которые подписан пользователь
    $selectedChannels = array();
    $CSubscribeEx = new CSubscribeEx("CHANNEL");
    $arChannels = $CSubscribeEx->getList(array("UF_ACTIVE"=>"Y", "UF_USER"=>$USER->GetID()), array("UF_CHANNEL"));
    foreach($arChannels as $arChannel)
    {
        $selectedChannels[] = $arChannel["UF_CHANNEL"];
    }
    
    $USER_ID = $USER->GetID();
    $rsUser = CUser::GetByID($USER_ID);
    $arUser = $rsUser->Fetch();
    
    //Проверим, принадлежит ли запись этому каналу
    $arProgTime = CProgTime::getByID($prog_time, array("ID", "PROPERTY_CHANNEL", "PROPERTY_PROG", "PROPERTY_DATE_END", "PROPERTY_DATE_START"));
    $arRecords = CRecordEx::getList(array("UF_USER"=>$USER_ID, "UF_SCHEDULE"=>$prog_time), array("ID"));
    if(intval($arRecords[0]["ID"])>0 && count($arRecords)>0)
    {
        exit(json_encode(array("status"=>"error", "error"=> "Такая запись уже есть.")));
    }
    
    //Провеим, хватит ли пространства!
    $duration = strtotime($arProgTime["PROPERTY_DATE_END_VALUE"])-strtotime($arProgTime["PROPERTY_DATE_START_VALUE"]);
    $minutes = ceil($duration/60);
    $gb = $minutes*18.5/1024;
    $busy = floatval($arUser["UF_CAPACITY_BUSY"])+$gb;
    
    if($busy>=floatval($arUser["UF_CAPACITY"]))
    {
        exit(json_encode(array("status"=>"require-space", "error"=> "Не достаточно места на диске для записи")));
    }else{
        if(in_array($arProgTime["PROPERTY_CHANNEL_VALUE"], $selectedChannels))
        {
            $log_file = "/logs/sotal/sotal_".date("d_m_Y_H").".txt";
            CDev::log(array(
                "ACTION"  => "PUT_TO_RECORD",
                "DATA"    => array(
                    "PROG_ID"    => $prog_time,
                    "DATE"       => date("d.m.Y H:i:s")
                )
            ), false, $log_file);
                
            $Sotal = new CSotal($USER_ID);
            $Sotal->register();     //регистрируем пользователя, если нужно
            $Sotal->getSubscriberToken();   //получим ключ для использования в запросах
            $record_id = $Sotal->putRecord($prog_time); //ставим на запись программу
            
            if($record_id>0)
            {
                //сохраняем инфу о записе
                CRecordEx::create(array(
                    "UF_SOTAL_ID" => $record_id,
                    "UF_SCHEDULE" => $prog_time
                ));
                
                //Увеличиваем рейтинг программы
                CProg::addRating($arProgTime["PROPERTY_PROG_VALUE"], 1);
                  
                $cuser = new CUser;
                $cuser->Update($arUser["ID"], array("UF_CAPACITY_BUSY"=>$busy));
                
                /**
                 * Данные в статистику
                 */
                $arStat = CStatChannel::getList(array("UF_USER"=>$USER_ID, "UF_CHANNEL"=>$arProgTime["PROPERTY_CHANNEL_VALUE"]), array("ID", "UF_RATING"));
                if(intval($arStat[0]["ID"])>0)
                {
                    $rating = $arStat[0]["UF_RATING"]+1;
                    CStatChannel::update($arStat[0]["ID"], array("UF_RATING"=>$rating));
                }else{
                    CStatChannel::add(array(
                        "UF_USER" => $USER_ID,
                        "UF_CHANNEL" => $arProgTime["PROPERTY_CHANNEL_VALUE"]
                    ));
                }
                //--------------------------------------------------                
                
                $status = "success";
            }else{
                exit(json_encode(array("status"=>"error", "error"=> "Ошибка, программа на запись не поставлена. [sotal problem]")));
            } 
        }else{
            exit(json_encode(array("status"=>"error", "error"=> "Нельзя записать")));
        }
    }    
}

exit(json_encode(array("status"=>$status)));
?>