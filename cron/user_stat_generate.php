<?
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__) . '/../');
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
set_time_limit(0);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
echo date("H:i:s")."\r\n";

$arProgsByRating = \Hawkart\Megatv\ProgTable::getProgsByRating();

$rsUsers = CUser::GetList(($by="id"), ($order="asc"), Array("ACTIVE" => "Y"), array("FIELDS"=>array("ID")));
while($arUser = $rsUsers->GetNext())
{
    $arStatistic = \Hawkart\Megatv\CStat::getByUser($arUser["ID"]);
        
    $arRecommend = array(
        "by_ganres" => \Hawkart\Megatv\CStat::getProgsByGanre($arProgsByRating, $arStatistic),
        "by_users" => \Hawkart\Megatv\CStat::getTopRateProg($arProgsByRating),
        "by_records" => \Hawkart\Megatv\CStat::getTopRateSerialByUser($arUser["ID"], $arStatistic)
    );
    $json = json_encode($arRecommend);
    
    $oUser = new CUser;
    $oUser->Update($arUser["ID"], array(
        "UF_RECOMMEND" => $json
    ));
    //break;
}

echo date("H:i:s")."\r\n";
die();
?>