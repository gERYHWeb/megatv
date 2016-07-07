<?
define('STOP_STATISTICS', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$GLOBALS['APPLICATION']->RestartBuffer();

global $USER;
if(!is_object($USER))
    $USER = new CUser;

$result = array();
$channelID = intval($_REQUEST["channelID"]);
$status = htmlspecialcharsbx($_REQUEST["status"]);

//get subcribe channel list
$selectedChannels = array();
$result = \Hawkart\Megatv\SubscribeTable::getList(array(
    'filter' => array("=UF_USER_ID" => $USER->GetID()),
    'select' => array("UF_CHANNEL_ID", "ID")
));
while ($arSub = $result->fetch())
{
    $selectedChannels[$arSub["UF_CHANNEL_ID"]] = $arSub["ID"];
}

//check disable sub
$result = \Hawkart\Megatv\ChannelBaseTable::getList(array(
    'filter' => array("=UF_FORBID_REC" => 1, "=ID" => $channelID),
    'select' => array("ID")
));
if ($arChannel = $result->fetch())
{
    exit(json_encode(array("status"=>"disable", "error"=>"Нельзя подписаться на канал")));
}


//update subsribes
$CSubscribe = new \Hawkart\Megatv\CSubscribe("CHANNEL");
if(!isset($selectedChannels[$channelID]))
{
    $result = $CSubscribe->setUserSubscribe($channelID);
    
}else{
    if($status=="enable")
    {
        $active = 1;
    }else{
        $active = 0;
    }
    
    $subscribeID = $selectedChannels[$channelID];
    $result = $CSubscribe->updateUserSubscribe($subscribeID, array("UF_ACTIVE"=>$active));
}
 
if(!$result)
{
    $error = "Ошибка";
    if($status=="enable")
    {
        $status = "disable";
    }else{
        $status = "enable";
    }
} 
 
exit(json_encode(array("status"=>$status, "error"=>$selectedChannels)));
?>