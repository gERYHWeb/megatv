<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

ini_set('max_execution_time', 10);

global $USER;
$arResult["PROGS"] = array();
$countPerPage = 12;
$count = 0;
$prog_ids = array();
$dateStart = date("Y-m-d H:i:s");
$dateEnd = date("Y-m-d 00:00:00");

$arFilter = array(
    //"=UF_PROG.UF_ACTIVE" => 1,
    ">=UF_DATE_START" => new \Bitrix\Main\Type\DateTime($dateStart, 'Y-m-d H:i:s'),
    "=UF_DATE" => new \Bitrix\Main\Type\DateTime($dateEnd, 'Y-m-d H:i:s')
);

$arSelect = array(
    "ID", "UF_CODE", "UF_DATE_START", "UF_DATE_END", "UF_DATE", "UF_CHANNEL_ID", "UF_PROG_ID",
    "UF_TITLE" => "UF_PROG.UF_TITLE", "UF_SUB_TITLE" => "UF_PROG.UF_SUB_TITLE", "UF_IMG_PATH" => "UF_PROG.UF_IMG.UF_PATH",
    "UF_CHANNEL_CODE" => "UF_CHANNEL.UF_CODE", "UF_CATEGORY" => "UF_PROG.UF_CATEGORY",
    "UF_ID" => "UF_PROG.UF_EPG_ID"
);

if($USER->IsAuthorized())
{
    $selectedChannels = array();
    $arProgByUsers = array();

    //get subsribed channels
    $result = \Hawkart\Megatv\SubscribeTable::getList(array(
        'filter' => array("=UF_ACTIVE" => 1, "=UF_USER_ID" => $USER->GetID(), ">UF_CHANNEL_ID" => 0),
        'select' => array("UF_CHANNEL_ID")
    ));
    while ($arSub = $result->fetch())
    {
        $selectedChannels[] = $arSub["UF_CHANNEL_ID"];
    } 
    
    $arRecords = $APPLICATION->GetPageProperty("ar_record_status");
    $arRecordsStatuses = json_decode($arRecords, true);
    
    $recording_ids = array();
    foreach($arRecordsStatuses["RECORDING"] as $schedule_id => $arRecord)
    {
        $recording_ids[] = $schedule_id;
    }
    
    $arFilter["=UF_CHANNEL_ID"] = $selectedChannels;  
    if(count($recording_ids)>0)
        $arFilter["!=ID"] = $recording_ids;
    
    $arRecommend = \Hawkart\Megatv\CStat::getRecommend($USER->GetID()); 
    
    //CDev::pre($arRecommend);
    
    $arProgs = array();
    $arRecommendSorted = array();
    foreach($arRecommend as $by_what=>$epg_ids)
    {
        $count = 0;
        
        if(count($epg_ids)>0)
        {
            $uf_ids = array();
            
            $result = \Hawkart\Megatv\ScheduleTable::getList(array(
                'filter' => $arFilter + array("=UF_PROG.UF_EPG_ID" => $epg_ids),
                'select' => $arSelect,
                'order' => array("UF_PROG.UF_RATING" => "DESC"),
                //'limit' => 200
            ));
            while ($arSchedule = $result->fetch())
            {
                $arSchedule["UF_DATE_START"] = $arSchedule["DATE_START"] = $arSchedule['UF_DATE_START']->toString();
                $arSchedule["UF_DATE_END"] = $arSchedule["DATE_END"] = $arSchedule['UF_DATE_END']->toString();
                $arSchedule["UF_DATE"] = $arSchedule["DATE"] = $arSchedule['UF_DATE']->toString();
                $arSchedule["DETAIL_PAGE_URL"] = "/channels/".$arSchedule["UF_CHANNEL_CODE"]."/".$arSchedule["UF_ID"]."/?event=".$arSchedule["ID"];
                $arProgs[$arSchedule["UF_ID"]] = $arSchedule;
            }
            
            //sort by recommend
            foreach($epg_ids as $uf_id)
            {
                if(in_array($uf_id, $uf_ids))
                    continue;
                
                $uf_ids[] = $uf_id;
                
                $arSchedule = $arProgs[$uf_id];
                
                if(intval($arSchedule["ID"])>0)
                {
                    $arRecommendSorted[$by_what][] = $arSchedule;
                    $count++;
                }
                
                unset($arSchedule);
                
                if($count==$countPerPage*2)
                    break;
            }
            unset($uf_ids);
            unset($arProgs);
        }
    }
    
    //from 3 array get 10 items
    $notRepeatIds = array();
    $count = 0;
    
    if($countPerPage>count($arRecommendSorted["by_users"]))
        $countPerPage = count($arRecommendSorted["by_users"]);
    
    while($count<$countPerPage/* && $countPerPage<count($arRecommendSorted["by_users"])*/)
    {
        foreach(array("by_users", "by_records", "by_ganres") as $by_what)
        {
            $added = false;
            
            if(count($arRecommendSorted[$by_what])==0)
                continue;
            
            while(!$added)
            {
                $arSchedule = array_shift($arRecommendSorted[$by_what]);
                
                if(!in_array($arSchedule["UF_ID"], $notRepeatIds))
                {
                    $notRepeatIds[] = $arSchedule["UF_ID"];
                    
                    if($count<$countPerPage)
                    {
                        $arResult["PROGS"][] = $arSchedule;
                    
                        if(!empty($arSchedule["UF_CATEGORY"]))
                            $arCats[] = $arSchedule["UF_CATEGORY"];
                    }
                    
                    $added = true;
                    $count++;
                }
                
                unset($arRecommendSorted[$by_what][0]);
                $arRecommendSorted[$by_what] = array_values($arRecommendSorted[$by_what]);
                
                if(count($arRecommendSorted[$by_what])==0)
                    $added = true;
            }
        }
    }
        
}else{
    
    $arFilter["=UF_CHANNEL.UF_ACTIVE"] = 1;
    
    $result = \Hawkart\Megatv\ScheduleTable::getList(array(
        'filter' => $arFilter,
        'select' => $arSelect,
        'order' => array("UF_PROG.UF_RATING" => "DESC"),
        'limit' => 200
    ));
    while ($arSchedule = $result->fetch())
    {   
        if(in_array($arSchedule["UF_PROG_ID"], $prog_ids))
            continue;
            
        $prog_ids[] = $arSchedule["UF_PROG_ID"];
        
        if($count<$countPerPage)
        {
            $arSchedule["UF_DATE_START"] = $arSchedule["DATE_START"] = $arSchedule['UF_DATE_START']->toString();
            $arSchedule["UF_DATE_END"] = $arSchedule["DATE_END"] = $arSchedule['UF_DATE_END']->toString();
            $arSchedule["UF_DATE"] = $arSchedule["DATE"] = $arSchedule['UF_DATE']->toString();
            $arSchedule["DETAIL_PAGE_URL"] = "/channels/".$arSchedule["UF_CHANNEL_CODE"]."/".$arSchedule["UF_ID"]."/?event=".$arSchedule["ID"];
        
            if(!empty($arSchedule["UF_CATEGORY"]))
                $arCats[] = $arSchedule["UF_CATEGORY"];    
            
            $arResult["PROGS"][] = $arSchedule;
            $count++;
        }
    }
}

if(count($arResult["PROGS"])>0)
{
    $arResult["PROGS"] = \Hawkart\Megatv\CScheduleView::setIndex(array(
        "PROGS" => $arResult["PROGS"],
    ));
}

$arCats = array_unique($arCats);

$arResult["CATEGORIES"] = array();
foreach($arCats as $category)
{
    $arParams = array("replace_space"=>"-", "replace_other"=>"-");
    $str = \CDev::translit($category, "ru", $arParams);
    $arResult["CATEGORIES"][$category] = $str; 
}

$this->IncludeComponentTemplate();
?>