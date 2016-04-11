<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

$arResult["PROGS"] = array();
$arTime = CTimeEx::getDatetime();
  
   
//активные каналы
$activeChannels = CChannel::getList(array("ACTIVE"=>"Y"), array("ID", "DETAIL_PAGE_URL", "PROPERTY_ICON"));
$ids = array();
$arResult["CHANNELS"] = array();
foreach($activeChannels as $activeChannel)
{
    $ids[] = $activeChannel["ID"];
    $arResult["CHANNELS"][$activeChannel["ID"]] = $activeChannel;
}

$arrFilter = array(
    "IBLOCK_ID" => PROG_IB,
    "ACTIVE" => "Y",
    "!PROPERTY_RECOMMEND" => false,
    "PROPERTY_CHANNEL" => CIBlockElement::SubQuery(
        "ID",
        array(
            "IBLOCK_ID" => CHANNEL_IB,
            "ACTIVE" => "Y",
        )
    ));

$arSelect = array("ID", "NAME", "PROPERTY_CHANNEL", "PROPERTY_SUB_TITLE", "PREVIEW_PICTURE", "PROPERTY_PICTURE_DOUBLE", "PROPERTY_PICTURE_HALF", "PROPERTY_CATEGORY");
$rsRes = CIBlockElement::GetList( array("PROPERTY_RATING" => "DESC"), $arrFilter, false, false, $arSelect );
while( $arItem = $rsRes->GetNext() )
{
    $arProgs[$arItem["ID"]] = $arItem;
}

if(count($arProgs)>0)
{
    $progIds = array();
    foreach($arProgs as $arProg)
    {
        $progIds[] = $arProg["ID"];
    }
    
    $key = 0;
    $exist = array();
    $arProgTimes = CProgTime::getList(array(
        ">=PROPERTY_DATE_START" => CTimeEx::datetimeForFilter(date("Y-m-d H:i:s")),
        "PROPERTY_PROG" => $progIds,
    ), array("ID", "CODE", "PROPERTY_DATE_START", "PROPERTY_DATE_END", "PROPERTY_PROG", "PROPERTY_CHANNEL"));
    foreach($arProgTimes as $arProgTime)
    {
        $arProg = $arProgs[$arProgTime["PROPERTY_PROG_VALUE"]];
        
        if(in_array($arProg["ID"], $exist))
            continue;
            
        $channel = $arProg["PROPERTY_CHANNEL_VALUE"];
        $arSchedule = $arProgTime;
        $arProg["SCHEDULE_ID"] = $arSchedule["ID"];
        $arProg["CHANNEL_ID"] = $channel;
        $arProg["CATEGORY"] = $arProg["PROPERTY_CATEGORY_VALUE"];
        $arProg["DATE_START"] = CTimeEx::dateOffset($arTime["OFFSET"], $arSchedule["PROPERTY_DATE_START_VALUE"]);
        $arProg["DATE_END"] = CTimeEx::dateOffset($arTime["OFFSET"], $arSchedule["PROPERTY_DATE_END_VALUE"]);
        $arProg["DETAIL_PAGE_URL"] = $arResult["CHANNELS"][$channel]["DETAIL_PAGE_URL"].$arSchedule["CODE"]."/";
        
        $arResult["PROGS"][] = $arProg;
        $exist[] = $arProg["ID"];
        
        
        if(!empty($arProg["CATEGORY"]))
            $arCats[] = $arProg["CATEGORY"];
        
        $key++;
        if($key>48) break;
    }
    
    //CDev::pre($arResult["PROGS"]);
    
    $arResult["PROGS"] = CScheduleTable::setIndex(array(
        "PROGS" => $arResult["PROGS"],
    ));
    
    //CDev::pre($arResult["PROGS"]);
}

$arCats = array_unique($arCats);

$arResult["CATEGORIES"] = array();
foreach($arCats as $category)
{
    $arParams = array("replace_space"=>"-", "replace_other"=>"-");
    $str = CDev::translit($category, "ru", $arParams);
    $arResult["CATEGORIES"][$category] = $str; 
}

$this->IncludeComponentTemplate();
?>