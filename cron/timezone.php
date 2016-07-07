<?
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__) . '/../');

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
set_time_limit(0);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

global $USER, $APPLICATION;
if (!is_object($USER))
    $USER=new CUser;

\Hawkart\Megatv\GeoCity::download();
\Hawkart\Megatv\CityTable::getTimezoneByCity();

die()
?>