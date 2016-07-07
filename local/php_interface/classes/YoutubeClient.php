<?
class YoutubeClient
{
    protected static $img_dir = '/upload/social_channel/youtube/';
    protected static $dev_key;
    
    /**
     * Get dev_key from db
     */
    public function __construct()
    {
        \CModule::IncludeModule("iblock");
        
        $arrFilter = array(
            "IBLOCK_ID" => SOCIAL_CONFIG_IB,
            "PROPERTY_PROVIDER" => "Youtube",
            "PROPERTY_SOCIAL_ID" => $userProfile["identifier"]
        );
        $arSelect = array("PROPERTY_SECRET");
        $rsRes = \CIBlockElement::GetList( $arOrder, $arrFilter, false, false, $arSelect );
		if( $arItem = $rsRes->GetNext() )
        {
            self::$dev_key = $arItem["PROPERTY_SECRET_VALUE"];
		}
    }
    
    /**
     * Get videos from youtube for each tv channel
     */
    public function importForChannels()
    {
        self::deletePics();
        
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
                $arBaseChannels[$row["ID"]] = $arUrls;
        }
        
        foreach($arBaseChannels as $channel_id => $arUrls)
        {
            $file = self::getFilePathByChannel($channel_id);
            
            $arVideos = array();
            foreach($arUrls as $url)
            {
                $arVideos =  array_merge($arVideos, $this->getArVideosByUrl($url));
            }
            
            self::save($arVideos, $file);
        }
    }
  
    /**
     * Parse video urls from web page
     * 
     * @return array
     */
    public static function parseUrl($url = false)
    {
        if(!$url)
            $url = "https://www.youtube.com/feed/trending";
        
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $html = curl_exec($ch);
        curl_close($ch);
        
        $dom = new DOMDocument();

        $videos = array();
        @$dom->loadHTML($html);
        foreach($dom->getElementsByTagName('a') as $link) 
        {
             $href = $link->getAttribute('href');
             
             if(strpos($href, "http://www.youtube.com")!==false)
                continue;
             
             if(strpos($href, "/watch?v=")!==false && strpos($href, "https://www.youtube.com")===false)
             {
                $vid = str_replace(array("/watch?v=", "https://www.youtube.com", "http://www.youtube.com"), "", $href);
                $videos[] = $vid; 
             }
        }
        $videos = array_unique($videos);
        return $videos;
    }
    
    public function getArVideosByUrl($url = false)
    { 
        $arVideos = array();
        $perPage = 50;
        $videoCount = 1000;
        $skipPages = 2;
        $curPage = 1;
        $nextPageToken = false;
        
        $client = new Google_Client();
        $client->setDeveloperKey(self::$dev_key);
        $youtube = new Google_Service_YouTube($client);
        $ids = array();
        
        $videoResults = self::parseUrl($url);
        $videoIds = implode(',', $videoResults);

        $videosResponse = $youtube->videos->listVideos('snippet, recordingDetails', array(
            'id' => $videoIds,
        ));
        
        foreach ($videosResponse['items'] as $videoResult) 
        {
            if(in_array($videoResult["id"], $ids))
                continue;
            
            if(!empty($videoResult['snippet']['thumbnails']["high"]["url"]))
                $img = $videoResult['snippet']['thumbnails']["high"]["url"];
            else
                $img = $videoResult['snippet']['thumbnails']["standard"]["url"]; 
            
            $crop = self::resizePic($videoResult["id"], $img);
            
            $arVideo = array(
                "NAME" => $videoResult['snippet']['title'],
                "TAGS" => $videoResult['snippet']['tags'],
                "IMG" => $crop,
                "PLAYER_BG" => $img,
                "ID" => $videoResult["id"],
                "VIDEO_URL" => "https://www.youtube.com/watch?v=".$videoResult["id"],
                "DESC" => $videoResult['snippet']["localized"]["description"],
                "CHANNEL_TITLE" => $videoResult['snippet']["channelTitle"],
                "CHANNEL_ID" => $videoResult['snippet']["channelId"]
            );
    
            $ids[] = $videoResult["id"];
            $arVideos[] = $arVideo;
        }
        
        return $arVideos;
    }
    
    public static function save($array, $file = false)
    {
        if(!$file)
            $file = $_SERVER["DOCUMENT_ROOT"]."/upload/youtube.json";
            
        file_put_contents($file, json_encode($array));
    }
    
    public static function deletePics()
    {
        \CDev::deleteOldFiles($_SERVER["DOCUMENT_ROOT"].self::$img_dir, 0);
    }
    
    public static function resizePic($id, $url)
    {
        $socialChannel = "youtube";
        $file_name = $id.".jpg";
        $dir = self::$img_dir;
        $file_path = $dir.$socialChannel."_".$file_name;
        $file_path_crop = $dir.$socialChannel."_288x288_".$file_name;
        file_put_contents($_SERVER["DOCUMENT_ROOT"].$file_path, file_get_contents($url));
        
        if(!file_exists($_SERVER["DOCUMENT_ROOT"].$file_path_crop))
        {
            $resizeRez = CFile::ResizeImageFile( // уменьшение картинки для превью
                $_SERVER["DOCUMENT_ROOT"].$file_path,
                $dest = $_SERVER["DOCUMENT_ROOT"].$file_path_crop,
                array(
                    'width' => 288,
                    'height' => 288,
                ),
                $resizeType = BX_RESIZE_IMAGE_EXACT,
                $waterMark = array(),
                $jpgQuality = 100
            );
        }
        
        unlink($_SERVER["DOCUMENT_ROOT"].$file_path);
        return $file_path_crop;
    }
    
    public static function getFilePathByChannel($channel_id)
    {
        return $_SERVER["DOCUMENT_ROOT"]."/upload/youtube_".$channel_id.".json";
    }
    
    public static function getList($file = false)
    {
        if(!$file)
            $file = $_SERVER["DOCUMENT_ROOT"]."/upload/youtube.json";
            
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
            $videos[$key]["CLASS"]="one";
            $arVideos[] = $videos[$key];
        }
        
        return $arVideos;
    }
}