<?
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__) . '/../');
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
set_time_limit(0);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: text/html; charset=utf-8');
ini_set('mbstring.func_overload', '2');
ini_set('mbstring.internal_encoding', 'UTF-8');

global $USER, $APPLICATION;
if (!is_object($USER))
    $USER=new \CUser;

$arSerials = array();
$result = \Hawkart\Megatv\ProgTable::getList(array(
    'filter' => array(
        "!UF_EPG_SUB_ID" => false,
    ),
    'select' => array(
        "ID", "UF_TITLE", "UF_SERIA", "UF_SEASON", "UF_SOCIAL_VIDEO", "UF_EPG_ID"
    )
));
while ($arProg = $result->fetch())
{
    $arVideos = json_decode($arProg["UF_SOCIAL_VIDEO"], true);
    
    if(empty($arVideos["RUTUBE"]))
    {
        if($rutube = \Hawkart\Megatv\Social\RutubeClient::search($arProg["UF_TITLE"], $arProg["UF_SEASON"], $arProg["UF_SERIA"]))
            $arVideos["RUTUBE"] = $rutube;
    }
    
    if(empty($arVideos["VK"]))
    {
        $title = $arProg["UF_TITLE"];
        if(!empty($arProg["UF_SEASON"]))
            $title.= ": сезон ".$arProg["UF_SEASON"];
        if(!empty($arProg["UF_SERIA"]))
            $title.= " серия ".$arProg["UF_SERIA"];
        
        $vk = new \Hawkart\Megatv\Social\VkClient();
        if($video = $vk->searchOne($title))
            $arVideos["VK"] = $video;
    }

      
    \Hawkart\Megatv\ProgTable::update($arProg["ID"], array(
        "UF_SOCIAL_VIDEO" => json_decode($arVideos))
    );
}
die();
?>