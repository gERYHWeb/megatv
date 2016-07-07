<?
define('STOP_STATISTICS', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$GLOBALS['APPLICATION']->RestartBuffer();
global $USER;

$arResult = array();
$query = htmlspecialcharsbx($_REQUEST["query"]);

$arFilter = array(
    "=UF_PROG.UF_ACTIVE" => 1,
    '%UF_PROG.UF_TITLE' => strtolower($query)
);

//User subscribe channel list
if($USER->IsAuthorized())
{ 
    $arFilter["=UF_CHANNEL_ID"] = \Hawkart\Megatv\ChannelTable::getActiveIdByCityByUser();
}else{
    $arFilter["=UF_CHANNEL_ID"] = \Hawkart\Megatv\ChannelTable::getActiveIdByCity();
}

$arExclude = array();

$result = \Hawkart\Megatv\ScheduleTable::getList(array(
    'filter' => $arFilter,
    'select' => array(
        "ID", "UF_CODE", "UF_DATE_START", "UF_TITLE" => "UF_PROG.UF_TITLE",
        "UF_SUB_TITLE" => "UF_PROG.UF_SUB_TITLE", "UF_IMG_PATH" => "UF_PROG.UF_IMG.UF_PATH",
        "UF_CHANNEL_CODE" => "UF_CHANNEL.UF_BASE.UF_CODE", "UF_ID" => "UF_PROG.UF_EPG_ID"
    ),
    'order' => array("UF_PROG.UF_RATING" => "DESC"),
));
while ($arSchedule = $result->fetch())
{
    if(in_array($arSchedule["UF_ID"], $arExclude))
    {
        continue;
    }else{
        $arExclude[] = $arSchedule["UF_ID"];
    }
    
    $arSchedule["UF_DATE_START"] = $arSchedule["DATE_START"] = $arSchedule['UF_DATE_START']->toString();
    
    $arJson = array();
    $arJson["date"] = substr($arSchedule["UF_DATE_START"], 11, 5)." | ".substr($arSchedule["UF_DATE_START"], 0, 10);
    $arJson["title"] = $arSchedule["UF_TITLE"];
    if($arSchedule["UF_IMG_PATH"])
    {
        $src = \Hawkart\Megatv\CFile::getCropedPath($arSchedule["UF_IMG_PATH"], array(600, 600));
        //$renderImage = CFile::ResizeImageGet($src, Array("width"=>60, "height"=>60));
        $arJson["thumbnail"] = $src;
    }
    else
    {
        $arJson["thumbnail"] = "null";
    }
        
    $arJson["tokens"] = array();
    $arJson["link"] = "/channels/".$arSchedule["UF_CHANNEL_CODE"]."/".$arSchedule["UF_ID"]."/?event=".$arSchedule["ID"];
    $arResult[] = $arJson;
}

exit(json_encode($arResult));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>