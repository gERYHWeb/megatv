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
    
    public static function parseTrend()
    {
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
    
    public function import()
    {
        $file = $_SERVER["DOCUMENT_ROOT"]."/upload/youtube.json";
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
        
        \CDev::deleteOldFiles($_SERVER["DOCUMENT_ROOT"].self::$img_dir, 0);

        $videoResults = self::parseTrend();
        //\CDev::pre($videoResults);
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
        
        //echo count($arVideos)."\r\n";
        file_put_contents($file, json_encode($arVideos));
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
            /*$image = new \Eventviva\ImageResize($_SERVER["DOCUMENT_ROOT"].$file_path);
            $image->resizeToHeight(288);
            $image->crop(288, 288);
            $image->save($_SERVER["DOCUMENT_ROOT"].$file_path_crop);*/
            
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
    
    public static function getList()
    {
        $file = $_SERVER["DOCUMENT_ROOT"]."/upload/youtube.json";
        $txt = file_get_contents($file);
        $json = json_decode($txt, true);
        
        return $json;
    }
    
    public static function dailyShow()
    {
        $arVideos = array();
        $videos = self::getList();
        $rand_keys = array_rand($videos, 24);
        foreach($rand_keys as $key)
        {
            $videos[$key]["CLASS"]="one";
            $arVideos[] = $videos[$key];
        }
        return $arVideos;
    }
}