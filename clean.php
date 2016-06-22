<?
$_SERVER["DOCUMENT_ROOT"] = "D:/OpenServer/domains/megatv2.ru"; //изменить на сервере
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
set_time_limit(0);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: text/html; charset=utf-8');
ini_set('mbstring.func_overload', '2');
ini_set('mbstring.internal_encoding', 'UTF-8');

die();
global $USER, $APPLICATION;
if (!is_object($USER))
    $USER=new CUser;

\Hawkart\Megatv\CEpg::clear();

\Hawkart\Megatv\RecordTable::deleteAll();
\CDev::deleteOldFiles($_SERVER["DOCUMENT_ROOT"]."/upload/record_cut/", 0);

$dbUsers = \CUser::GetList(($by="EMAIL"), ($order="desc"), Array());
while($arUser = $dbUsers->Fetch())
{
    $cuser = new \CUser;
    $cuser->Update($arUser["ID"], array(
        "UF_STATISTIC" => "",
        "UF_CAPACITY_BUSY" => 0
    ));
}
die();
?>