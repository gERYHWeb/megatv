<?
$_SERVER["DOCUMENT_ROOT"] = "/home/d/daotel/MEGATV/public_html";
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
set_time_limit(0);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: text/html; charset=utf-8');
ini_set('mbstring.func_overload', '2');
ini_set('mbstring.internal_encoding', 'UTF-8');

global $USER, $APPLICATION;
if (!is_object($USER))
    $USER=new CUser;

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");

/**
 * Уведомление на email в момент начала записи
 */
$dt = new Bitrix\Main\Type\DateTime(date('Y-m-d H:i:s', time()), 'Y-m-d H:i:s');
$arFilter = array(
    "UF_URL" => false,
    "<UF_DATE_START" => $dt,
    ">UF_DATE_END" => $dt,
    "UF_BEFORE_NOTIFY" => false
);
$arRecords = CRecordEx::getList($arFilter, array("ID", "UF_USER", "UF_NAME", "UF_SUB_TITLE", "UF_PICTURE"));
foreach($arRecords as $arRecord)
{
    CNotifyEx::onRecord(array(
        "USER_ID" => $arRecord["UF_USER"],
        "RECORD_ID" => $arRecord["ID"],
        "PICTURE" => "http://megatv.su".CFile::GetPath($user_record["UF_PICTURE"]),
        "RECORD_NAME" => trim($arRecord["UF_NAME"]." ".$arRecord["UF_SUB_TITLE"])
    ));
    CRecordEx::update($arRecord["ID"], array("UF_BEFORE_NOTIFY" => "Y"));
}
die();
?>