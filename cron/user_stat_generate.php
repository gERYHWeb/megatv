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
    
    $result = \Hawkart\Megatv\UserTable::getList(array(
        'filter' => array(
            "=UF_USER_ID" => $arUser["ID"], 
        ),
        'select' => array(
            "ID"
        )
    ));
    if($arUserTable = $result->fetch())
    {
        $arFields = array(
            "UF_RECOMMEND" => $json
        );
        \Hawkart\Megatv\UserTable::Update($arUser["ID"], $arFields);
    }else{
        $arFields = array(
            "UF_USER_ID" => $arUser["ID"],
            "UF_RECOMMEND" => $json
        );
        \Hawkart\Megatv\UserTable::add($arFields);
    }
}

echo date("H:i:s")."\r\n";
die();
?>