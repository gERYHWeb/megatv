<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $USER, $APPLICATION;
$arResult = array();
$arParams = $arParams + array(
    "DATETIME" => \CTimeEx::getDatetime(),
    "LIST_URL" => $APPLICATION->GetCurDir(),
);

//get channel by code
$result = \Hawkart\Megatv\ChannelTable::getList(array(
    'filter' => array("=UF_CODE" => $arParams["ELEMENT_CODE"], '=UF_ACTIVE'=> 1),
    'select' => array('ID', 'UF_TITLE', 'UF_ICON', 'UF_CODE', "UF_IS_NEWS")
));
if ($arResult = $result->fetch())
{
    $arResult["DETAIL_PAGE_URL"] = "/channels/".$arResult['UF_CODE']."/";
    $APPLICATION->SetTitle($arResult["UF_TITLE"]);
}

//get subscription list
$arSubscriptionChannels = $APPLICATION->GetPageProperty("ar_subs_channels");
$arResult["CHANNELS_SHOW"] = json_decode($arSubscriptionChannels, true);

//show error page
if(intval($arResult["ID"])==0 || (!in_array($arResult['ID'], $arResult["CHANNELS_SHOW"]) && $USER->IsAuthorized()))
{
    CHTTP::SetStatus("404 Not Found");
    @define("ERROR_404", "Y");
}

//filter progs by date & use epg_file_id
$dateStart = date("Y-m-d H:i:s", strtotime("-2 hours", strtotime($arParams["DATETIME"]["SERVER_DATETIME"])));
$result = \Hawkart\Megatv\ScheduleTable::getList(array(
    'filter' => array(
        "=UF_CHANNEL_ID" => $arResult["ID"],
        "=UF_EPG_FILE_ID" => $_SESSION["USER_GEO"]["UF_EPG_FILE_ID"],
        ">=UF_DATE_START" => new \Bitrix\Main\Type\DateTime($dateStart, 'Y-m-d H:i:s'),
    ),
    'select' => array(
        "ID", "UF_CODE", "UF_DATE_START", "UF_DATE_END", "UF_DATE", "UF_CHANNEL_ID", "UF_PROG_ID",
        "UF_TITLE" => "UF_PROG.UF_TITLE", "UF_SUB_TITLE" => "UF_PROG.UF_SUB_TITLE", "UF_IMG_PATH" => "UF_PROG.UF_IMG.UF_PATH",
        "UF_RATING" => "UF_PROG.UF_RATING", "UF_HD" => "UF_PROG.UF_HD", "UF_DESC" => "UF_PROG.UF_DESC",
        "UF_ID" => "UF_PROG.UF_EPG_ID"
    ),
    'limit' => 12
));
while ($arSchedule = $result->fetch())
{
    $arSchedule["UF_DATE_START"] = $arSchedule["DATE_START"] = $arSchedule['UF_DATE_START']->toString();
    $arSchedule["UF_DATE_END"] = $arSchedule["DATE_END"] = $arSchedule['UF_DATE_END']->toString();
    $arSchedule["UF_DATE"] = $arSchedule["DATE"] = $arSchedule['UF_DATE']->toString();
    $arSchedule["PROG_ID"] = $arSchedule["UF_PROG_ID"];
    $arSchedule["DETAIL_PAGE_URL"] = $arResult["DETAIL_PAGE_URL"].$arSchedule["UF_ID"]."/?event=".$arSchedule["ID"];
    $arResult["PROGS"][] = $arSchedule;
}

$arSchedules = \Hawkart\Megatv\CScheduleView::setChannel(array(
    "PROGS" => $arResult["PROGS"],
    "NEWS" => $arResult["UF_IS_NEWS"],
));

$arResult["PROGS"] = $arSchedules;

/**
 * Add data to statistics
 */
//\Hawkart\Megatv\CStat::channelAdd($arResult["ID"]);

$this->IncludeComponentTemplate();
?>