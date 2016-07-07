<?
class SocialChannel
{
    public function __construct()
    {
        //$this->getBaseChannels();
        //$this->deletePics();
        //$this->import();
        
        $this->getSerials();
        $this->importBySerialUrl();
    }
    
    public static function deletePics()
    {
        \CDev::deleteOldFiles($_SERVER["DOCUMENT_ROOT"]. "/upload/social_channel/youtube/", 0);
        \CDev::deleteOldFiles($_SERVER["DOCUMENT_ROOT"]. "/upload/social_channel/vk/", 0);
        \CDev::deleteOldFiles($_SERVER["DOCUMENT_ROOT"]. "/upload/social_channel/rutube/", 0);
    }
    
    /**
     * Get serials items 
     */
    public static function getItemsBySerialEpg($epg_id)
    {
        $result = \Hawkart\Megatv\SerialTable::getList(array(
            'filter' => array("=UF_EPG_ID" => $epg_id, "!UF_EXTERNAL_URL" => false),
            'select' => array("UF_EPG_ID", "ID", "UF_EXTERNAL_URL", "UF_EXTERNAL_TITLE", "UF_ITEMS")
        ));
        if ($row = $result->fetch())
        {
            $arSerials[$row["UF_EPG_ID"]] = $row;
        }
        
        return false;
    }

    public static function getBaseChannels()
    {
        $arBaseChannels = array();
        
        $result = \Hawkart\Megatv\ChannelBaseTable::getList(array(
            'filter' => array("UF_ACTIVE" => 1),
            'select' => array("ID", "UF_YOUTUBE"),
            'order' => array("ID" => "ASC")
        ));
        while ($row = $result->fetch())
        {
            $arUrls = array();
            $ar = explode(";", $row["UF_YOUTUBE"]);
            foreach($ar as $url)
            {
                $url = trim($url);
                if(!empty($url))
                {
                    $arUrls[] = $url;
                }
            }
            
            if(count($arUrls)>0)
            {
                $arBaseChannels[$row["ID"]] = $arUrls;
            }
                
        }
        
        $this->arBaseChannels = $arBaseChannels;
    }
    
    public static function getSerials()
    {
        $arSerials = array();
        $result = \Hawkart\Megatv\SerialTable::getList(array(
            'filter' => array("!UF_EPG_ID" => false, "!UF_EXTERNAL_URL" => false),
            'select' => array("UF_EPG_ID", "ID", "UF_EXTERNAL_URL", "UF_EXTERNAL_TITLE")
        ));
        while ($row = $result->fetch())
        {
            $arSerials[$row["UF_EPG_ID"]] = $row;
        }
        
        $this->arSerials = $arSerials;
    }
    
    public static function importBySerialUrl()
    {
        $arSerials = $this->arSerials;
        foreach($arSerials as $epg_id => $arSerial)
        {
            $url = $arSerial["UF_EXTERNAL_URL"];
            
            if(strpos($url, "youtube.com/user")!==false)
            {
                //$arPersonalYoutube[] = $url."/videos";
            }
            if(strpos($url, "youtube.com/channel")!==false)
            {
                //$arOficialChannelYoutube[] = $url."/videos";
            }
            if(strpos($url, "rutube.ru")!==false)
            {
                if(strpos($url, "rutube.ru/api/")===false)
                {
                    $url = str_replace("rutube.ru/", "rutube.ru/api/", $url)."video";
                }
                
                $rutube = new \RutubeClient();
                $arVideos =  array_merge($arVideos, $rutube->getItemsBySerialUrl($url));
            }
            if(strpos($url, "vk.com")!==false)
            {
                //$arVkontakte[] = $url;
            }
        }
    }
    
    public function import()
    {
        $arBaseChannels = $this->arBaseChannels;
        
        foreach($arBaseChannels as $channel_id => $arUrls)
        {
            $file = self::getFilePathByChannel($channel_id);
            
            $arVideos = array();
            $arPersonalYoutube = array();
            $arOficialChannelYoutube = array();
            $arRutube = array();
            $arVkontakte = array();
            
            foreach($arUrls as $url)
            {
                if(strpos($url, "youtube.com/user")!==false)
                {
                    $arPersonalYoutube[] = $url."/videos";
                }
                if(strpos($url, "youtube.com/channel")!==false)
                {
                    $arOficialChannelYoutube[] = $url."/videos";
                }
                if(strpos($url, "rutube.ru")!==false)
                {
                    $arRutube[] = $url;
                }
                if(strpos($url, "vk.com")!==false)
                {
                    $arVkontakte[] = $url;
                }
            }
            
            if(count($arPersonalYoutube)>0)
            {
                foreach($arPersonalYoutube as $url)
                {
                    $youtube = new \YoutubeClient();
                    $arVideos =  array_merge($arVideos, $youtube->getArVideosByUrl($url));
                }
            }
            
            if(count($arOficialChannelYoutube)>0)
            {
                foreach($arOficialChannelYoutube as $url)
                {
                    $youtube = new \YoutubeClient();
                    $arVideos =  array_merge($arVideos, $youtube->getArVideosByUrl($url));
                }
            }
            
            if(count($arRutube)>0)
            {
                foreach($arRutube as $url)
                {
                    $rutube = new \RutubeClient();
                    //$arVideos =  array_merge($arVideos, $rutube->getArVideosByUrl($url));
                }
            }
            
            self::save($arVideos, $file);
        }
    }
    
    public static function getFilePathByChannel($channel_id)
    {
        return $_SERVER["DOCUMENT_ROOT"]."/upload/".$channel_id.".json";
    }
    
    public static function getList($file)
    {
        $txt = file_get_contents($file);
        $json = json_decode($txt, true);
        
        return $json;
    }
    
    public static function dailyShow($file)
    {
        $arVideos = array();
        $videos = self::getList($file);     
        $rand_keys = array_rand($videos, 24);
        foreach($rand_keys as $key)
        {
            $videos[$key]["CLASS"] = "one";
            $arVideos[] = $videos[$key];
        }
        
        return $arVideos;
    }
    
    public static function save($array, $file)
    {
        file_put_contents($file, json_encode($array));
    }
    
}