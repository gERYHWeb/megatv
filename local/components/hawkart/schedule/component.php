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
    $arFilter = array("=UF_PROG.UF_EPG_ID" => $arParams["ELEMENT_CODE"]);
}else{
    $arFilter = array("=ID" => $_REQUEST["event"]);
}
 
//get channel by code
$result = \Hawkart\Megatv\ScheduleTable::getList(array(
    'filter' => $arFilter,
    'select' => array(
        "ID", "UF_DATE_START", "UF_DATE_END", "UF_DATE", "UF_CHANNEL_ID", "UF_PROG_ID",
        "UF_TITLE" => "UF_PROG.UF_TITLE", "UF_SUB_TITLE" => "UF_PROG.UF_SUB_TITLE", "UF_IMG_PATH" => "UF_PROG.UF_IMG.UF_PATH",
        "UF_RATING" => "UF_PROG.UF_RATING" , "UF_DESC" => "UF_PROG.UF_DESC", "UF_SUB_DESC" => "UF_PROG.UF_SUB_DESC",
        "UF_TOPIC" => "UF_PROG.UF_GANRE", "UF_YEAR_LIMIT" => "UF_PROG.UF_YEAR_LIMIT", "UF_COUNTRY" => "UF_PROG.UF_COUNTRY",
        "UF_YEAR" => "UF_PROG.UF_YEAR", "UF_DIRECTOR" => "UF_PROG.UF_DIRECTOR", "UF_PRESENTER" => "UF_PROG.UF_PRESENTER",
        "UF_ACTOR" => "UF_PROG.UF_ACTOR", "UF_ICON" => "UF_CHANNEL.UF_BASE.UF_ICON", "UF_CATEGORY" => "UF_PROG.UF_CATEGORY"
    ),
    'limit' => 1
));
if ($arResult = $result->fetch())
{
    $arResult["UF_DATE_START"] = $arResult["DATE_START"] = \CTimeEx::dateOffset($arResult['UF_DATE_START']->toString());
    $arResult["UF_DATE_END"] = $arResult["DATE_END"] = \CTimeEx::dateOffset($arResult['UF_DATE_END']->toString());
    $arResult["UF_DATE"] = $arResult["DATE"] = substr($arResult["DATE_START"], 0, 10);
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
    if(!empty($arFilter["=UF_PROG.UF_EPG_ID"]))
    {
        CHTTP::SetStatus("404 Not Found");
        @define("ERROR_404", "Y");
    }else{
        LocalRedirect($APPLICATION->GetCurDir(), false, "301 Moved Permanently");
    }
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