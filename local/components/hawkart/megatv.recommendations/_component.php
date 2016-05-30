<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arResult["PROGS"] = array();
$arTime = \CTimeEx::getDatetime();

$arMatrix = array();

if($USER->IsAuthorized())
{
    $arStatistic = \Hawkart\Megatv\CStat::getByUser();
    foreach(array("CATS", "TAGS", "CHANNELS", "SERIALS") as $statPart)
    {
        uasort($arStatistic[$statPart], function($a, $b){
            return $b - $a;
        });
        
        $arStatistic[$statPart] = array_keys($arStatistic[$statPart]);
        array_splice($arStatistic[$statPart], 3);
        foreach($arStatistic[$statPart] as $key)
        {
            $arMatrix[$statPart][$key] = array();
        }
    }
    unset($arStatistic);
}

//CDev::pre($arMatrix);

//get progs by rating
$countPerPage = 20;
$limit = 200;
$count = 0;
$prog_ids = array();

$arParams["CURRENT_DATETIME"] = date("d.m.Y H:i:s", strtotime($arTime["SERVER_DATETIME_WITH_OFFSET"]));
$dateStart = date("Y-m-d H:i:s", strtotime($arParams["CURRENT_DATETIME"]));
$result = \Hawkart\Megatv\ScheduleTable::getList(array(
    'filter' => array(
        "UF_CHANNEL.UF_ACTIVE" => 1,
        "UF_PROG.UF_ACTIVE" => 1,
        ">UF_DATE_START" => new \Bitrix\Main\Type\DateTime($dateStart, 'Y-m-d H:i:s'),
    ),
    'select' => array(
        "ID", "UF_CODE", "UF_DATE_START", "UF_DATE_END", "UF_DATE", "UF_CHANNEL_ID", "UF_PROG_ID",
        "UF_TITLE" => "UF_PROG.UF_TITLE", "UF_SUB_TITLE" => "UF_PROG.UF_SUB_TITLE", "UF_IMG_PATH" => "UF_PROG.UF_IMG.UF_PATH",
        "UF_CHANNEL_CODE" => "UF_CHANNEL.UF_CODE", "UF_CATEGORY" => "UF_PROG.UF_CATEGORY",
        "UF_ID" => "UF_PROG.UF_EPG_ID"
    ),
    'order' => array("UF_PROG.UF_RATING" => "DESC"),
    'limit' => $limit
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
        
        if($USER->IsAuthorized())
        {
            if(array_key_exists($arSchedule["UF_CATEGORY"], $arMatrix["CATS"]))
            {
                $arMatrix["CATS"][$arSchedule["UF_CATEGORY"]][] = $arSchedule; $count++;
            }
            else if(array_key_exists($arSchedule["UF_ID"], $arMatrix["SERIALS"]))
            {
                $arMatrix["SERIALS"][$arSchedule["UF_ID"]][] = $arSchedule; $count++;
            }
            else if(array_key_exists($arSchedule["UF_CHANNEL_ID"], $arMatrix["CHANNELS"]))
            {
                $arMatrix["CHANNELS"][$arSchedule["UF_CHANNEL_ID"]][] = $arSchedule; $count++;
            }
        }else{
            $arResult["PROGS"][] = $arSchedule;
            $count++;
        }
    }
}

if($USER->IsAuthorized())
{
    //CDev::pre($arMatrix);
    
    $arResult["PROGS"] = array();
    foreach(array("CATS", "CHANNELS", "SERIALS") as $statPart)
    {
        $arMatrixMerged[$statPart] = array();
        foreach($arMatrix[$statPart] as $key=>$arSchedule)
        {
            $arMatrixMerged[$statPart] = array_merge($arMatrixMerged[$statPart], $arSchedule);
        }
    }
    unset($arMatrix);
    
    $arResult["PROGS"] = $arMatrixMerged["CATS"];
    //CDev::pre($arMatrixMerged);
    
    /*$i = 1;
    $str = 1;
    while($i<$count)
    {
        foreach(array("CATS", "CHANNELS", "SERIALS") as $statPart)
        {
            $arMatrixMerged
        }
        $arResult["PROGS"][]
    }*/
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