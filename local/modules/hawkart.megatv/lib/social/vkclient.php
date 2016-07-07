<?
namespace Hawkart\Megatv\Social;

class VkClient{
    
    protected static $url = 'http://api.vk.com/method/';
    protected static $client_id;
    protected static $client_secret;
    protected static $token;
    protected static $code = 'a35c29357c7c87e93a';
    
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
        
        $vkontakteAccessToken = \COption::GetOptionString("grain.customsettings", "vk_token");
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
            
            \COption::SetOptionString("grain.customsettings", "vk_token", $oResponce["access_token"]);
            self::$token = $data["access_token"];
        }else{
            self::$token = $vkontakteAccessToken;
        }
    }
    
    function api($method, $params = array())
    {
    	$params['access_token'] = self::$token;
        $params['client_secret'] = self::$client_secret;
        $params['v'] = "5.52";
        
    	$url = 'https://api.vk.com/method/' . $method . '?' . http_build_query($params);
        //echo $url."<br />";
        
    	$response = file_get_contents($url);
    	return json_decode($response, true);
    }
    
    public function searchOne($title)
    {
        $params = array("q"=>$title, "sort"=> 2, "count" => 1, "extended" => 1);

        $response = self::api('video.search', $params);
        $arItem = $response["response"]["items"][0];
        
        if(!empty($arItem["photo_640"]))
            $img = $arItem['photo_640'];
        else
            $img = $arItem['photo_320'];
        
        $arVideo = array(
            "TITLE" => $arItem["title"],
            "ID" => $arItem["id"],
            "VIDEO_URL" => $arItem["player"],
            "THUMBNAIL_URL" => $img
        );
 
        return $arVideo;   
    }    
}