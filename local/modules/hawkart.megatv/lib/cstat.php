<?php

namespace Hawkart\Megatv;

class CStat
{
    public static function getByUser($user_id = false)
    {
        global $USER;
        
        if($USER->IsAuthorized() && !$user_id)
            $user_id = $USER->GetID();
        
        $rsUser = \CUser::GetByID($user_id);
        $arUser = $rsUser->Fetch();
        
        return json_decode($arUser["UF_STATISTIC"], true);
    }
    
    public static function countRate($action)
    {
        switch($action)
        {
            case "record":
                $arRate = array(
                    "CATS" => 4,
                    "TAGS" => 0.4, 
                    "CHANNELS" => 1,
                    "SERIALS" => 1,
                    "GANRES" => 1
                );
            break;
            
            case "channelShow":
                $arRate = array(
                    "CHANNELS" => 1
                );
            break;
            
            case "scheduleShow":
                $arRate = array(
                    "CATS" => 1,
                    "TAGS" => 0.1,
                    "GANRES" => 1
                );
            break;
            
            case "quaterShow_1":
                $arRate = array(
                    "CATS" => 1,
                    "TAGS" => 0.5,
                    "CHANNELS" => 0.5, 
                    "SERIALS" => 1,
                    "GANRES" => 1
                );
            break;
            
            case "quaterShow_2":
                $arRate = array(
                    "CATS" => 2,
                    "TAGS" => 0.5,
                    "CHANNELS" => 1, 
                    "SERIALS" => 1,
                    "GANRES" => 1
                );
            break;
            
            case "quaterShow_3":
                $arRate = array(
                    "CATS" => 3,
                    "TAGS" => 0.5,
                    "CHANNELS" => 1, 
                    "SERIALS" => 1.5,
                    "GANRES" => 1
                );
            break;
            
            case "quaterShow_4":
                $arRate = array(
                    "CATS" => 4,
                    "TAGS" => 0.5,
                    "CHANNELS" => 1, 
                    "SERIALS" => 2,
                    "GANRES" => 1
                );
            break;
        }
        
        return $arRate;
    }
    
    public static function addByRecord($prog_id, $action = false)
    {
        global $USER;
        if($USER->IsAuthorized())
        {
            $arStatistic = self::getByUser();
            
            $arSelect = array(
                "UF_CHANNEL_BASE_ID" => "UF_CHANNEL.UF_BASE_ID", "UF_CATEGORY" => "UF_PROG.UF_CATEGORY", 
                "UF_SERIAL" => "UF_PROG.UF_EPG_ID", "UF_GANRE" => "UF_PROG.UF_GANRE"
            );
            $result = \Hawkart\Megatv\RecordTable::getList(array(
                'filter' => array("=ID" => $prog_id),
                'select' => $arSelect,
                'limit' => 1
            ));
            $arSchedule = $result->fetch();
            
            $arCount = self::countRate($action);
            
            if(!empty($arSchedule["UF_CHANNEL_BASE_ID"]))
                $arStatistic["CHANNELS"][$arSchedule["UF_CHANNEL_BASE_ID"]] += floatval($arCount["CHANNELS"]);
            
            if(!empty($arSchedule["UF_CATEGORY"]))
                $arStatistic["CATS"][$arSchedule["UF_CATEGORY"]] += floatval($arCount["CATS"]);
            
            if(!empty($arSchedule["UF_SERIAL"])) 
                $arStatistic["SERIALS"][$arSchedule["UF_SERIAL"]] += floatval($arCount["SERIALS"]);
                
            if(!empty($arSchedule["UF_GANRE"]))
            {
                $arGanres = explode(",", $arSchedule["UF_GANRE"]);
                foreach($arGanres as $ganre)
                {
                    $arStatistic["GANRES"][$ganre] += floatval($arCount["GANRES"])/count($arGanres);
                }
            } 
                
            self::save($arStatistic);
        }
    }
    
    public static function addByShedule($prog_id, $action = false)
    {
        global $USER;
        if($USER->IsAuthorized())
        {
            $arStatistic = self::getByUser();
            
            $arSelect = array(
                "UF_CHANNEL_BASE_ID" => "UF_CHANNEL.UF_BASE_ID", "UF_CATEGORY" => "UF_PROG.UF_CATEGORY", 
                "UF_SERIAL" => "UF_PROG.UF_EPG_ID", "UF_GANRE" => "UF_PROG.UF_GANRE"
            );
            $result = \Hawkart\Megatv\ScheduleTable::getList(array(
                'filter' => array("=ID" => $prog_id),
                'select' => $arSelect,
                'limit' => 1
            ));
            $arSchedule = $result->fetch();
            
            $arCount = self::countRate($action);
            
            if(!empty($arSchedule["UF_CHANNEL_BASE_ID"]))
                $arStatistic["CHANNELS"][$arSchedule["UF_CHANNEL_BASE_ID"]] += floatval($arCount["CHANNELS"]);
            
            if(!empty($arSchedule["UF_CATEGORY"]))
                $arStatistic["CATS"][$arSchedule["UF_CATEGORY"]] += floatval($arCount["CATS"]);
            
            if(!empty($arSchedule["UF_SERIAL"])) 
                $arStatistic["SERIALS"][$arSchedule["UF_SERIAL"]] += floatval($arCount["SERIALS"]);
            
            if(!empty($arSchedule["UF_GANRE"]))
            {
                $arGanres = explode(",", $arSchedule["UF_GANRE"]);
                foreach($arGanres as $ganre)
                {
                    $arStatistic["GANRES"][$ganre] += floatval($arCount["GANRES"])/count($arGanres);
                }
            }
            
            self::save($arStatistic);
        }
    }
    
    public static function channelAdd($channel_id, $action = false)
    {
        if(!$action)
            $action = "channelShow";
        
        global $USER;
        if($USER->IsAuthorized())
        {
            $arStatistic = self::getByUser();
            $arCount = self::countRate($action);
            $arStatistic["CHANNELS"][$channel_id] += intval($arCount["CHANNELS"]);
            self::save($arStatistic);
        }
    }
    
    public static function save($arStatistic, $user_id = false)
    {
        global $USER;
        
        if($USER->IsAuthorized() && !$user_id)
            $user_id = $USER->GetID();
        
        $cuser = new \CUser;
        $cuser->Update($user_id, array(
            "UF_STATISTIC" => json_encode($arStatistic)
        ));
    }
    
    /**
     * $arProgs = array("UF_ID", "ID", "UF_GANRE")
     */
    public static function getProgsByGanre($arProgs, $arStatistic)
    {
        $arProgsByRating = array();
        foreach($arProgs as $arProg)
        {
            $count = count($arProg["UF_GANRE"]);
            $total_rating = 0;
            foreach($arProg["UF_GANRE"] as $ganre)
            {
                $ganre_rating = $arStatistic["GANRES"][$ganre];
                $total_rating+= (1/$count) * $ganre_rating;
            }
            $arProgsByRating[$arProg["UF_EPG_ID"]] = $total_rating;
        }
        
        uasort($arProgsByRating, function($a, $b){
            return $b - $a;
        }); 
        $arProgsByRatingSorted = array_keys($arProgsByRating);
        unset($arProgsByRating);
        
        return $arProgsByRatingSorted;
    }
    
    public static function getTopRateSerialByUser($user_id, $arStatistic)
    {
        uasort($arStatistic["SERIALS"], function($a, $b){
            return $b - $a;
        }); 
        $arStatistic["SERIALS"] = array_keys($arStatistic["SERIALS"]);
        
        $ids = array();
        $dateStart = date("Y-m-d H:i:s", time() - 86400 * 7); 
        $result = \Hawkart\Megatv\RecordTable::getList(array(
            'filter' => array(
                "=UF_USER_ID" => $user_id, 
                "!UF_URL" => false,
                ">UF_DATE_START" => new \Bitrix\Main\Type\DateTime($dateStart, 'Y-m-d H:i:s'),
            ),
            'select' => array(
                "UF_ID" => "UF_PROG.UF_EPG_ID"
            ),
            'order' => array("UF_DATE_END" => "DESC")
        ));
        while ($arRecord = $result->fetch())
        {  
            $ids[] = $arRecord["UF_ID"];
        }
        
        $sortedIds = array();
        foreach($arStatistic["SERIALS"] as $id)
        {
            if(in_array($id, $ids))
            {
                $key = array_search ($id, $ids);
                $sortedIds[] = $id;
                unset($ids[$key]);
            }
        }
        
        if(count($ids)>0)
            $sortedIds = array_merge($sortedIds, $ids);
        
        unset($ids);
        $sortedIds = array_unique($sortedIds);
        
        return $sortedIds;
    }
    
    public static function getTopRateProg($arProgsByRating)
    {
        $ids = array();
        foreach($arProgsByRating as $arProg)
        {
            $ids[] = $arProg["UF_EPG_ID"];
        }
        
        /*
        $arIds = array();
        $selectedChannels = array();
        $result = \Hawkart\Megatv\SubscribeTable::getList(array(
            'filter' => array("=UF_ACTIVE" => 1, "=UF_USER_ID" => $user_id, ">UF_CHANNEL_ID" => 0),
            'select' => array("UF_CHANNEL_ID")
        ));
        while ($arSub = $result->fetch())
        {
            $selectedChannels[] = $arSub["UF_CHANNEL_ID"];
        }    
        
        $dateStart = date("Y-m-d H:i:s");
        $result = \Hawkart\Megatv\ScheduleTable::getList(array(
            'filter' => array(
                "=UF_CHANNEL_ID" => $selectedChannels,
                "=UF_PROG.UF_ACTIVE" => 1,
                ">UF_DATE_START" => new \Bitrix\Main\Type\DateTime($dateStart, 'Y-m-d H:i:s'),
            ),
            'select' => array(
                "ID", "UF_ID" => "UF_PROG.UF_EPG_ID"
            ),
            'order' => array("UF_PROG.UF_RATING" => "DESC"),
        ));
        while ($arSchedule = $result->fetch())
        {
            $arIds[] = $arSchedule["UF_ID"];
        }*/
        
        return $ids;
    }
    
    public static function getRecommend($user_id)
    {
        $rsUser = \CUser::GetByID($user_id);
        $arUser = $rsUser->Fetch();
        
        return json_decode($arUser["UF_RECOMMEND"], true);
    }
}