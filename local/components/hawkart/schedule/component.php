<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $USER, $APPLICATION;
$arResult = array();
$arParams = $arParams + array(
    "DATETIME" => \CTimeEx::getDatetime(),
    "LIST_URL" => $APPLICATION->GetCurDir(),
    "BACK_URL" => $_SERVER['HTTP_REFERER']
);

if(empty($_REQUEST["event"]))
{
    $arFilter = array("=UF_PROG.UF_EPG_ID" => trim($arParams["ELEMENT_CODE"]));
}else{
    $arFilter = array("=ID" => $_REQUEST["event"]);
}

//get channel by code
$result = \Hawkart\Megatv\ScheduleTable::getList(array(
    //'filter' => array("=UF_CODE" => $arParams["ELEMENT_CODE"]),
    'filter' => $arFilter,
    'select' => array(
        "ID", "UF_DATE_START", "UF_DATE_END", "UF_DATE", "UF_CHANNEL_ID", "UF_PROG_ID",
        "UF_TITLE" => "UF_PROG.UF_TITLE", "UF_SUB_TITLE" => "UF_PROG.UF_SUB_TITLE", "UF_IMG_PATH" => "UF_PROG.UF_IMG.UF_PATH",
        "UF_RATING" => "UF_PROG.UF_RATING" , "UF_DESC" => "UF_PROG.UF_DESC", "UF_SUB_DESC" => "UF_PROG.UF_SUB_DESC",
        "UF_TOPIC" => "UF_PROG.UF_GANRE", "UF_YEAR_LIMIT" => "UF_PROG.UF_YEAR_LIMIT", "UF_COUNTRY" => "UF_PROG.UF_COUNTRY",
        "UF_YEAR" => "UF_PROG.UF_YEAR", "UF_DIRECTOR" => "UF_PROG.UF_DIRECTOR", "UF_PRESENTER" => "UF_PROG.UF_PRESENTER",
        "UF_ACTOR" => "UF_PROG.UF_ACTOR", "UF_ICON" => "UF_CHANNEL.UF_ICON", "UF_CATEGORY" => "UF_PROG.UF_CATEGORY"
    ),
    'limit' => 1
));
if ($arResult = $result->fetch())
{
    $arResult["UF_DATE_START"] = $arResult["DATE_START"] = $arResult['UF_DATE_START']->toString();
    $arResult["UF_DATE_END"] = $arResult["DATE_END"] = $arResult['UF_DATE_END']->toString();
    $arResult["UF_DATE"] = $arResult['UF_DATE']->toString();
    $arResult["PICTURE"]["SRC"] = \Hawkart\Megatv\CFile::getCropedPath($arResult["UF_IMG_PATH"], array(600, 600));
    
    $keywords[] = $arResult["UF_CATEGORY"];
    $keywords[] = $arResult["UF_TOPIC"];
    $APPLICATION->SetTitle($arResult["UF_TITLE"]);
    $APPLICATION->SetPageProperty("title", trim($arResult["UF_TITLE"]. " ".$arResult["UF_SUB_TITLE"]));
    $APPLICATION->SetPageProperty("keywords", implode(", ", $keywords));
    $APPLICATION->SetPageProperty("description", TruncateText($arResult["UF_DESC"], 256));
    
    $APPLICATION->SetDirProperty('og_image', $arResult["PICTURE"]["SRC"]);
    $APPLICATION->SetDirProperty('og_type', 'album');
}

//redirect if error
if(intval($arResult["ID"])==0)
{
    /*$explode = explode("/", $APPLICATION->GetCurDir());
    unset($explode[count($explode)-2]);
    $backurl = implode("/", $explode);*/
    if(!empty($arFilter["=UF_PROG.UF_EPG_ID"]))
    {
        LocalRedirect("/");
    }else{
        LocalRedirect($APPLICATION->GetCurDir());
    }
}

//get status schedule
$arResult["STATUS"] = \Hawkart\Megatv\CScheduleTemplate::status(array(
    "ID" => $arResult["ID"],
    "UF_CHANNEL_ID" => $arResult["UF_CHANNEL_ID"],
    "DATE_START" => $arResult["DATE_START"],
    "DATE_END" => $arResult["DATE_END"]
));

//CDev::pre($arResult);

$sec = strtotime($arResult["DATE_END"]) - strtotime($arResult["DATE_START"]);
$arResult["DURATION"] = \CTimeEx::secToStr($sec);

/**
 * Add data to statistics
 */
//\Hawkart\Megatv\CStat::channelAdd($arResult["UF_CHANNEL_ID"]);
\Hawkart\Megatv\CStat::addByShedule($arResult["ID"], "scheduleShow");

$this->IncludeComponentTemplate();
?>