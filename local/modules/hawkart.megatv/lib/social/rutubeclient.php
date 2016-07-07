<?
namespace Hawkart\Megatv\Social;

class RutubeClient{
    
    /**
     * Build request to resource
     * 
     * @array return
     */
    public static function api($method, $params = array())
    {
        $url = $method;
        if(strpos($method, "http://rutube.ru/api/")===false)
            $url = 'http://rutube.ru/api/' . $method;
        
        if(count($params)>0)
    	   $url.= '?' . http_build_query($params);
        
    	$response = file_get_contents($url);
    	return json_decode($response, true);
    }
    
    /**
     * Get all serials from resource
     *
     * @array return
     */
    public static function getSerials()
    {
        $arSerials = array();
        $page = 1;
        
        while(intval($page)>0)
        {
            $params = array("page" => $page);
            $json = self::api('metainfo/tv/', $params);
            
            foreach($json["results"] as $arSerial)
            {
                if($arSerial["type"]["id"]==3)  //is serial
                {
                    $arSerials[] = $arSerial;
                }  
            }
            
            $page = str_replace("http://rutube.ru/api/metainfo/tv/?page=", "", $json["next"]);
        }
        
        return $arSerials;
    }
    
    /**
     * Import all serials href from resource to db 
     */
    public static function importSerials($arSerials)
    {
        $arTableSerials = array();
        $result = \Hawkart\Megatv\SerialTable::getList(array(
            'filter' => array("!UF_EPG_ID" => false/*, "UF_EXTERNAL_URL" => false*/),
            'select' => array("ID", "UF_TITLE",  "UF_EXTERNAL_TITLE"/*, "UF_EPG_ID"*/)
        ));
        while ($row = $result->fetch())
        {
            if($row["UF_EXTERNAL_TITLE"])
            {
                $title = $row["UF_EXTERNAL_TITLE"];
            }else{
                $title = $row["UF_TITLE"];
            }
            $arTableSerials[strtoupper($title)] = $row;
        }
        
        foreach($arSerials as $arSerial)
        {
            $href = $arSerial["content"];
            $title = strtoupper($arSerial["name"]);
            $description = $arSerial["description"];
            
            if($arTableSerials[$title]["ID"]>0)
            {
                \Hawkart\Megatv\SerialTable::update($arTableSerials[$title]["ID"], array(
                    "UF_EXTERNAL_TITLE" => $arSerial["name"],
                    "UF_EXTERNAL_URL" => $href,
                    "UF_DESC" => $description
                ));
                
                $arTableItems = array();
                $result = \Hawkart\Megatv\ProgExternalTable::getList(array(
                    'filter' => array("=UF_SERIAL_ID" => $arTableSerials[$title]["ID"]),
                    'select' => array("ID", "UF_SERIAL_ID",  "UF_EXTERNAL_ID")
                ));
                while ($row = $result->fetch())
                {
                    $arTableItems[] = $row["UF_EXTERNAL_ID"];
                }
                
                /**
                 * Add items for serials in db table
                 */
                $arItems = self::getItemsBySerialUrl($href);
                foreach($arItems as $arItem)
                {
                    if(!in_array($arItem["ID"], $arTableItems))
                    {
                        $arFields = array(
                            "UF_TITLE" => $arItem["TITLE"],
                            "UF_EXTERNAL_ID" => $arItem["ID"],
                            "UF_SERIAL_ID" => $arTableSerials[$title]["ID"],
                            "UF_THUMBNAIL_URL" => $arItem["THUMBNAIL_URL"],
                            "UF_VIDEO_URL" => $arItem["VIDEO_URL"],
                            "UF_JSON" => $arItem["JSON"],
                        );
                        $result = \Hawkart\Megatv\ProgExternalTable::add($arFields);
                        if ($result->isSuccess())
                        {
                            $arTableItems[] = $result->getId();
                        }else{
                            $errors = $result->getErrorMessages();
                        }
                    }
                }
                
                //$file = \Hawkart\Megatv\SerialTable::getFilePathBySerial($arTableSerials[$title]["UF_EPG_ID"]);
                //\Hawkart\Megatv\SerialTable::saveToFile($arItems, $file);
            }
        }
    }
    
    /**
     * Import all serials href from resource to db 
     */
    public static function importSerialsByWeek()
    {
        $arTableSerials = array();
        $result = \Hawkart\Megatv\SerialTable::getList(array(
            'filter' => array("!UF_EPG_ID" => false, "UF_EXTERNAL_URL" => "%rutube%"),
            'select' => array("ID", "UF_TITLE", "UF_EXTERNAL_URL")
        ));
        while ($row = $result->fetch())
        {
            $serial_id = preg_replace("/[^0-9]/", "", $row["UF_EXTERNAL_URL"]);
            $arTableSerials[$serial_id] = $row;
        }
        
        foreach($arTableSerials as $serial_id=>$arSerial)
        {
            $arTableItems = array();
            $result = \Hawkart\Megatv\ProgExternalTable::getList(array(
                'filter' => array("=UF_SERIAL_ID" => $arSerial["ID"]),
                'select' => array("ID", "UF_SERIAL_ID",  "UF_EXTERNAL_ID")
            ));
            while ($row = $result->fetch())
            {
                $arTableItems[] = $row["UF_EXTERNAL_ID"];
            }
            
            $arItems = self::search($arSerial["UF_TITLE"], "week", $serial_id);
            
            /**
             * Add items for serials in db table
             */
            foreach($arItems as $arItem)
            {
                if(!in_array($arItem["ID"], $arTableItems))
                {
                    $arFields = array(
                        "UF_TITLE" => $arItem["TITLE"],
                        "UF_EXTERNAL_ID" => $arItem["ID"],
                        "UF_SERIAL_ID" => $$arSerial["ID"],
                        "UF_THUMBNAIL_URL" => $arItem["THUMBNAIL_URL"],
                        "UF_VIDEO_URL" => $arItem["VIDEO_URL"],
                        "UF_JSON" => $arItem["JSON"],
                    );
                    $result = \Hawkart\Megatv\ProgExternalTable::add($arFields);
                    if ($result->isSuccess())
                    {
                        $arTableItems[] = $result->getId();
                    }else{
                        $errors = $result->getErrorMessages();
                    }
                }
            }
        }
    }   
    
    /**
     * Search new videos
     *
     * @array return
     */
    public static function search($query, $period = "week", $serial_id = false)
    {
        $arItems = array();
        $params = array(
            "query" => $query,
            "created" => $period
        );
            
        $page_next_url = 1;
        while(!empty($page_next_url))
        {
            $json = self::api("search/video/", $params);
        
            foreach($json["results"] as $arSeria)
            {
                if( ceil($arSeria["duration"]/60)>5 && $serial_id==$arSeria["tv_id"])
                {
                    $arItems[] = self::getVideoById($arSeria["id"]);
                }
            }
 
            $params = array();
            $page_next_url = $json["next"];
        }
        
        return $arItems;
    }
    
    /**
     * Get items from serial by url
     *
     * @array return
     */
    public static function getItemsBySerialUrl($url)
    {
        $arItems = array();
        $page = 1;
        $method = str_replace(array("http://rutube.ru/", "api/"), "", $url);
        if(strpos($method, "video")===false)
            $method.= "video";
        
        while(intval($page)>0)
        {
            $params = array("page" => $page);
            $json = self::api($method, $params);
            
            foreach($json["results"] as $arSeria)
            {
                if($arSeria["type"]["id"]==2)
                {
                    $arItems[] = self::getVideoById($arSeria["id"]);
                } 
            }
            
            $page = str_replace("http://rutube.ru/api/".$method."?page=", "", $json["next"]);
        }
        
        $arItems = array_reverse($arItems);
        
        return $arItems;
    }
        
    /**
     * Search one item by title
     *
     * @array return
     */
    public static function searchOne($serial_title, $season = false, $seria = false)
    {
        $serial_title = trim($serial_title);

        $obj = self::api("search/autocomplete/", array("query" => $serial_title));

        if(count($obj)>0)
        {
            $tv_id = $obj["tv"][0]["id"];
            $params = array();
            
            if($season)
                $params["season"] = intval($season);
            
            if($seria)
                $params["episode"] = intval($seria);
                
            $json = self::api("metainfo/tv/".$tv_id."/video/", $params);
            
            $arItems = $json["results"];
            
            return self::getVideoById($arItems[0]["id"]);
        }
        
        return false;
    }  
    
    /**
     * Get one item by id
     *
     * @array return
     */
    public static function getVideoById($video_id)
    {
        if(!$video_id)
            return false;
        
        $json = self::api('video/'.$video_id, array());
        
        $arVideo = array(
            "TITLE" => $json["title"],
            "ID" => $json["id"],
            "VIDEO_URL" => $json["embed_url"],
            "THUMBNAIL_URL" => $json["thumbnail_url"],
            "JSON" => $json
        );
        
        return $arVideo;
    }
}