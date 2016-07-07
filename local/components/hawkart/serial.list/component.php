<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $USER, $APPLICATION;
$arResult = array();

$arResult = array();
$result = \Hawkart\Megatv\SerialTable::getList(array(
    'filter' => array("!UF_EPG_ID" => false, "!UF_EXTERNAL_URL" => false),
    'select' => array("ID", "UF_TITLE", "UF_EPG_ID", "UF_DESC"),
    'order' => array("UF_TITLE" => "ASC")
));
while ($row = $result->fetch())
{
    $arResult["ITEMS"][] = $row;
}

$this->IncludeComponentTemplate();
?>