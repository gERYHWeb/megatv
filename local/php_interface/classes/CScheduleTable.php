<?
class CScheduleTable
{    
    /**
     * array(
        "CITY" => $arParams["CITY"],
        "COUNT" => $arChannel["COUNT"],
        "PROGS" => $arChannel["PROGS"],
        "NEWS" => $arChannel["PROPERTIES"]["NEWS"]["VALUE"],
     )
    */
    public static function setIndex($arParams)
    {
        global $CACHE_MANAGER;
        
        /*$obCache = new CPHPCache;
		$cache_time = 86400;
		$cache_path = "schedule_index";
        
        $arRes = array();
        $cache_id = 'cache_'.serialize( $arParams["PROGS"] ).serialize( $arParams["CITY"]["ID"] );
		if($cache_time > 0 && $obCache->InitCache($cache_time, $cache_id, $cache_path))
        {
			$arProgs = $obCache->GetVars();
		}
        elseif($obCache->StartDataCache())
        {*/        
            $arResult = array();
            $needDelete = false;
        
            $arProgs = $arParams["PROGS"];
            unset($arParams["PROGS"]);
            
            $arParams["COUNT"] = count($arProgs);
            
            if(count($arProgs)==0)
                return false;
            
            if($arParams["NEWS"] || $arParams["COUNT"]>BROADCAT_COLS*2)
            {
                $arParts["HALF"] = BROADCAT_COLS;
                $needDelete = $arParams["COUNT"] - BROADCAT_COLS*2;
            }else{
                if($arParams["COUNT"]>=BROADCAT_COLS)  //больше 12 колонок
                {
                    $ostatok = BROADCAT_COLS*2 - $arParams["COUNT"];
                    $arParts["ONE"] = $ostatok;
                    $arParts["HALF"] = ($arParams["COUNT"]-$ostatok)/2;
                }else{
                    if(BROADCAT_COLS>$arParams["COUNT"]*2)
                    {
                        $arParts["DOUBLE"] = floor($arParams["COUNT"]/2);
                        $arParts["ONE"] = $arParams["COUNT"] - $arParts["DOUBLE"];//$arParams["COUNT"]%2;
                    }else{
                        $double = BROADCAT_COLS - $arParams["COUNT"];
                        $arParts["DOUBLE"] = $double;
                        $arParts["ONE"] = $arParams["COUNT"]-$double;
                    }
                }
            }     
            
            //Отсортируем программы по рейтингу
            $arProgsSorted = $arProgs;
            foreach($arProgsSorted as $key => &$arProg)
            {
                foreach($arProgsSorted as $key2 => &$arProg_2)
                {
                    if($arProg["ID"]!=$arProg_2["ID"])
                    {
                        $rating = intval($arProg["PROPERTY_RATING_VALUE"]);
                        $rating_2 = intval($arProg_2["PROPERTY_RATING_VALUE"]);
                        
                        if($rating_2<$rating)
                        {
                            $prog = $arProg_2;
                            $arProg_2 = $arProg;
                            $arProg = $prog;
                        }
                    }
                }
            }
            
            //Отсортируем все ключи программ в порядке рейтинга
            $allKeys = array(); 
            foreach($arProgs as $key=>$arProg)
            {
                foreach($arProgsSorted as $arProg_2)
                {
                    if($arProg["ID"]==$arProg_2["ID"])
                    {
                        $allKeys[] = $key;
                        break;
                    }
                }
            }
            
            //CDev::pre($arParts);
                        
            if(count($arParts["HALF"])==0)
            {
                if(count($arParts["DOUBLE"])>0)
                {
                    while($arParts["DOUBLE"]>0)
                    {
                        $key = array_shift($allKeys);
                        $arProgs[$key]["CLASS"] = "double"; 
                        $allKeys = array_diff($allKeys, array($key));
                        $arParts["DOUBLE"]--;
                    }
                }
                
                if(count($arParts["ONE"])>0)
                {
                    while($arParts["ONE"]>0)
                    {
                        $key = array_shift($allKeys);
                        $arProgs[$key]["CLASS"] = "one"; 
                        $allKeys = array_diff($allKeys, array($key));
                        $arParts["ONE"]--;
                    }
                }
            }else{
                $allKeysReverse = array_reverse($allKeys);
                
                //Получили ключи с худшим рейтингом в количестве = количеству нужных половин * 2
                $halfKeys = array();
                for($i=0; $i<$arParts["HALF"]*2; $i++)
                {
                    $halfKeys[] = $allKeysReverse[$i];
                }
                
                //проверим сколько из этих ключей являются парами
                $countHalfs = 0;
                $halfKeysDelete = array();
                sort($halfKeys);    //отсортируем
                for($i=1; $i<count($halfKeys); $i=$i+2)
                {
                    if(abs($halfKeys[$i]-$halfKeys[$i-1])==1)
                    {
                        $allKeys = array_diff($allKeys, array($halfKeys[$i], $halfKeys[$i-1]));
                        $halfKeysDelete[] = $halfKeys[$i];
                        $halfKeysDelete[] = $halfKeys[$i-1];
                        $arProgs[$halfKeys[$i]]["CLASS"] = "half";
                        $arProgs[$halfKeys[$i-1]]["CLASS"] = "half";
                        $countHalfs++;
                    }
                }
                $halfKeys = array_diff($halfKeys, $halfKeysDelete);

                //если остались половинуи, то берем соседние половинок
                $diff = 0;
                $halfKeysDelete = array();
                if(count($arParts["HALF"])>$countHalfs)
                {
                    $diff = $arParts["HALF"]-$countHalfs;

                    foreach($halfKeys as $key)
                    {
                        $key_1 = $key-1;
                        $key_2 = $key+1;
                        if(in_array($key_1, $allKeys))
                        {
                            $arProgs[$key]["CLASS"] = "half"; 
                            $arProgs[$key_1]["CLASS"] = "half";
                            $halfKeysDelete[] = $key;
                            $halfKeysDelete[] = $key_1;
                            $allKeys = array_diff($allKeys, array($key, $key_1));
                            $diff--;
                        }
                        elseif(in_array($key_2, $allKeys))
                        {
                            $arProgs[$key]["CLASS"] = "half"; 
                            $arProgs[$key_2]["CLASS"] = "half";
                            $halfKeysDelete[] = $key;
                            $halfKeysDelete[] = $key_2;
                            $allKeys = array_diff($allKeys, array($key, $key_2));
                            $diff--;
                        }
                        
                        if($diff==0)
                            break;
                    }
                    $halfKeys = array_diff($halfKeys, $halfKeysDelete);
                }
                
                //если остались половинки, то берем соседние половинок
                if($diff>0)
                {
                    if(count($halfKeys)>0)
                    {
                        foreach($halfKeys as $key)
                        {
                            $arProgs[$key]["CLASS"] = "one"; 
                            $allKeys = array_diff($allKeys, array($key));
                            $arParts["ONE"]--;
                        }
                    }
                }
                
                if(count($arParts["DOUBLE"])>0)
                {
                    while($arParts["DOUBLE"]>0)
                    {
                        $key = array_shift($allKeys);
                        $arProgs[$key]["CLASS"] = "double"; 
                        $allKeys = array_diff($allKeys, array($key));
                        $arParts["DOUBLE"]--;
                    }
                }
                
                if(count($arParts["ONE"])>0)
                {
                    while($arParts["ONE"]>0)
                    {
                        $key = array_shift($allKeys);
                        $arProgs[$key]["CLASS"] = "one"; 
                        $allKeys = array_diff($allKeys, array($key));
                        $arParts["ONE"]--;
                    }
                }
                
            }                   
            
            //Удаляем каждый 3-й
            /*if($needDelete)
            {
                $k = 0;
                foreach($arProgs as $key=>$arProg)
                {
                    if($k%3==0 && $k!=0)
                        unset($arProgs[$key]);
                    $k++;
                }
            }*/

            //echo count($arProgs);
            //CDev::pre($arParts, true, false);

            //Все ключи программ
            /*$allKeys = array_keys($arProgs); 
            $countHalfs = $arParts["HALF"];
            
            if(count($arParts["HALF"])>0)
            {
                $doubleKeys = self::getDoubleArray($allKeys);
                $allDoubleKeys = array_keys($doubleKeys); 
                
                while($arParts["HALF"]>0)
                {
                    $key = array_rand($allDoubleKeys, 1);
                    $keys = $doubleKeys[$key];
                    unset($allDoubleKeys[$key]);

                    $arProgs[$keys[0]]["CLASS"] = "half"; 
                    $arProgs[$keys[1]]["CLASS"] = "half";
                    $allKeys = array_diff($allKeys, array($keys[0], $keys[1]));
                    
                    //echo $keys[0]." ".$keys[1]." ".$arParts["HALF"]."<br />";
                    
                    $arParts["HALF"]--;
                }
            }
            

            if(count($arParts["DOUBLE"])>0)
            {
                while($arParts["DOUBLE"]>0)
                {
                    $key = array_rand($allKeys, 1);
                    $arProgs[$key]["CLASS"] = "double"; 
                    $allKeys = array_diff($allKeys, array($key));
                    $arParts["DOUBLE"]--;
                }
            }
            
            if(count($arParts["ONE"])>0)
            {
                while($arParts["ONE"]>0)
                {
                    $key = array_rand($allKeys, 1);
                    $arProgs[$key]["CLASS"] = "one"; 
                    $allKeys = array_diff($allKeys, array($key));
                    $arParts["ONE"]--;
                }
            }*/
            
            //CDev::pre($arParts, true, false);
			//$obCache->EndDataCache($arRes); 
		//} // END CACHE
        
        return $arProgs;
    }
    
    public static function setChannel($arParams)
    {
        $arProgs = $arParams["PROGS"];
        unset($arParams["PROGS"]);
        
        $arParams["COUNT"] = count($arProgs);
        
        if(count($arProgs)==0)
            return false;

        $allKeys = array_keys($arProgs);
        $key = array_rand($allKeys, 1);
        unset($allKeys[$key]);
        $arProgs[$key]["CLASS"] = "one"; 
        
        foreach($allKeys as $key)
        {
            $arProgs[$key]["CLASS"] = "half"; 
        }
        return $arProgs;
    }
    
    public static function setRecommendIndex($arParams)
    {
        $arProgs = $arParams["PROGS"];        
        $arParams["COUNT"] = count($arProgs);
        
        if(count($arProgs)==0)
            return false;
        
        foreach($arProgs as $key=>$arProg)
        {
            if($key<4)
            {
                $arProgs[$key]["CLASS"] = "quadro"; 
            }else{
                $arProgs[$key]["CLASS"] = "one"; 
            }
        }
        return $arProgs;
    }
    
    public static function topics($arParams)
    {
        $arProgs = $arParams["PROGS"];        
        $arParams["COUNT"] = count($arProgs);
        
        if(count($arProgs)==0)
            return false;
        
        foreach($arProgs as $key=>$arProg)
        {
            $arProgs[$key]["CLASS"] = "one"; 
        }
        return $arProgs;
    }
    
    public static function getDoubleArray($array)
    {
        $doubleArray = array();
        $count = floor(count($array)/2);
        
        for($key = 0; $key<count($array); $key=$key+2)
        {
            if($key/2<$count)
            {
                $doubleArray[] = array($key, ($key+1));
            }else{
                break;
            }
        }
        
        return $doubleArray;
    }    
}