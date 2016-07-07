<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $USER, $APPLICATION;
$arResult = array();

$result = \Hawkart\Megatv\SerialTable::getList(array(
    'filter' => array("=UF_EPG_ID" => $_REQUEST["EPG_ID"]),
    'select' => array("ID", "UF_TITLE",  "UF_DESC")
));
if ($row = $result->fetch())
{
    $arResult["SERIAL"] = $row;
}

$result = \Hawkart\Megatv\ProgExternalTable::getList(array(
    'filter' => array("=UF_SERIAL.UF_EPG_ID" => $_REQUEST["EPG_ID"]),
    'select' => array("ID", "UF_TITLE", "UF_EXTERNAL_ID", "UF_THUMBNAIL_URL")
));
while ($row = $result->fetch())
{
    if(strpos($row["UF_THUMBNAIL_URL"], "rutube")!==false)
    {
        $row["UF_THUMBNAIL_URL"].="?size=m";
    }
    $arResult["ITEMS"][] = $row;
}

$APPLICATION->SetTitle($arResult["SERIAL"]["UF_TITLE"]);
$APPLICATION->SetPageProperty($arResult["SERIAL"]["UF_TITLE"]);
$APPLICATION->SetPageProperty("description", TruncateText($arResult["SERIAL"]["UF_DESC"], 256));

$this->IncludeComponentTemplate();
?>