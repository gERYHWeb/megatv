<?
define('STOP_STATISTICS', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
$GLOBALS['APPLICATION']->RestartBuffer();
CModule::IncludeModule("iblock");
CModule::IncludeModule("sale");
CModule::IncludeModule("catalog");

global $USER;
if(!is_object($USER))
    $USER = new CUser;

$broadcastID = intval($_GET["broadcastID"]);

if(!$_GET["record"])
{
    $arRecords = CRecordEx::getList(array("UF_USER"=> $USER->GetID(), "UF_SCHEDULE"=>$broadcastID), array("ID"));
    $arRecord = $arRecords[0];
    $broadcastID = $arRecord["ID"];
}
CRecordEx::update($broadcastID, array(
    "UF_PROGRESS_SECS" => intval($_GET["progressInSeconds"]),
    "UF_PROGRESS_PERS" => intval($_GET["progressPosition"]),
    "UF_WATCHED" => true
));

die();  
?>