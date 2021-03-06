<?
class VkClient{
    
    protected static $url = 'http://api.vk.com/method/';
    protected static $client_id;
    protected static $client_secret;
    protected static $token;
    protected static $code = 'a35c29357c7c87e93a';
    protected static $img_dir = '/upload/social_channel/vk/';
    protected static $file = '/upload/vk.json';
    
    public function __construct()
    {
        \CModule::IncludeModule("iblock");
        
        $arrFilter = array(
            "IBLOCK_ID" => SOCIAL_CONFIG_IB,
            "PROPERTY_PROVIDER" => "Vkontakte",
            "PROPERTY_SOCIAL_ID" => $userProfile["identifier"]
        );
        $arSelect = array("PROPERTY_SECRET", "PROPERTY_ID");
        $rsRes = \CIBlockElement::GetList( $arOrder, $arrFilter, false, false, $arSelect );
		if( $arItem = $rsRes->GetNext() )
        {
            self::$client_id = $arItem["PROPERTY_ID_VALUE"];
            self::$client_secret = $arItem["PROPERTY_SECRET_VALUE"];
		}
        
        if($_GET["code"])
            $code = $_GET["code"];
        
        self::getToken($code);
    }    
    
    /**
     * Return url for getting code (need for get token after)
     */
    public static function getCode()
    {
        $url = 'http://api.vkontakte.ru/oauth/authorize?client_id='.self::$client_id.
            '&scope=offline,wall,groups,pages,photos,docs,audio,video,notes,stats'.
            '&redirect_uri=http://www.megatv.su/cron/vk.php&response_type=code';
            
        return $url;
    }
    
    /**
     * Получить Token ID
     * @return str Token
     */
    public static function getToken($vkontakteCode)
    {
        if($vkontakteCode)
            self::$code = $vkontakteCode;
        
        $vkontakteAccessToken = COption::GetOptionString("grain.customsettings", "vk_token");
        if (!empty(self::$code) && !$vkontakteAccessToken)
        {
            /*$sUrl = 'https://oauth.vk.com/access_token?client_id='.self::$client_id.'&client_secret='.self::$client_secret.
                '&redirect_uri=http://www.megatv.su/cron/vk.php&code='.self::$code;
            $oResponce = json_decode(file_get_contents($sUrl), true);*/
            
            $client = new \Guzzle\Http\Client();
            $params = array(
                "client_id" => self::$client_id,
                "client_secret" => self::$client_secret,
                "v" => "5.50",
                "redirect_uri" => "http://www.megatv.su/cron/vk.php",
                "code"=> self::$code
            );
            $request = $client->get("https://oauth.vk.com/access_token". '?' . http_build_query($params));
            $data = $request->send()->json();
            
            COption::SetOptionString("grain.customsettings", "vk_token", $oResponce["access_token"]);
            self::$token = $data["access_token"];
        }else{
            self::$token = $vkontakteAccessToken;
        }
    }
    
    function api($method, $params = array())
    {
    	$params['access_token'] = self::$token;
        $params['client_secret'] = self::$client_secret;
        $params['v'] = "5.50";
        
    	$url = 'https://api.vk.com/method/' . $method . '?' . http_build_query($params);
        //echo $url."<br />";
        
    	$response = file_get_contents($url);
    	return json_decode($response, true);
    }
    
    public function import()
    {
        $arVideos = array();
        $videoIds = array();
        
        $file = $_SERVER["DOCUMENT_ROOT"].self::$file;
        \CDev::deleteOldFiles($_SERVER["DOCUMENT_ROOT"]. self::$img_dir, 0);
        
        $page = 1;
        $nextPageToken = false;
                
        while($page<100)
        {
            $params = array("filters"=>"ugc", "items_count"=> 10);
            if($nextPageToken)
                $params["from"] = $nextPageToken;
                
            $response = self::api('video.getCatalog', $params);
            $items = $response["response"]["items"][0]["items"];
            
            foreach ($items as $videoResult) 
            {
                $id = $videoResult["owner_id"]."_".$videoResult["id"];
                if(in_array($id, $videoIds) || $videoResult["type"]!="video")
                    continue;
        
                $videoIds[] = $id;
            }
            
            $nextPageToken = $response["response"]["next"];
            //echo $nextPageToken."\r\n";
            $page++;
        }
        
        //Склеенный запрос не работает(! слишком много ид !)
        
        foreach($videoIds as $video)
        {
            $params = array(
                "videos" => $video,
                "count" => 1,
                "offset" => 0
            );
            $response = self::api('video.get', $params);
            
            $videoResult = $response["response"]["items"][0];
            
            if(empty($videoResult["id"]))
                continue;
            
            if(!empty($videoResult["photo_640"]))
                $img = $videoResult['photo_640'];
            else
                $img = $videoResult['photo_320']; 
            
            $crop = self::resizePic($videoResult["id"], $img);
            
            $arVideo = array(
                "NAME" => $videoResult['title'],
                "IMG" => $crop,
                "PLAYER_BG" => $img,
                "ID" => $videoResult["id"],
                //"VIDEO_URL" => "https://vk.com/video?z=video".$videoResult["owner_id"]."_".$videoResult["id"],
                "VIDEO_URL" => $videoResult["player"],
                "DESC" => $videoResult['description'],
            );
    
            $ids[] = $videoResult["id"];
            $arVideos[] = $arVideo;
        }
        
        file_put_contents($file, json_encode($arVideos));        
    }
    
    public static function resizePic($id, $url)
    {
        $socialChannel = "vk";
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
    
    public static function getList()
    {
        $file = $_SERVER["DOCUMENT_ROOT"].self::$file;
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
            $videos[$key]["CLASS"] = "one";
            $arVideos[] = $videos[$key];
        }
        return $arVideos;
    }
}