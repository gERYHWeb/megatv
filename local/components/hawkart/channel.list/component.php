<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

ini_set('max_execution_time', 30);
global $USER;

$arParams = $arParams + array(
    "DATETIME" => \CTimeEx::getDatetime(),
    "AJAX" => $_REQUEST["AJAX"],
    "LIST_URL" => $APPLICATION->GetCurDir(),
    "AJAX_TYPE" => $_REQUEST["AJAX_TYPE"]
);

//Get dates
$arResult["CONFIG_DATES"] = array();
$fisrt_date = date('d.m.Y', strtotime(\CTimeEx::getCurDate()));
for($i = 0; $i<\CTimeEx::getCalendarDays()+2; $i++)
{
    $date_confing = date('d.m.Y', strtotime("+".$i." day", strtotime($fisrt_date)));
    $arResult["CONFIG_DATES"][] = $date_confing;
}

//Get channel list
$arResult["ITEMS"] = \Hawkart\Megatv\ChannelTable::getActiveByCity();

$arChannels = array();
foreach($arResult["ITEMS"] as $arChannel)
{
    $arChannels[$arChannel["UF_CHANNEL_BASE_ID"]] = $arChannel;
    
}

$arResult["ITEMS"] = $arChannels;

/**
 * sort channels for user according statistics
 */
if($USER->IsAuthorized())
{
    $arItems = array();
    
    $arStatistic = \Hawkart\Megatv\CStat::getByUser();
    //sort channels by raiting
    uasort($arStatistic["CHANNELS"], function($a, $b){
        return strcmp($b, $a);
    });

    foreach($arStatistic["CHANNELS"] as $channel_id => $rating)
    {
        if(!empty($arResult["ITEMS"][$channel_id]))
        {
            $arItems[] = $arResult["ITEMS"][$channel_id];
            unset($arResult["ITEMS"][$channel_id]);
        }
    }
    
    if(count($arResult["ITEMS"])>0)
        $arItems = array_merge($arItems, $arResult["ITEMS"]);   

    $arResult["ITEMS"] = $arItems;
    unset($arItems);
}


/**
 * pagenavigation
 */
if(!isset($_REQUEST["PAGEN_1"]))
{
    $arResult["NAV_RESULT"]->NavPageNomer = 1;
}else{
    $arResult["NAV_RESULT"]->NavPageNomer = intval($_REQUEST["PAGEN_1"]);
}
$start = ($arResult["NAV_RESULT"]->NavPageNomer-1)*intval($arParams["NEWS_COUNT"])+1;
$end = $arResult["NAV_RESULT"]->NavPageNomer*intval($arParams["NEWS_COUNT"]);
$totalPages = $arResult["NAV_RESULT"]->NavPageCount = ceil(count($arResult["ITEMS"])/$arParams["NEWS_COUNT"]);


//get subscription list
$arSubscriptionChannels = $APPLICATION->GetPageProperty("ar_subs_channels");
$arResult["CHANNELS_SHOW"] = json_decode($arSubscriptionChannels, true);

/**
 * filter channels by navigation
 */
$k = 1;
$arChannelIds = array();
$arResult["CHANNELS"] = array();
foreach($arResult["ITEMS"] as $key=>$arItem)
{   
    if($k>=$start && $k<=$end)
    {
        $arItem["ICON"] = $arItem["UF_ICON"];
        $arChannelIds[] = $arItem["ID"];
        $arResult["CHANNELS"][$arItem["ID"]] = $arItem;
    }else{
        unset($arResult["ITEMS"][$key]);
    }
    $k++;
}
unset($arResult["ITEMS"]);


/**
 * Create list date & channels with schedules
 */
$arResult["DATES"] = array();
//Получим все программы текущих каналов за выбранный день
if(!isset($_REQUEST["date"]))
{
    $arParams["CURRENT_DATE"] = date("d.m.Y");
}else{
    $arParams["CURRENT_DATE"] = $_REQUEST["date"];
}

$arDate = \CTimeEx::getDateFilter($arParams["CURRENT_DATE"]);
$dateStart = date("Y-m-d H:i:s", strtotime($arDate["DATE_FROM"]));
$dateEnd = date("Y-m-d H:i:s", strtotime($arDate["DATE_TO"]));

$arFilter = array(
    "=UF_CHANNEL_ID" => $arChannelIds,
    ">=UF_DATE_START" => new \Bitrix\Main\Type\DateTime($dateStart, 'Y-m-d H:i:s'),
    "<UF_DATE_START" => new \Bitrix\Main\Type\DateTime($dateEnd, 'Y-m-d H:i:s'),
);
$arSelect = array(
    "ID", "UF_CODE", "UF_DATE_START", "UF_DATE_END", "UF_DATE", "UF_CHANNEL_ID", "UF_PROG_ID",
    "UF_TITLE" => "UF_PROG.UF_TITLE", "UF_SUB_TITLE" => "UF_PROG.UF_SUB_TITLE", "UF_IMG_PATH" => "UF_PROG.UF_IMG.UF_PATH",
    "UF_RATING" => "UF_PROG.UF_RATING", "UF_ID" => "UF_PROG.UF_EPG_ID"
);
$obCache = new \CPHPCache;
if( $obCache->InitCache(86400, serialize($arFilter).serialize($arSelect), "/index-schedules/"))
{
	$arResult["DATES"] = $obCache->GetVars();
}
elseif($obCache->StartDataCache())
{
	$result = \Hawkart\Megatv\ScheduleTable::getList(array(
        'filter' => $arFilter,
        'select' => $arSelect,
        'order' => array("UF_DATE_START" => "ASC")
    ));
    while ($arSchedule = $result->fetch())
    {
        $channel = $arSchedule["UF_CHANNEL_ID"];
        $arSchedule["UF_DATE_START"] = $arSchedule["DATE_START"] = \CTimeEx::dateOffset($arSchedule['UF_DATE_START']->toString());
        $arSchedule["UF_DATE_END"] = $arSchedule["DATE_END"] = \CTimeEx::dateOffset($arSchedule['UF_DATE_END']->toString());
        $arSchedule["UF_DATE"] = $arSchedule["DATE"] = substr($arSchedule["DATE_START"], 0, 10);
        
        $arSchedule["PROG_ID"] = $arSchedule["UF_PROG_ID"];
        $arSchedule["DETAIL_PAGE_URL"] = $arResult["CHANNELS"][$channel]["DETAIL_PAGE_URL"].$arSchedule["UF_ID"]."/?event=".$arSchedule["ID"];
        $arResult["DATES"][$arSchedule["UF_DATE"]][$channel][] = $arSchedule;
    }
	$obCache->EndDataCache($arResult["DATES"]); 
}
unset($arChannelIds);


/**
 * Create view through template
 */
$arResult["FIRST_DATE"] = false;
foreach($arResult["DATES"] as $date => $arChannels )
{
    if(!$arResult["FIRST_DATE"])
        $arResult["FIRST_DATE"] = $date;
    
    foreach($arChannels as $channel=>$arSchedules)
    {
        $arSchedules = \Hawkart\Megatv\CScheduleView::setIndex(array(
            "PROGS" => $arSchedules,
            "NEWS" => $arResult["CHANNELS"][$channel]["UF_IS_NEWS"],
        ));
        
        $arResult["DATES"][$date][$channel] = $arSchedules;
    }
    
    //add social schedule
    //$arResult["DATES"][$date]["YOUTUBE"] = \YoutubeClient::dailyShow();
    //$arResult["DATES"][$date]["VK"] = \VkClient::dailyShow();
}

/**
 * Add social channels
 */
/*$arResult["SOCIAL_CHANNELS"] = array(
    array(
        "ID" => "YOUTUBE",
        "NAME" => "Youtube",
        "UF_ICON" => "icon-youtube-channel"
    ),
    array(
        "ID" => "VK",
        "NAME" => "Vk",
        "UF_ICON" => "icon-vk-channel"
    )
);*/

$this->IncludeComponentTemplate();
?>