<?
define('STOP_STATISTICS', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$GLOBALS['APPLICATION']->RestartBuffer();

global $USER;
if(!is_object($USER))
    $USER = new \CUser;
    
$status = false;

$record_id = intval($_REQUEST["broadcastID"]);
if($USER->IsAuthorized() && $record_id>0 && $_REQUEST["delete"])
{
    $result = \Hawkart\Megatv\RecordTable::getById($record_id);
    $arRecord = $result->fetch();
    
    if($arRecord["UF_USER_ID"]==$USER->GetID())
    {
        $USER_ID = $USER->GetID();
        $rsUser = \CUser::GetByID($USER_ID);
        $arUser = $rsUser->Fetch();
        
        $Sotal = new \Hawkart\Megatv\CSotal($arUser["ID"]);
        $Sotal->getSubscriberToken();
        $arSchedules = $Sotal->getScheduleList();
        
        $is_deleted = false;
        foreach($arSchedules["schedule"] as $arSchedule)
        {
            if($arRecord["UF_SOTAL_ID"]==$arSchedule["id"])
            {
                $duration = $arSchedule["duration"];
                $minutes = ceil($duration/60);
                $gb = $minutes*(18.5/1024);
                
                $busy = floatval($arUser["UF_CAPACITY_BUSY"])-$gb; 
                $user = new \CUser;
                $user->Update($arUser["ID"], array("UF_CAPACITY_BUSY"=>$busy));
                
                $Sotal->cancelRecord($arRecord["UF_SOTAL_ID"]);
                
                \Hawkart\Megatv\RecordTable::delete($record_id);
                $is_deleted = true;
                
                break;
            }
        }
        
        if(!$is_deleted)
            \Hawkart\Megatv\RecordTable::delete($record_id);

        $status = "success";
    }
}

exit(json_encode(array("status"=>$status)));
?>