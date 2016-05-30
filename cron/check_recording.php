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

/**
 * Уведомление на email в момент начала записи
 */
$dt = new Bitrix\Main\Type\DateTime(date('Y-m-d H:i:s', time()), 'Y-m-d H:i:s');
$result = \Hawkart\Megatv\RecordTable::getList(array(
    'filter' => array(
        "UF_URL" => false,
        ">UF_DATE_START" => $dt,
        "<UF_DATE_END" => $dt,
        "!UF_BEFORE_NOTIFY" => 1
    ),
    'select' => array(
        "ID", "UF_TITLE" => "UF_PROG.UF_TITLE", "UF_SUB_TITLE" => "UF_PROG.UF_SUB_TITLE",
        "UF_IMG_PATH" => "UF_PROG.UF_IMG.UF_PATH", "UF_USER_ID"
    )
));
while ($arRecord = $result->fetch())
{
    $arRecord["NAME"] = \Hawkart\Megatv\ProgTable::getName($arRecord);
    $arRecord["PICTURE"]["SRC"] = \Hawkart\Megatv\CFile::getCropedPath($arRecord["UF_IMG_PATH"], array(300, 300), true);
    
    \CNotifyEx::onRecord(array(
        "USER_ID" => $arRecord["UF_USER_ID"],
        "RECORD_ID" => $arRecord["ID"],
        "PICTURE" => "http://megatv.su".$arRecord["PICTURE"]["SRC"],
        "RECORD_NAME" => trim($arRecord["NAME"])
    ));
    
    \Hawkart\Megatv\RecordTable::update($arRecord["ID"], array("UF_BEFORE_NOTIFY" => 1));
}
die();
?>