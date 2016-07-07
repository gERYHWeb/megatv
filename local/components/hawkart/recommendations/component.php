<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arResult["PROGS"] = array();
$arTime = \CTimeEx::getDatetime();

$prog_ids = array();

//get progs by rating
$arDate = \CTimeEx::getDateFilter($arTime["SELECTED_DATE"]);
$dateStart = date("Y-m-d H:i:s", strtotime($arDate["DATE_FROM"]));
$dateEnd = date("Y-m-d H:i:s", strtotime($arDate["DATE_TO"]));

$arFilter = array(
    "UF_PROG.UF_ACTIVE" => 1,
    "UF_CHANNEL_ID" => \Hawkart\Megatv\ChannelTable::getActiveIdByCityByUser(),
    ">=UF_DATE_START" => new \Bitrix\Main\Type\DateTime($dateStart, 'Y-m-d H:i:s'),
    "<UF_DATE_START" => new \Bitrix\Main\Type\DateTime($dateEnd, 'Y-m-d H:i:s'),
);
$arSelect = array(
    "ID", "UF_CODE", "UF_DATE_START", "UF_DATE_END", "UF_DATE", "UF_CHANNEL_ID", "UF_PROG_ID",
    "UF_TITLE" => "UF_PROG.UF_TITLE", "UF_SUB_TITLE" => "UF_PROG.UF_SUB_TITLE", "UF_IMG_PATH" => "UF_PROG.UF_IMG.UF_PATH",
    "UF_ICON" => "UF_CHANNEL.UF_BASE.UF_ICON", "UF_CHANNEL_CODE" => "UF_CHANNEL.UF_BASE.UF_CODE", "UF_ID" => "UF_PROG.UF_EPG_ID"
);
$arOrder = array("UF_PROG.UF_RATING" => "DESC");

$result = \Hawkart\Megatv\ScheduleTable::getList(array(
    'filter' => $arFilter,
    'select' => $arSelect,
    'order' => $arOrder,
    'limit' => 24
));
while ($arSchedule = $result->fetch())
{
    if(in_array($arSchedule["UF_PROG_ID"], $prog_ids)) continue;
    
    $prog_ids[] = $arSchedule["UF_PROG_ID"];    //for unrepeat
    
    $arSchedule["UF_DATE_START"] = $arSchedule["DATE_START"] = \CTimeEx::dateOffset($arSchedule['UF_DATE_START']->toString());
    $arSchedule["UF_DATE_END"] = $arSchedule["DATE_END"] = \CTimeEx::dateOffset($arSchedule['UF_DATE_END']->toString());
    $arSchedule["UF_DATE"] = $arSchedule["DATE"] = substr($arSchedule["DATE_START"], 0, 10);
    $arSchedule["DETAIL_PAGE_URL"] = "/channels/".$arSchedule["UF_CHANNEL_CODE"]."/".$arSchedule["UF_ID"]."/?event=".$arSchedule["ID"];
    $arResult["PROGS"][] = $arSchedule;
}

if($arParams["TEMPLATE"]=="MAIN_PAGE")
{
    $arResult["PROGS"] = \Hawkart\Megatv\CScheduleView::setRecommendIndex(array(
        "PROGS" => $arResult["PROGS"],
    ));
}

$this->IncludeComponentTemplate();
?>