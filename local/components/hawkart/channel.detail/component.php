<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $USER, $APPLICATION;
$arResult = array();
$arParams = $arParams + array(
    "DATETIME" => \CTimeEx::getDatetime(),
    "LIST_URL" => $APPLICATION->GetCurDir(),
);

//get channel by code
$arFilter = array(
    "=UF_CHANNEL.UF_BASE.UF_ACTIVE" => 1,
    "=UF_CHANNEL.UF_BASE.UF_CODE" => $arParams["ELEMENT_CODE"],
    "=UF_CITY_ID" => $_SESSION["USER_GEO"]["ID"]
);
$arSelect = array(
    'ID', 'UF_CHANNEL_ID', 'UF_CHANNEL_BASE_ID' => 'UF_CHANNEL.UF_BASE.ID', 
    'UF_TITLE' => 'UF_CHANNEL.UF_BASE.UF_TITLE', 'UF_ICON' => 'UF_CHANNEL.UF_BASE.UF_ICON',
    'UF_CODE' => 'UF_CHANNEL.UF_BASE.UF_CODE', "UF_IS_NEWS" => 'UF_CHANNEL.UF_BASE.UF_IS_NEWS',
    'UF_DESC' => 'UF_CHANNEL.UF_BASE.UF_DESC', 'UF_H1' => 'UF_CHANNEL.UF_BASE.UF_H1'
);
$arSort = array("UF_CHANNEL.UF_BASE.UF_SORT" => "ASC");
$result = \Hawkart\Megatv\ChannelCityTable::getList(array(
    'filter' => $arFilter,
    'select' => $arSelect,
    'limit' => 1,
));
if ($arResult = $result->fetch())
{
    $arResult["ID"] = $arResult["UF_CHANNEL_ID"];
    $arResult["DETAIL_PAGE_URL"] = "/channels/".$arResult['UF_CODE']."/";
    $title = $arResult["UF_TITLE"]." -  телепрограмма на сегодня, программа телепередач канала ".$arResult["UF_H1"]." на МегаТВ";
    $APPLICATION->SetTitle($title);
    $APPLICATION->SetDirProperty("h1", $arResult["UF_H1"] ? $arResult["UF_H1"] : $arResult["UF_TITLE"]);
}

//get subscription list
$arSubscriptionChannels = $APPLICATION->GetPageProperty("ar_subs_channels");
$arResult["CHANNELS_SHOW"] = json_decode($arSubscriptionChannels, true);

//show error page SEO
if(intval($arResult["ID"])==0 || (!in_array($arResult['UF_CHANNEL_BASE_ID'], $arResult["CHANNELS_SHOW"]) && $USER->IsAuthorized()))
{
    CHTTP::SetStatus("404 Not Found");
    @define("ERROR_404", "Y");
}else{
    
    //FOR SEO
    $url_params = parse_url($_SERVER["REQUEST_URI"]);
    if(substr($url_params["path"], -1)!="/")
    {
        $url = $url_params["path"]."/";
        if(!empty($url_params["query"]))
            $url.= "?".$url_params["query"];
        
        LocalRedirect($url, false, "301 Moved permanently");
    }
}

$file = \YoutubeClient::getFilePathByChannel($arResult['UF_CHANNEL_BASE_ID']);
$arResult["PROGS"] = \YoutubeClient::dailyShow($file);

//CDev::pre($arResult["PROGS"]);

/*
//filter progs by date & use epg_file_id
$arDate = \CTimeEx::getDateFilter($arParams["DATETIME"]["SELECTED_DATE"]);
$dateStart = date("Y-m-d H:i:s", strtotime("-2 hours", strtotime($arDate["DATE_FROM"])));
$dateEnd = date("Y-m-d H:i:s", strtotime($arDate["DATE_TO"]));

$result = \Hawkart\Megatv\ScheduleTable::getList(array(
    'filter' => array(
        "=UF_CHANNEL_ID" => $arResult["ID"],
        ">=UF_DATE_START" => new \Bitrix\Main\Type\DateTime($dateStart, 'Y-m-d H:i:s'),
        "<UF_DATE_START" => new \Bitrix\Main\Type\DateTime($dateEnd, 'Y-m-d H:i:s'),
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
    $arSchedule["UF_DATE_START"] = $arSchedule["DATE_START"] = \CTimeEx::dateOffset($arSchedule['UF_DATE_START']->toString());
    $arSchedule["UF_DATE_END"] = $arSchedule["DATE_END"] = \CTimeEx::dateOffset($arSchedule['UF_DATE_END']->toString());
    $arSchedule["UF_DATE"] = $arSchedule["DATE"] = substr($arSchedule["DATE_START"], 0, 10);
    $arSchedule["PROG_ID"] = $arSchedule["UF_PROG_ID"];
    $arSchedule["DETAIL_PAGE_URL"] = $arResult["DETAIL_PAGE_URL"].$arSchedule["UF_ID"]."/?event=".$arSchedule["ID"];
    $arResult["PROGS"][] = $arSchedule;
}

$arSchedules = \Hawkart\Megatv\CScheduleView::setChannel(array(
    "PROGS" => $arResult["PROGS"],
    "NEWS" => $arResult["UF_IS_NEWS"],
));

$arResult["PROGS"] = $arSchedules;
*/

/**
 * Add data to statistics
 */
//\Hawkart\Megatv\CStat::channelAdd($arResult["ID"]);

$this->IncludeComponentTemplate();
?>