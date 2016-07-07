<?
class CTimeEx
{
    protected static $defaultTimezone = "+0300";
    
    public static function getDatetime()
    {
        $arCity = \Hawkart\Megatv\CityTable::getGeoCity();
        $curTimezone = $arCity["UF_TIMEZONE"]; //current timezone
        
        $arResult = array();
        $arResult["SERVER_DATETIME"] = date("d.m.Y H:i:s");     //серверная дата 
        $arResult["TIMEZONE"] = $curTimezone;
        $offset = intval($curTimezone) - intval(self::$defaultTimezone)/100;        
        $arResult["OFFSET"] = $offset;  //сдвиг относительно Москвы (берется из города)
        $arResult["SERVER_DATETIME_WITH_OFFSET"] = self::dateOffset($arResult["SERVER_DATETIME"], $offset);
        $arResult["SELECTED_DATE"] = $_SESSION["DATE_CURRENT_SHOW"];
        $arResult["SELECTED_DATETIME"] = $_SESSION["DATE_CURRENT_SHOW"].date(" H:i:s");
        $arResult["SELECTED_DATETIME_WITH_OFFSET"] = self::dateOffset($arResult["SELECTED_DATETIME"], $offset);
        return $arResult;
    }
    
    public static function getDateFilter($date, $offset = 0)
    {
        if(!$offset)
        {
            $arCity = \Hawkart\Megatv\CityTable::getGeoCity();
            $offset = intval($arCity["UF_TIMEZONE"]) - intval(self::$defaultTimezone)/100;
        }
        
        $date = date('d.m.Y 00:00:00', strtotime($date));
        $date = substr($date, 0, 10).date(" 00:00:00");  
        $next_date = date('d.m.Y 00:00:00', strtotime("+1 day", strtotime($date)));
        
        $arDate = array(
            "DATE_FROM" => self::dateOffset($date, (-1)*$offset),
            "DATE_TO" => self::dateOffset($next_date, (-1)*$offset),
            "OFFSET" => $offset,    //сдвиг
        );
        
        return $arDate;
    }
    
    public static function getDateTimeFilter($datetime, $offset = 0)
    {
        if(!$offset)
        {
            $arCity = \Hawkart\Megatv\CityTable::getGeoCity();
            $offset = intval($arCity["UF_TIMEZONE"]) - intval(self::$defaultTimezone)/100;
        }
        
        $datetime = date('d.m.Y H:i:s', strtotime($datetime));
        $next_datetime = date('d.m.Y H:i:s', strtotime("+1 day", strtotime($datetime)));
        
        $arDate = array(
            "DATE_FROM" => self::dateOffset($datetime, (-1)*$offset),
            "DATE_TO" => self::dateOffset($next_datetime, (-1)*$offset),
            "OFFSET" => $offset,    //сдвиг
        );
        
        return $arDate;
    }
    
    public static function getCalendarDays()
    {
        $arDate = self::getDatetime();
        $datetime = $arDate["SERVER_DATETIME_WITH_OFFSET"];
        
        //$w = date("w");
        $w = date("w", strtotime($datetime));                
        $dateFullDownload = 6;
        
        //понедельник
        if($w==0) $w = 7;
        
        //Меньше дня выгрузки
        if($w<$dateFullDownload) $w+=7;
            
        $countDays = 10 - ($w-$dateFullDownload);
        $countDays--;
        return $countDays;
        //return 4;
    }
    
    public static function getCurDate()
    {
        global $APPLICATION;
        if($APPLICATION->GetCurDir()=="/" && !isset($_GET["cur_date"]))
            $_SESSION["DATE_CURRENT_SHOW"] = date("d.m.Y");
        
        if(isset($_GET["cur_date"]) && !empty($_GET["cur_date"]))
        {
            $count_days = self::getCalendarDays();
            $max_date = date('d.m.Y', strtotime("+".$count_days." day", strtotime(date("d.m.Y"))));
            $min_date = date('d.m.Y', strtotime("-1 day", strtotime(date("d.m.Y"))));
            
            $date = substr($_GET["cur_date"], 0, 10);
            $date = date("d.m.Y", strtotime($date));
            
            if(strtotime($date)>strtotime($max_date) || strtotime($date)<strtotime($min_date))
            {
                $date = date("d.m.Y");
            }           
            
        }else{
            if(!isset($_SESSION["DATE_CURRENT_SHOW"]) || empty($_SESSION["DATE_CURRENT_SHOW"]))
            {
                $date = date("d.m.Y");  //текущая дата сервера
            }else{
                $date = $_SESSION["DATE_CURRENT_SHOW"];
            }  
        }
        
        $_SESSION["DATE_CURRENT_SHOW"] = $date;
        
        return $date;
    }
    
    public static function dateOffset($datetime, $offset=false)
    {
        if(!$offset)
        {
            $arCity = \Hawkart\Megatv\CityTable::getGeoCity();
            $offset = intval($arCity["UF_TIMEZONE"]) - intval(self::$defaultTimezone)/100;
        } 
        return date('d.m.Y H:i:s', strtotime($offset." hour", strtotime($datetime)));
    }
    
    //Высчитываем с учетом города сдвиг по времени относительно дня
    public static function getArDateTimeOffset($date = false, $offset = 0)
    {       
        if(!$offset)
        {
            $arCity = \Hawkart\Megatv\CityTable::getGeoCity();
            $offset = intval($arCity["UF_TIMEZONE"]) - intval(self::$defaultTimezone)/100;
        }
        
        if(!$date)
        {
            $date = self::getCurDate();
        }
        
        $date = substr($date, 0, 10).date(" 00:00:00");
            
        $next_date = date('d.m.Y 00:00:00', strtotime("+1 day", strtotime($date)));
           
        $arDate = array(
            "DATE_FROM" => self::dateOffset($date, $offset),    //дата со смещением
            "DATE_TO" => self::dateOffset($next_date, $offset), //дата со смещением +1 день
            "OFFSET" => $offset,    //сдвиг
            //"DATETIME_REAL" => date("d.m.Y H:i:s"), //дата настоящая без сдвигов и выборов
            //"DATETIME_CURRENT" => substr($date, 0, 10).date(" H:i:s"),  //дата выбранная без сдвига
        );
        
        return $arDate;
    }    
    
    public static function dateToStr($date = false)
    {
        if(!$date)
            $date = self::getCurDate();
            
        $arDATE = ParseDateTime($date, FORMAT_DATETIME);
        return $arDATE["DD"]." ".ToLower(GetMessage("MONTH_".intval($arDATE["MM"])."_S"))." ".$arDATE["YYYY"];
    }
    
    public static function dateToStrWithDay($date = false)
    {
        if(!$date)
            $date = self::getCurDate();
            
        $day = FormatDate("l", MakeTimeStamp($date));
            
        $arDATE = ParseDateTime($date, FORMAT_DATETIME);
        return $day.". ".$arDATE["DD"]." ".ToLower(GetMessage("MONTH_".intval($arDATE["MM"])."_S"));
    }
    
    public static function dateDiff($date1, $date2)
    {
        return strtotime($date1)<strtotime($date2);
    }
    
    public static function secToTime($sec) 
    {
        if ($sec <= 0)
            return false;
            
        $hh = floor($sec/3600); 
        $min = floor(($sec-$hh*3600)/60); 
        $sec = $sec-$hh*3600-$min*60; 
    
        return array(
            "h" => $hh,
            "i" => $min,
            "s" => $sec
        );
    }
    
    public static function secToStr($sec)
    {
        $arTime = self::secToTime($sec);
        
        $str = "";
        if($arTime["h"])
        {
            $str.= $arTime["h"]." ".CDev::number_ending($arTime["h"], "часов", "час", "часа")." ";
        }
        if($arTime["i"])
        {
            $str.= $arTime["i"]." ".CDev::number_ending($arTime["i"], "минут", "минута", "минуты");
        }
        return $str;
    }
}