<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $APPLICATION;
$arResult = array();
$arResult["URL"] = $APPLICATION->GetCurDir();
$arResult["GEO"] = \Hawkart\Megatv\CityTable::getGeoCity();
$arResult["ITEMS"] = array();
$arResult["CUR_CITY"] = array();

$arFilter = array(
    "UF_COUNTRY.UF_TITLE" => "Россия", 
    "UF_ACTIVE" => 1
);
$arSelect = array("ID", "UF_TITLE");
$obCache = new \CPHPCache;
if( $obCache->InitCache(86400, serialize($arFilter).serialize($arSelect), "/cityList/"))
{
	$arResult["ITEMS"] = $obCache->GetVars();
}
elseif($obCache->StartDataCache())
{
    $result = \Hawkart\Megatv\CityTable::getList(array(
        'filter' => $arFilter,
        'select' => $arSelect,
        'order' => array("UF_TITLE" => "ASC")
    ));
    while ($arCity = $result->fetch())
    {
        $arResult["ITEMS"][] = $arCity;
    }
	$obCache->EndDataCache($arResult["ITEMS"]); 
}
    
foreach($arResult["ITEMS"] as $arCity)
{
    if($arResult["GEO"]["ID"]==$arCity["ID"]) $arResult["CUR_CITY"] = $arCity;
}

$this->IncludeComponentTemplate();
?>